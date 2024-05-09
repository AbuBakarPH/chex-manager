<?php

namespace App\Http\Requests;

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
            'user_id'   => 'required|array',
            'start_time'=> 'required',
            'end_time'  => 'required|after:start_time',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Please select the user.',
        ];
    }
}
