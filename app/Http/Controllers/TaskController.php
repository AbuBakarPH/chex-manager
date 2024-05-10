<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\CloneTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
use App\Services\GeneralService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct(private TaskService $service, private GeneralService $generalService)
    {
        //
    }
    public function index(Request $request)
    {
        return $this->response('CheckList listing', $this->service->index($request), 200);
    }

    public function store(StoreTaskRequest $request)
    {
        // if ($this->generalService->hasLimit('task')) {
        $task = $request->validated();
        $sections = $request->sections;
        return $this->response('CheckList created successfully', $this->service->store($task, $sections), 200);
        // } else {
        //     return $this->response('Your Limit Exceeded To Create Checklist', NULL, 403);
        // }
    }

    public function show($id)
    {

        $checklist = Task::with([
            'category', 'sub_category', 'mythBusters', 'org_role',
            // 'sections', 'sections.questions',
            'sections.questions.risk.assignees',
            'sections.questions.risk.conversations',
            'sections.questions.fields',
            'sections.questions.sub_questions',
            // 'sections.questions.sub_questions',
            // 'sections' => function ($q) {
            //     $q->with(['questions' => function ($query) {
            //         $query->with(['form_fields', 'question_checklist'])->where('parent_id', null);
            //     }]);
            // }
        ])->where('id', $id)->first();
        return $this->response('CheckList detail', $checklist, 200);
    }

    public function update(Request $request, $id)
    {
        $checkList = Task::find($id);
        return $this->response('CheckList updated successfully', $this->service->update($request, $checkList), 200);
    }

    public function destroy(Task $checkList)
    {
        $checkList->delete();
        return response()->noContent();
    }

    public function setTaskStatus(Request $request, $id)
    {
        $checkList = Task::findOrFail($id);

        if ($request['status'] == 'approved') {
            $checkList->update(['status' => 'active', 'admin_status' => 'approved']);
        }

        if ($request['status'] == 'rejected') {
            $checkList->update(['admin_status' => 'rejected']);
        }

        $manager = User::where('company_id', $checkList->company_id)->role('Manager')->first();
        $content = "I hope this message finds you well. We would like to inform you that your checklist request has been reviewed and approved by our administrative team. Checklist Details: <ul><li>Checklist Name : " . $checkList->name . "</li> <li> Status : " . ucfirst($request['status']) . "</li> </ul> Your approved checklist is now ready for use, and you may proceed with its implementation.If you have any further questions or require additional assistance, please feel free to reach out to us.";
        $subject = "Checklist Status Update";
        Notification::send($manager, new GeneralNotification($checkList, 'Task', 'POST', "Checklist " . ucfirst($request['status']) . " Notification", $subject, $content));
        $this->generalService->sendEmail($manager, $subject, $content);
        return $this->response('CheckList Update', $checkList, 204);
    }

    /**
     * Check List For Super Admin
     */

    public function adminChecklist(Request $request)
    {
        $where =  [
            ['key' => 'type', 'operator' => '=', 'value' => $request->type],
            ['key' => 'company_id', 'operator' => '!=', 'value' => null]
        ];
        return $this->response('CheckList listing', $this->service->index($request, null, ['category', 'sub_category', 'sections', 'company'], false, $where), 200);
    }

    public function dailyChecklist()
    {
        // $daily_checklist = DailyChecklist::with('checklist')->where('company_id', auth()->user()->company_id)->get();
        // return $this->response('CheckList detail', $daily_checklist, 200);
    }

    public function updateStatusDailyChecklist(Request $request, $id)
    {
        $daily_checklist = Schedule::findOrFail($id);
        $daily_checklist->update(['status' => $request['status'], 'approved_by' => auth()->user()->id]);
        $subject = '';
        $content = '';
        $managerNotificationContent = '';
        $notManagerNotificationContent = '';
        $fcmNotificationContent = '';
        switch ($request["status"]) {
            case "verified":
                $subject = "Checklist Verification Completed - " . $daily_checklist->task->name;
                $content = "<span>I am writing to inform you that the checklist assigned to you has been successfully verified. <br /><br /><b>Title:</b> " . $daily_checklist->task->name . " <br /><br />Your attention to detail and commitment to completing the checklist is appreciated. If you have any questions or if there are further steps to be taken, please feel free to reach out <a href='mailto:info@mychex.co.uk'>info@mychex.co.uk</a> <br /><br /> Thank you for your dedication and commitment.</span>";
                $managerNotificationContent = "Checklist Verified! The checklist " . $daily_checklist->task->name . " has been successfully verified. Manager, check your email for details.";
                $notManagerNotificationContent = "The checklist " . $daily_checklist->task->name . " has been successfully verified. Check your email for details.";
                $fcmNotificationContent = "The checklist " . $daily_checklist->task->name . " assigned has been successfully verified.";
                break;
            case "rejected":
                $subject = "Checklist Rejection - " . $daily_checklist->task->name;
                $content = "<span>I regret to inform you that the checklist assigned to you has been rejected <br /><br /><b>Title:</b> " . $daily_checklist->task->name . " <br /><br />Upon review, it was found that certain criteria were not met or there were errors present in the checklist. <br /><br />If you require further clarification on the rejection or assistance in addressing the issues identified, please do not hesitate to reach out <a href='mailto:info@mychex.co.uk'>info@mychex.co.uk</a> <br /><br />Thank you for your attention to this matter.</span>";
                $managerNotificationContent = "Checklist Rejected! The checklist " . $daily_checklist->task->name . " has been rejected. Managers, check your email for details. Coordinate with your team to address any issues.";
                $notManagerNotificationContent = "The checklist " . $daily_checklist->task->name . " has been rejected. Check your email for details and take necessary actions.";
                $fcmNotificationContent = "The checklist " . $daily_checklist->task->name . " assigned has been rejected.";
                break;
        }
        foreach ($daily_checklist->checklist_config->assignees as $user) {
            $this->generalService->sendEmail($user, $subject, $content);
        }
        $this->generalService->sendEmail(auth()->user(), 'Checklist Verification ' . ucfirst($request["status"]), "We hope this message finds you well. <br /><br />We wanted to inform you that the checklist assigned to your team has been successfully verified by the approver. Below are the details:<br /><b>Checklist Title: </b>" . $daily_checklist->task->name . "The verification process has been completed, and the checklist is now deemed satisfactory. If you have any further questions or require additional information, please feel free to reach out  <a href='mailto:info@mychex.co.uk'>info@mychex.co.uk</a><br /><br />Thank you for your attention to this matter. ");
        Notification::send($daily_checklist->checklist_config->assignees, new GeneralNotification($daily_checklist, 'Schedule', 'Update', $notManagerNotificationContent, $subject, $content));
        Notification::send(Auth::user(), new GeneralNotification($daily_checklist, 'Schedule', 'Update', $managerNotificationContent, $subject, $content));
        $this->generalService->sendFCMNotification($daily_checklist->checklist_config->assignees, "Schedule", $fcmNotificationContent);

        return $this->response('Schedule ' . $daily_checklist->status, $daily_checklist, 200);
    }

    public function checklistDetail(Request $request, $id)
    {
        // $checklist = Task::with(
        //     'category',
        //     'sub_category',
        //     'company',
        //     'sections',
        //     'sections.questions',
        //     'sections.questions.form_fields',
        //     'sections.questions.form_fields.field_values',
        //     'sections.questions.questionFormFields.answer'
        // )->findOrFail($id);
        // $daily_checklist = DailyChecklist::where('id', $request['daily_checklist'])->first();
        // $checklist['checklist_config'] =  $daily_checklist->checklist_config;
        // return $this->response('CheckList detail', $checklist, 200);
    }

    public function getDailyChecklistDetail($id)
    {
        // $daily_checklist = DailyChecklist::findOrFail($id);
        // return $this->response('CheckList detail', $daily_checklist, 200);
    }

    public function getAdminTemplates(Request $request)
    {
        $searchCols = ['name', 'priority', 'status', 'ref_id', 'admin_status'];
        $relationalCols = [
            'category' => ['name'],
            'sub_category' => ['name'],
            'company' => ['title'],
        ];

        $data = Task::with(['category', 'sub_category', 'sections', 'company'])->whereNull('company_id')->where('status', 'active');
        $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, '', $relationalCols);

        if ($request['perpage'] == 'all') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function getTaskRequests(Request $request)
    {
        $authRole = auth()->user()->roles->pluck('name')[0];
        if ($authRole != 'Super Admin') {
            abort(401, 'Unauthorized');
        }




        $searchCols = ['name', 'priority', 'status', 'ref_id', 'admin_status'];
        $relationalCols = [
            'category' => ['name'],
            'sub_category' => ['name'],
            'company' => ['title'],
        ];

        $data = Task::with(['category', 'mythBusters', 'sub_category', 'sections', 'company'])->whereNotNull('company_id');
        $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, '', $relationalCols);

        if (isset($request->status)) {
            $data =  $data->where('status', $request->status);
        }
        if (isset($request->priority)) {
            $data =  $data->where('priority', $request->priority);
        }

        if (isset($request->name)) {
            $data = $data->where('name', 'LIKE', "%{$request->name}%");
        }


        if (isset($request->category_ids) && is_array($request->category_ids) && count($request->category_ids)) {
            $data =  $data->whereIn('category_id', $request->category_ids);
        };

        if (isset($request->mythbuster_ids)) {
            $data = $data->with('mythBusters')->whereHas('mythBusters', function ($query) use ($request) {
                $query->whereIn('myth_busters.id', $request->mythbuster_ids);
            });
        }


        if (isset($request->range)) {
            if (isset($request->range['from'])) {
                $data = $data->whereDate('created_at', '>=', $request->range['from']);
            }
            if (isset($request->range['to'])) {
                $data = $data->whereDate('created_at', '<=', $request->range['to']);
            }
        }

        if ($request['perpage'] == 'all') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function getChecklistsToConfig()
    {
        $data = Task::where(function ($query) {
            $query->where(function ($subQuery) {
                $subQuery->where('company_id', Auth::user()->company_id)
                    ->orWhereNull('company_id');
            })
                ->where(function ($subQuery) {
                    $subQuery->whereIn('type', ['admine_template', 'custom_template'])
                        ->orWhere('type', '!=', 'risk_template');
                })
                ->where('status', 'active');
        })->get();
        return $data;
    }

    public function updateStatus(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'required|string|in:active,draft',
        ]);
        $riskAssessment = Task::find($id);
        $riskAssessment->update($validatedData);
        return response()->noContent();
    }

    public function getTasksForConfig()
    {
        $data = Task::where('type', 'custom_template')->where('company_id', Auth::user()->company_id)->where('status', 'active');
        $data = $data->orWhere('type', 'admin_template')->whereNull('company_id')->where('status', 'active');
        return $data->get();
    }

    public function taskClone(CloneTaskRequest $request)
    {
        $data = $request->validated();
        $this->service->cloneTask($data);
        return $this->response('Copy Succssfully', null, 200);
    }
}
