<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskSection;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;


/**
 * Class TaskService
 * @package App\Services
 */
class TaskService
{
    public function __construct(private Task $model, private TaskSectionService $sectionService, private SectionQuestionService $sectionQuestionService, private GeneralService $generalService)
    {
        // parent::__construct($model);
    }

    public function index($request)
    {
        $authRole = auth()->user()->roles->pluck('name')[0];

        $where = [];
        switch ($authRole) {
            case 'Super Admin':
                $whereIn['type'] = ['admin_template'];
                break;
            case 'Manager':
                $whereIn['type'] = ['custom_template'];
                break;
        }
        if (isset($request->type) && is_array($request->type) && count($request->type)) {
            $whereIn['type'] = $request->type;
            $whereIn['company_id'] = [Auth::user()->company_id, null];
        };

        // if (!in_array('admin_template', $whereIn['type'])) {
        //     array_push($where, ['key' => 'company_id', 'operator' => '=', 'value' => Auth::user()->company_id ?? null]);
        // }
        if (isset($request->category_id)) {
            array_push($where, ['key' => 'category_id', 'operator' => '=', 'value' => $request->category_id]);
        }
        // if (isset($request->sub_category_id)) {
        //     array_push($where, ['key' => 'sub_category_id', 'operator' => '=', 'value' => $request->sub_category_id]);
        // }
        if (isset($request->status)) {
            array_push($where, ['key' => 'status', 'operator' => '=', 'value' => $request->status]);
        }
        if (isset($request->priority)) {
            array_push($where, ['key' => 'priority', 'operator' => '=', 'value' => $request->priority]);
        }
        if (isset($request->name)) {
            array_push($where, ['key' => 'name', 'operator' => 'LIKE', 'value' => "%{$request->name}%"]);
        }


        $searchCols = ['name', 'priority', 'status', 'ref_id'];
        $relationalCols = [
            'category' => ['name'],
            'sub_category' => ['name'],
        ];

        $data = Task::with([
            'category',
            'sub_category',
            'sections',
            'sections.questions',
            'mythBusters',
            'org_role',
        ]);


        if ($authRole == 'Super Admin') {
            $data = $data->where('type', 'admin_template')->whereNull('company_id');
        }
        if ($authRole == 'Manager') {
            // It will fetch record with the following conditions
            // Condition 1: type = ['custom_template', 'admin_template'] 
            // Condition 2: company_id is Auth::user()->company_id 
            // Condition 3: when company_id is null than status should be active
            $data = $data->whereIn('type', ['custom_template', 'admin_template'])
                ->where(function ($query) {
                    $query->where('company_id', Auth::user()->company_id)->orWhere(function ($query) {
                        $query->whereNull('company_id')->where('status', 'active');
                    });
                });
        }

        if (isset($request->range_from)) {
            $data = $data->where('created_at', '>=', $request->range_from . ' 00:00:00');
        }

        if (isset($request->category_ids) && is_array($request->category_ids) && count($request->category_ids)) {
            $data =  $data->whereIn('category_id', $request->category_ids);
        };


        if (isset($request->mythbuster_ids)) {
            $data = $data->with('mythBusters')->whereHas('mythBusters', function ($query) use ($request) {
                $query->whereIn('myth_busters.id', $request->mythbuster_ids);
            });
        }

        if (isset($request->range_to)) {
            $data = $data->where('created_at', '<=', $request->range_to . ' 23:59:59');
        }


        $data = $this->generalService->handleWhere($data, $where);
        $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, '', $relationalCols);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function store($task, $sections)
    {
        $user = auth()->user();
        $task['created_by'] = $user->id;
        $task['ref_id'] = random_int(100000, 999999);
        $role =  $user->roles->pluck('name')[0];
        if ($role == 'Manager') {
            $task['company_id'] = auth()->user()->company_id;
            $task['type'] = 'custom_template';
        }
        if ($role == 'Super Admin') {
            $task['company_id'] = null;
            $task['type'] = 'admin_template';
            $task['admin_status'] = 'approved';
        }
        $checklist = Task::create($task);
        if (isset($task['myth_buster_ids'])) {
            $checklist->mythBusters()->attach($task['myth_buster_ids']);
            Auth::user()->company->mythBusters()->attach($task['myth_buster_ids']);
        }
        $subject = "Checklist Creation :" . $checklist->name;
        $content = "This is to inform you that a new checklist named " . $checklist->name . " a checklist has been successfully created on MyChex.<br /><br />Thank you for your action.";
        Notification::send($user, new GeneralNotification($checklist, 'Task', 'create', "You've successfully created the checklist: $checklist->name on MyChex.", $subject, $content));
        $this->generalService->sendEmail($user, $subject, $content);

        return $checklist;
    }

    public function update($request, $model)
    {
        $user = auth()->user();
        $role =  $user->roles->pluck('name')[0];
        $type = $request['type'];
        if ($role == 'Manager') {
            $request['type'] = 'custom_template';
            if ($type == 'risk_template') {
                $request['type'] = 'risk_template';
            }
        }
        $model->update($request->all());
        $model->mythBusters()->sync($request->myth_buster_ids);
        if ($user->company) {
            $user->company->mythBusters()->sync($request->myth_buster_ids);
        }
        $content = "I wanted to inform you that the checklist on MyChex has been updated. <br /><br /> <b>Checklist Title: " . $model->name . "</b><br />Should you have any questions or require clarification on the updated checklist, feel free to reach out <a href=" . '"info@mychex.co.uk"' . ">info@mychex.co.uk</a> <br /><br />Thank you for your attention.";
        $subject = 'Update: Revised Checklist on MyCheX';
        Notification::send($user, new GeneralNotification($model, 'Task', 'Update', "Update Alert: The checklist " . $model->name . " has been updated. Manager, please check your email for details and take necessary actions.", $subject, $content));
        $this->generalService->sendEmail($user, $subject, $content);

        return $model;
    }

    public function cloneTask($request)
    {
        $task = Task::where('id', $request['task_id'])->first();
        $newTask = $task->replicate();
        $validateTask = Task::where('name', $request['title'])->first();
        if (!$validateTask) {
            $newTask->name = $request['title'];
        } else {
            $newTask->name = $request['title'] . ' - duplicate';
        }
        $newTask->save();
        if ($task->mythBusters) {
            foreach ($task->mythBusters as $mythBuster) {
                $newTask->mythBusters()->attach($mythBuster->id);
            }
        }
        foreach ($task->sections as $section) {
            $newSection = $section->replicate();
            $newSection->task()->associate($newTask);
            $newSection->save();


            foreach ($section->questions as $question) {
                $newSectionQuestion = $question->replicate();
                $newSectionQuestion->section()->associate($newSection);
                $newSectionQuestion->save();

                $fields = [];
                $sub_questions = [];

                foreach ($question->sub_questions as $sub_question) {
                    $newSubSectionQuestion = $sub_question->replicate();
                    $newSubSectionQuestion->section()->associate($newSectionQuestion);
                    $newSubSectionQuestion->parent_id = $newSectionQuestion->id; // Set the new parent_id
                    $sub_questions[] = $newSubSectionQuestion;
                    // $newSubSectionQuestion->save();
                }

                foreach ($question->fields as $field) {
                    // $newField = $field->replicate();
                    // $newField->save();
                    $fields[] = [
                        'field_id' => $field->id,
                        'label' => $field->label,
                        'required' => $field->pivot->required,
                        'sort_no' => $field->pivot->sort_no,
                    ];
                    // $newSectionQuestion->fields()->attach($field->id);
                }


                $this->sectionQuestionService->handleFields($newSectionQuestion->id, $fields);
                $this->sectionQuestionService->handleSubQuestions($newSectionQuestion->id, $sub_questions);
            }
        }
        return true;
    }
}
