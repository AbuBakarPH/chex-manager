<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfigRequest extends FormRequest
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
            'repeat_count'    => 'required',
            'team_id'    => 'nullable',
            'staff_id'  => 'required|array|min:1',
            'is_active' => 'required|in:0,1',
            'approval' => 'required|array|min:1',
            'repeat_due_count'    => 'nullable',
            'exceptional_days' => 'nullable|array',
            'exceptional_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
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
            'is_active.in' => 'The status field must be either active or inactive.',
        ];
    }
}
