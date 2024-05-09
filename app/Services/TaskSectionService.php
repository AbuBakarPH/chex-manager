<?php

namespace App\Services;

use App\Models\TaskSection;
use App\Models\SectionQuestion;
use Illuminate\Support\Str;

/**
 * Class TaskSectionService
 * @package App\Services
 */

class TaskSectionService
{
    public function __construct(private TaskSection $model, private SectionQuestionService $questionService, private GeneralService $generalService)
    {
        // parent::__construct($model);
    }

    public function store($section, $questions)
    {
        $section['slug']  = Str::slug($section['title']);
        $checkListSection       = TaskSection::create($section);

        // if (count($questions)) {
        //     foreach ($questions as $question) {
        //         $validatedData = [
        //             'check_list_section_id' => $question['check_list_section_id'],
        //             'title'                 => $question['title'],
        //             'status'                => $question['status'],
        //             'sort_no'               => $question['sort_no'],
        //             'guidance'              => $question['guidance'],
        //             'form_fields'           => $question['form_fields'],
        //             'deletedIds'            => $question['deletedIds'] ?? array(),
        //         ];
        //         if (isset($question['id'])) {
        //             // update
        //             $checkListSectionQues = SectionQuestion::find($question['id']);
        //             $this->questionService->update($validatedData, $checkListSectionQues, true);
        //         } else {
        //             // create
        //             $this->questionService->store($validatedData, true);
        //         }
        //     }
        // }

        return $checkListSection->load('questions');
    }

    public function show($id)
    {
        $checkListSection = TaskSection::find($id);
        return $checkListSection;
    }

    public function update($request, $model)
    {
        $checkListSection = $request->validated();
        $model->update($checkListSection);

        // if (isset($request->questions) && count($request->questions)) {
        //     foreach ($request->questions as $question) {
        //         $validatedData = [
        //             'check_list_section_id' => $question['check_list_section_id'],
        //             'title'                 => $question['title'],
        //             'status'                => $question['status'],
        //             'sort_no'               => $question['sort_no'],
        //             'guidance'              => $question['guidance'],
        //             'form_fields'           => $question['form_fields'],
        //             'deletedIds'            => $question['deletedIds'] ?? array(),
        //         ];
        //         if (isset($question['id'])) {
        //             // update
        //             $checkListSectionQues = SectionQuestion::find($question['id']);
        //             $this->questionService->update($validatedData, $checkListSectionQues, true);
        //         } else {
        //             // create
        //             $this->questionService->store($validatedData, true);
        //         }
        //     }
        // }

        // if (isset($request->deletedQuestions) && count($request->deletedQuestions)) {
        //     SectionQuestion::whereIn('id', $request->deletedQuestions)->delete();
        // }

        return $model;
    }

    public function destroy($model)
    {

        if (!$model) {
            return $this->error('CheckList section not found', 404);
        }

        $model->delete();
    }
}
