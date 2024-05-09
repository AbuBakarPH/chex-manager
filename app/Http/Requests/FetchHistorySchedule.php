<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HistoryScheduleDateRange;

class FetchHistorySchedule extends FormRequest
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
            'category_id' => 'nullable|exists:categories,id',
            'date_to' => ['nullable', new HistoryScheduleDateRange],
            'date_from' => ['nullable', new HistoryScheduleDateRange],
        ];
    }

    public function messages()
    {
        return [
            'date_to' => 'End date should be greater than yesterday',
            'date_from' => 'Start date should be greater than yesterday',
        ];
    }
}
