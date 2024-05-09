<?php

namespace App\Services;

use App\Models\Admin\Formfield;
use App\Models\Admin\QuestionFormField;
use App\Models\Field;
use App\Models\FieldSectionQuestion;
use App\Models\SectionQuestion;
use App\Services\Interfaces\SectionQuestionInterface;
use Illuminate\Support\Facades\Validator;

/**
 * Class SectionQuestionService
 * @package App\Services
 */
class SectionQuestionService extends GeneralService implements SectionQuestionInterface
{
    public function __construct(private SectionQuestion $model)
    {
        parent::__construct($model);
    }

    public function store($data)
    {
        $question = SectionQuestion::create($data);
        return $question;
    }

    public function update($data, $id)
    {
        $item = SectionQuestion::find($id);
        $question = $item->update($data);
        return $question;
    }

    public function assign_field_to_sub_question($field_id, $question_id)
    {
        $checklist_form_field = QuestionFormField::where('check_list_section_question_id', $question_id)->first();
        if (!$checklist_form_field) {
            $checklist_form_field = new QuestionFormField();
        }
        $checklist_form_field->form_field_id = $field_id;
        $checklist_form_field->required = 1;
        $checklist_form_field->sort_no = 1;
        $checklist_form_field->check_list_section_question_id = $question_id;
        $checklist_form_field->save();
    }
    public function show($id)
    {

        $checklistSectionQues = SectionQuestion::find($id);
        $questionFormFields = $checklistSectionQues->questionFormFields;
        return $checklistSectionQues;
    }
    public function destroy($model)
    {

        if (!$model) {
            return $this->error('CheckList section question not found', 404);
        }

        $model->delete();
    }

    public function handleFields($parentId, $fields)
    {
        // $parentId is Question ID here
        $validationRules = [
            'field_id' => 'required',
            'required' => 'required',
            'sort_no' => 'required',
            'section_question_id' => 'required',
        ];

        // Fetch IDs of active records associated with $parentId
        $activeFieldSectionQuestionIds = FieldSectionQuestion::where('section_question_id', $parentId)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        foreach ($fields as $field) {
            if (isset($field['pivot']['id'])) {
                $data = [
                    'sort_no' => $field['pivot']['sort_no'] ?? null,
                    'field_id' => $field['pivot']['field_id'] ?? null,
                    'required' => $field['pivot']['required'] ?? null,
                    'section_question_id' => $field['pivot']['section_question_id'] ?? null,
                ];
            } else {
                $data = [
                    'field_id' => $field['field_id'] ?? null,
                    'required' => $field['required'] ?? null,
                    'sort_no' => $field['sort_no'] ?? null,
                    'section_question_id' => $parentId ?? null,
                ];
            }

            $validator = Validator::make($data, $validationRules);
            if ($validator->fails()) {
                continue;
            }

            if (isset($field['pivot']['id'])) {
                FieldSectionQuestion::where('id', $field['pivot']['id'])->update($data);
            } else {
                FieldSectionQuestion::create($data);
            }

            // Remove the ID from $activeFieldSectionQuestionIds if present in the field pivot
            if (isset($field['pivot']['id']) && in_array($field['pivot']['id'], $activeFieldSectionQuestionIds)) {
                $key = array_search($field['pivot']['id'], $activeFieldSectionQuestionIds);
                unset($activeFieldSectionQuestionIds[$key]);
            }
        }

        // Update records that are associated with $parentId but not present in $activeFieldSectionQuestionIds
        FieldSectionQuestion::where('section_question_id', $parentId)
            ->whereIn('id', $activeFieldSectionQuestionIds)
            ->where('status', 'active')
            ->update(['status' => 'in-active']);
    }

    public function handleSubQuestions($parentId, $questions)
    {
        // $parentId is Question ID here
        $validationRules = [
            'section_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,in-active',
            'sort_no' => 'required|integer',
            'guidance' => 'nullable',
        ];

        // Fetch IDs of active records associated with $parentId
        $activeSubQuestionIds = SectionQuestion::where('parent_id', $parentId)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        foreach ($questions as $question) {
            $data = [
                'title' => $question['title'] ?? null,
                'sort_no' => $question['sort_no'] ?? null,
                'guidance' => $question['guidance'] ?? "",
                'section_id' => $question['section_id'] ?? null,
                'parent_id' => $parentId ?? null,
                'status' => "active",
            ];

            $validator = Validator::make($data, $validationRules);

            if ($validator->fails()) {
                continue;
            }

            if (isset($question['id'])) {
                SectionQuestion::where('id', $question['id'])->update($data);
            } else {
                $item = SectionQuestion::create($data);
                $this->assignCheckboxToSubQuestion($item->id);
            }

            if (isset($question['id']) && in_array($question['id'], $activeSubQuestionIds)) {
                $key = array_search($question['id'], $activeSubQuestionIds);
                unset($activeSubQuestionIds[$key]);
            }
        }

        // Update records that are associated with $parentId but not present in $activeSubQuestionIds
        SectionQuestion::where('parent_id', $parentId)
            ->whereIn('id', $activeSubQuestionIds)
            ->where('status', 'active')
            ->update(['status' => 'in-active']);
    }

    public function assignCheckboxToSubQuestion($questionId)
    {
        $validationRules = [
            'field_id' => 'required',
            'required' => 'required',
            'section_question_id' => 'required',
        ];

        $checkbox = Field::where('name', 'checklist')->first();

        $data = [
            'required' => 1,
            'field_id' => $checkbox->id ?? null,
            'section_question_id' => $questionId ?? null,
        ];

        $validator = Validator::make($data, $validationRules);
        if (!$validator->fails()) {
            FieldSectionQuestion::create($data);
        }
    }
}
