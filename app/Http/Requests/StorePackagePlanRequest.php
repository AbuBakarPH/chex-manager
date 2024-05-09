<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackagePlanRequest extends FormRequest
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
            'name'=> 'required' ,
            'allow_checklist'=> 'required' ,
            'allow_checklist_config'=> 'required' ,
            'allow_documents'=> 'required' ,
            'allow_risk'=> 'required' ,
            'allow_active_ip'=> 'required' ,
            'allow_users'=> 'required' ,
            'allow_teams'=> 'required' ,
            'price' => 'required' ,
            'plan_type' => 'required'
        ];
    }
}
