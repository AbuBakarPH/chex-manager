<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFieldRequest extends FormRequest
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
        $rules = [
            'label'         => 'required|string',
            'placeholder'   => 'string|nullable',
            'type'          => 'required|in:text,number,file,select,checkbox,date,textarea,texteditor,time',
            'file_type'     => 'nullable',
            'status'        => 'required|in:active,in-active',
        ];

        if ($this->isMethod('post') || $this->isMethod('put')) {
            $rules['name'] = 'required|string';
            if ($this->input('type') === 'select') {
                $rules['field_values'] = 'required';
            }
        }

        // If this is an update request (PUT or PATCH), add specific rules
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = array_merge($rules, [
                'label' => 'required|string',
                'status' => 'required|in:active,in-active',
                'placeholder' => 'string|nullable'
            ]);
        }

        return $rules;
    }
}
