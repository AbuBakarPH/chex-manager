<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;

class CloneTaskRequest extends FormRequest
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
            'title' => 'required',
            'task_id' => 'required|exists:tasks,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title field is required.',
            'task_id.required' => 'Checklist id is required.',
            'task_id.exists' => 'The selected checklist id is invalid.',
        ];
    }
}
