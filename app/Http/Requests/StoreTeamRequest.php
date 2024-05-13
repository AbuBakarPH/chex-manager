<?php

namespace App\Http\Requests;

use App\Rules\ValidUserIds;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreTeamRequest extends FormRequest
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

        $id = $this->route('team') ? $this->route('team') : null;

        return [
            'title'     => [
                'required',
                Rule::unique('teams')->where(function ($query) {
                    return $query->where('company_id', auth()->user()->company_id);
                })->ignore($id),
            ],
            'is_active' => 'required',
            'user_id' => ['required', 'array', new ValidUserIds],
            'start_time'=> 'required',
            'end_time'  => 'required|after:start_time',
            'org_role_id' => 'required|exists:organizational_roles,id',
            'category_id' => 'required|exists:categories,id',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User ID is required.',
            'user_id.array' => 'User ID must be an array.',
            'user_id.*' => 'Invalid user ID.',
            'category_id.required' => 'Category is required',
            'category_id.exists' => 'Category Id is invalid',
            'org_role_id.required' => 'Role is required',
            'org_role_id.exists' => 'Role Id is invalid',
        ];
    }
}
