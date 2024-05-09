<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
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
            'title'         => 'required|string|max:255',
            'shifts'        => 'required|string|max:255',
            'company_address' => 'required|string|max:255',
            'company_phone' => 'required|string|max:255',
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'start_time'     => 'required',
            'end_time'     => 'required|after:start_time',
            'phone'         => 'required|string|max:255',
            'address'       => 'required|string|max:255',
            // 'cnic'          => 'required|string|max:255',
            // 'package_plan_id' => 'required',
        ];
        if ($this->isMethod('post')) {
            $rules = array_merge($rules, [
                'company_email' => 'required|email|unique:companies,email',
                'email' => 'required|email|unique:users,email',
            ]);
        }

        return $rules;
    }
    public function messages()
    {
        return [
            'email'          => 'The manager email has already been taken.',
            // 'cnic.required'          => 'The id field is required.',
        ];
    }
}
