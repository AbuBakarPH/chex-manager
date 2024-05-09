<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskSectionRequest extends FormRequest
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

        return [
            'check_list_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('check_list_sections')->ignore($this->id),
            ],
        ];
    }
}
