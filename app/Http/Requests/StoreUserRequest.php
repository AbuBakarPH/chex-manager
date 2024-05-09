<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'company_id'    => 'nullable',
            'first_name'    => 'required',
            'last_name'     => 'required',
            'name'          => 'required',
            'email'         => 'required|unique:users',
            'password'      => 'nullable',
            // 'cnic'          => 'required',
            // 'phone'         => 'required',
            // 'address'       => 'required',
            'image_id'      => 'nullable',
            // 'category_id'   => 'nullable',
            // 'sub_category_id'=> 'nullable',
            'role'          => 'required',
            'org_role'      => 'nullable',
            'status'      => 'required',
        ];
    }
    
    // public function messages()
    // {
    //     return [
    //         // 'cnic.required' => 'The id is required.',
    //         'category_id.required' => 'The department is required.',
    //         'sub_category_id.required' => 'The sub department is required.',
    //     ];
    // }
}
