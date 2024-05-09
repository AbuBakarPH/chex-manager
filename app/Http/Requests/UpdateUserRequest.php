<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
            'first_name'    => 'required',
            'last_name'     => 'required',
            'name'          => 'nullable',
            // 'email'         => 'required|email|unique:users,email,' . $this->user->id,
            'email'         =>
            [
                'required',
                Rule::unique('users')->ignore($this->id)
            ],
            'password'      => 'nullable',
            'cnic'          => 'required',
            'phone'         => 'nullable',
            'address'       => 'nullable',
            'image_id'      => 'nullable',
            'category_id'   => 'nullable',
            'sub_category_id' => 'nullable',
            'role'          => 'nullable',
            'org_role'      => 'nullable',
            'status'      => 'required',
        ];
    }

    public function messages()
    {
        return [
            'cnic.required' => 'The id is required.',
            'category_id.required' => 'The department is required.',
            'sub_category_id.required' => 'The sub department is required.',
        ];
    }
}
