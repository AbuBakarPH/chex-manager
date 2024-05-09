<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class QuestionRiskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'due_date' => 'required',
            'priority' => 'required',
            'description' => 'required',
            'status' => 'required|in:draft,in_progress,resolved,rejected',
            'section_question_id' => 'required|exists:section_questions,id',
            'assignees' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    $exists = User::whereIn('id', $value)
                        ->where('company_id', auth()->user()->company_id)
                        ->exists();

                    if (!$exists) {
                        $fail('One or more of the provided user IDs do not exist in the company.');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'question_id.required' => 'Question Id is required',
            'question_id.exists' => 'Question Id is not valid',
            'assignees.required' => 'Atleast one assignee is required',
            'status.in' => 'Status value should be valid',
        ];
    }
}
