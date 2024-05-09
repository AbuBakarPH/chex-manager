<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
            'name'                  => 'required',
            'category_id'           => 'required|exists:categories,id',
            // 'sub_category_id'   => 'required|exists:categories,id',
            'priority'              => 'required|string|max:255',
            'status'                => 'required|in:active,in-active,draft',
            'description'           => 'required|string',
            'myth_buster_ids.*'     => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'category_id.exists' => 'The selected category is invalid',
            // 'sub_category_id.exists' => 'The selected sub category is invalid',
            'category_id.required' => 'The category field is required.',
            // 'sub_category_id.required' => 'The sub category field is required.',
        ];
    }
}
