<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
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
            'first_name'    => 'required',
            'last_name'     => 'required',
            'image_id'      => 'nullable',
        ];

        if ($this->isMethod('post')) {
            $rules['email'] = 'required|unique:users';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'first_name.required' => 'First name required.',
            'last_name.required' => 'Last name required.',
            'email.required' => 'Email required.',
            'email.unique' => 'Email already exists.',
        ];
    }
}
