<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskSectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $rules = [
            'task_id' => 'required|integer',
            'title'         => 'required|string|max:255',
            'status'        => 'required|string',
            'description'   => 'nullable|string',
            'notes'         => 'nullable',
        ];
        
        if ($this->isMethod('post')) {
            $rules['slug'] = 'nullable|string|max:255|unique:check_list_sections,slug';
        }
        return $rules;
    }
}
