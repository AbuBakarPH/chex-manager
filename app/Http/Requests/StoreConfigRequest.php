<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConfigRequest extends FormRequest
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
            'name'    => 'nullable', // According to Munneb Bhai
            'task_id' => [
                'required',
                Rule::exists('tasks', 'id')->where(function ($query) {
                    $query->where('status', 'active');
                }),
            ],
            'repeat'    => 'required',
            'repeat_count'    => 'required',
            'repeat_start_dd'    => 'required',
            'team_id'    => 'nullable',
            'repeat_due_count'    => 'nullable',
            'staff_id'  => 'required|array|min:1',
            'is_active' => 'required',
            'approval' => 'required|array|min:1',
            'exceptional_days' => 'nullable|array',
            'exceptional_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'exceptional_dates' => 'nullable|array',
            'exceptional_dates.*' => 'nullable|string|date_format:Y-m-d',
        ];
    }

    public function messages()
    {
        return [
            'task_id' => 'Task Id is required, valid and active',
            'staff_id' => 'Staff is required from users list',
            'approval' => 'Approver is required',
            'exceptional_days.array' => 'The Exceptional Days field must be an array.',
            'exceptional_days.*.in' => 'The :attribute field must contain valid weekday names like Monday.',
            'exceptional_dates.array' => 'The Exceptional Dates field must be an array.',
            'exceptional_dates.*.date_format' => 'The :attribute field must contain a valid date format like YYYY-MM-DD.',
        ];
    }
}
