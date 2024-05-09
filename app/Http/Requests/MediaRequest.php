<?php

namespace App\Http\Requests;

use App\Rules\ValidFile;
use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest
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
    public function rules()
    {
        return [
            'file' => ['required', new ValidFile],
            // Other rules for your request...
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'The file is required.',
            'file.max' => 'The file must not be larger than 2048 kilobytes.',
            // Add more custom messages for other rules as needed...
        ];
    }

}
