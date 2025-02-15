<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SectionQuestionRequest extends FormRequest
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
        return  [
            'section_id'            => 'required|integer',
            'title'                 => 'required|string|max:255',
            'status'                => 'required|in:active,in-active',
            'sort_no'               => 'required|integer',
            'guidance'              => 'nullable',
        ];
    }
}
