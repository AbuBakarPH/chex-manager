<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolvedRiskRequest extends FormRequest
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
            'risk_id' => 'required|exists:question_risks,id',
            'image_id' => 'nullable',
            'description' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'risk_id.required' => 'The risk id required.',
            'risk_id.exists' => 'The selected risk id is invalid.',
            'description.required' => 'The description field is required.',
        ];
    }
}
