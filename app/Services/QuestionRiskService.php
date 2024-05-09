<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\QuestionRisk;
use Illuminate\Support\Facades\Auth;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Class QuestionRiskService
 * @package App\Services
 */
class QuestionRiskService
{
    public function __construct(private GeneralService $generalService, private MeidaService $meidaService)
    {
    }

    public function index($request)
    {
        $authRole = auth()->user()->roles->pluck('name')[0];
        if ($authRole != 'Manager') {
            abort(403, 'Unauthorized: Only Manager users are allowed.');
        }
        $searchCols = ['priority'];
        $relationalCols = [
            'question' => ['title'],
        ];

        //filter work
        $where = [];
        if (isset($request->status)) {
            array_push($where, ['key' => 'status', 'operator' => '=', 'value' => $request->status]);
        }
        if (isset($request->priority)) {
            array_push($where, ['key' => 'priority', 'operator' => '=', 'value' => $request->priority]);
        }

        if (isset($request->is_audit)) {
            array_push($where, ['key' => 'status', 'operator' => '!=', 'value' => 'completed']);
        }

        $data = QuestionRisk::with('question', 'assignees', 'conversations', 'reason', 'reason.user', 'conversations.user', 'conversations.photo', 'conversations.user.photo', 'question.section.task.mythBusters', 'question.section.task.category')->where('company_id', auth()->user()->company_id);
        $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, 'status', $relationalCols);


        $data = $this->generalService->handleWhere($data, $where);
        if (isset($request->name)) {
            $data = $data->whereHas('question', function ($query) use ($request) {
                $query->where('title', 'LIKE', "%{$request->name}%");
            });
        }

        if (isset($request->range_from)) {
            $data = $data->where('due_date', '>=', $request->range_from . ' 00:00:00');
        }

        if (isset($request->range_to)) {
            $data = $data->where('due_date', '<=', $request->range_to . ' 23:59:59');
        }

        // if (isset($request->repeat)) {
        //     $data = $data->whereHas('question.section.task.configuration', function($q) use($request){
        //         $q->where('repeat', $request->repeat);
        //     });
        // };

        if ($request['perpage'] == 'All') {
            $data = $this->generalService->handleAllData($request, $data);
        } else {
            $data = $this->generalService->handlePagination($request, $data);
        }

        return $data;
    }

    public function createRisk($data)
    {
        $user = auth()->user();
        $authID = $user->id;
        $data['company_id'] = $user->company_id;
        $risk = QuestionRisk::where('section_question_id', $data['section_question_id'])
            ->where('company_id', $data['company_id'])->first();
        if ($risk) {
            if ($risk->status == 'draft' || $risk->status == 'in_progress') {
                abort(422, 'Risk already exists.');
            } else {
                // Updating Risk
                if (Auth::user()->roles->pluck('name')[0] == 'Staff') {
                    $data["due_date"] = null;
                }
                $risk->update($data);
            }
        } else {
            // Creating Risk
            $risk = QuestionRisk::create($data);
        }

        $risk = $risk->load('question');

        if (isset($data["assignees"]) && count($data["assignees"])) {
            // Fetch existing assignee IDs for the risk
            $existingAssigneeIds = $risk->assignees()->pluck('user_id')->toArray();

            // Filter out new assignees that are not already assigned
            $newAssignees = array_diff($data["assignees"], $existingAssigneeIds);

            // Create pivot data for new assignees
            $assigneesData = collect($newAssignees)->map(function ($userId) {
                return ['user_id' => $userId];
            });

            // Create pivot records for new assignees
            if ($assigneesData->isNotEmpty()) {
                $risk->assigneesPivot()->createMany($assigneesData->toArray());
            }
        }

        // Creating Risk Conversation
        $risk->conversations()->create([
            'user_id' => $authID,
            'description' => $data["description"],
            'reason' => 1,
        ]);
        $risk = $risk->load('conversations');

        if (isset($risk->assignees) && count($risk->assignees)) {
            $this->generalService->sendFCMNotification($risk->assignees, "Creating Risk on MyCheX", 'Important update! A Risk has been assigned to you.');

            $subject = 'Creating Risk on MyCheX';
            $content = "<span>I wanted to inform you that a Risk has been assigned to you.<br /><br /> Details of the assigned Risk: <br /><br />" . (isset($risk->question) ? ("<b>Risk Title:</b> " . $risk->question->title . "<br /><br />") : '') . "<b>Description:</b> " .  $data["description"] . " <br /><br />If you have any questions or need clarification, please feel free to reach out <a href='mailto:info@mychex.co.uk'>info@mychex.co.uk</a> <br /><br />We appreciate your attention to this matter.</span>";
            // foreach ($risk->assigneesPivot as $user) {
            //     $this->generalService->sendEmail($user->user, $subject, $content);
            // }
            $notificationContent = '';
            if (isset($risk->assigneesPivot)) {
                if (count($risk->assigneesPivot) == 1) {
                    $notificationContent = $risk->assigneesPivot[0]->user->name . " has";
                }

                if (count($risk->assigneesPivot) == 2) {
                    $notificationContent = $risk->assigneesPivot[0]->user->name . ", " . $risk->assigneesPivot[1]->user->name . " have";
                }

                if (count($risk->assigneesPivot) > 2) {
                    $notificationContent = $risk->assigneesPivot[0]->user->name . ", " . $risk->assigneesPivot[1]->user->name . " and more have";
                }
            }

            if (Auth::user()->roles->pluck('name')[0] == 'Staff') {
                $subj = 'Risk Request:' . $risk->question->title;
                foreach (Auth::user()->company->managers as $manager) {
                    $message = "<span> One of your staff members has requested to mark a question as a Risk. Here are the details: <br /> <b>Question Title:</b> " . $risk->question->title . "<br /> <b>Staff Member:</b> " . auth()->user()->name . " <br /> <b>Request Description:</b> " . $risk->reason->description . " <br /> Please review this request and take appropriate action accordingly.</span>";
                    $this->generalService->sendEmail($manager, $subj, $message);
                }
                Notification::send(Auth::user()->company->managers, new GeneralNotification($risk, 'Risk', 'create', auth()->user()->name . " has requested to mark " . $risk->question->title . " as a Risk. Check your email for details and coordinate with the staff for swift action.", $subject, $content));
                Notification::send(Auth::user(), new GeneralNotification($risk, 'Risk', 'create', " Your request to mark a question as a Risk has been sent. Thank you for your attention to this matter.", $subject, $content));
            } else {

                $subj = 'Risk Request : ' . $risk->question->title;
                foreach (Auth::user()->company->managers as $manager) {
                    if (auth()->user()->id == $manager->id) {
                        continue;
                    }
                    $message = "<span> One of your staff members has requested to mark a question as a Risk. Here are the details: <br /> <b>Question Title:</b> " . $risk->question->title . "<br /> <b>Staff Member:</b> " . auth()->user()->name . " <br /> <b>Request Description:</b> " . $risk->reason->description . " <br /> Please review this request and take appropriate action accordingly.</span>";
                    $this->generalService->sendEmail($manager, $subj, $message);
                }

                foreach ($risk->assignees as $assignee) {
                    $subj = 'Risk Request:' . $risk->question->title;
                    $message = "<span> We hope this message finds you well. We wanted to inform you that a Risk has been assigned to you. Details of the assigned Risk: <br /> <b>Risk Title:</b> " . $risk->question->title . "<br /> <b>Staff Member:</b> " . $assignee->name . " <br /> <b>Priority:</b> " . $risk->priority . "<br /> <b>Due Date:</b> " . $risk->due_date . "<br /><b>Request Description:</b> " . $risk->reason->description . " <br /> If you have any questions or need clarification, please feel free to reach out at <a href='info@mychex.co.uk'>info@mychex.co.uk</a> <br /> We appreciate your attention to this matter.</span>";
                    $this->generalService->sendEmail($assignee, $subj, $message);
                }
                $this->generalService->sendEmail(auth()->user(), "Assigning Risk Confirmation", 'We hope this email finds you well.<br /><br />We wanted to confirm that the following Risk has been successfully assigned to the staff members under your supervision:<br /> <b>Risk Title:</b> ' . $risk->question->title . '<br /> <b>Staff Member:</b> ' . $assignee->name . ' <br /> <b>Priority:</b> ' . $risk->priority . '<br /> <b>Due Date:</b> ' . $risk->due_date . '<br /><b>Request Description:</b> ' . $risk->reason->description . ' <br />If there are any questions or concerns regarding this assignment, please dont hesitate to reach out at <a href="info@mychex.co.uk">info@mychex.co.uk</a><br />Thank you for your attention to this matter.');

                Notification::send($risk->assignees, new GeneralNotification($risk, 'Risk', 'create', "Risk Assignment Alert: $notificationContent been assigned a Risk. Check your email for details.", $subject, $content));
                Notification::send(Auth::user(), new GeneralNotification($risk, 'Risk', 'create', "Risk Assignment Alert: $notificationContent been assigned a Risk. Manager, check your email for details and coordinate with the team for swift action.", $subject, $content));
            }
        }

        if (isset($data["image_id"])) {
            $this->meidaService->store($data["image_id"], $risk->conversations[0]["id"], "App\\Models\\RiskConversation");
        }

        return $risk;
    }

    public function updateRisk($data, $id)
    {
        $data['company_id'] = auth()->user()->company_id;

        // Update Risk
        $risk = QuestionRisk::findOrFail($id);
        $risk->update($data);

        // Update Risk Assignees
        $risk->assigneesPivot()->delete();
        $assigneesData = collect($data["assignees"])->map(function ($userId) {
            return [
                'user_id' => $userId,
            ];
        });
        $risk->assigneesPivot()->createMany($assigneesData->toArray());

        if ($risk->status == 'completed') {
            $this->generalService->sendFCMNotification($risk->assignees, "Completion of Assigned Risk", 'The Risk assigned to you has been successfully completed.');
            $subject = 'Completion of Assigned Risk';
            $content = "<span>I am pleased to inform you that the Risk assigned to you has been successfully completed. <br /><br /> Thank you for your diligence and effort in addressing this Risk. <br /><br /> Thank you once again for your cooperation. <br /><br /></span>";
            foreach ($risk->assigneesPivot as $user) {
                $this->generalService->sendEmail($user->user, $subject, $content);
            }
            Notification::send($risk->assignees, new GeneralNotification($risk, 'Risk', 'create', "Risk Completed: The Risk assigned to you has been successfully addressed. Check your email for details.", $subject, $content));
            Notification::send(Auth::user(), new GeneralNotification($risk, 'Risk', 'create', "Risk Completed Alert: The assigned Risk has been successfully addressed. Manager, check your email for details.", $subject, $content));
        }
        return $risk;
    }

    // private function sendEmail($model, $risk)
    // {
    //     $email_data = [
    //         "name"          =>  $model->first_name,
    //         "email"         =>  $model->email,
    //         "subject"       =>  "Risk Assessment Checklist Action Required",
    //         "body"          =>  "<span> I hope this message finds you well. The recent Risk assessment for the checklist associated with your area has identified potential Risks that require your attention.</span> <br /><br /> <span><b>Risk:" . $risk->title . "<br /> Status: " . $risk->status . " </b></span> <br /> <br /> <span> Your prompt response is crucial in addressing these Risks. If you have any questions, please reach out. </span>",
    //         'button_url'    =>  'http://localhost:9000/',
    //         'button_text'   =>  "Login",
    //         'logo'          => (Auth::user()->company->photo != null) ? Auth::user()->company->photo->path : config('app.aws_url') . "public/logo.png",
    //     ];
    //     dispatch(new SendEmailJob($email_data));
    // }
}
