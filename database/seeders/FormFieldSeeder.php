<?php

namespace Database\Seeders;

use App\Models\Admin\FieldValue;
use App\Models\Admin\Formfield;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FormFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $form_field = Formfield::where('name', 'checklist')->first();
        if (!$form_field){
            Formfield::create([
                'name'          => 'checklist',
                'label'         => 'CheckBox',
                'placeholder'   => 'check the box',
                'type'          => 'checkbox',
                'status'        => 'active'
            ]);
        }
        $risk_consequence_fields = Formfield::where('name','consequence')->first();
        if (!$risk_consequence_fields){
            $field = Formfield::create([
                'name'          => 'consequence',
                'label'         => 'Consequence',
                'placeholder'   => 'Select consequence',
                'type'          => 'select',
                'status'        => 'active'
            ]);
            $field_values = [
              [
                  'label' => 'Negligible',
                  'value' => 'negligible',
                  'form_field_id' => $field->id,
              ],[
                  'label' => 'Minor',
                  'value' => 'minor',
                  'form_field_id' => $field->id,
              ],[
                  'label' => 'Moderate',
                  'value' => 'moderate',
                  'form_field_id' => $field->id,
              ],[
                  'label' => 'Major',
                  'value' => 'major',
                  'form_field_id' => $field->id,
              ],[
                  'label' => 'Catastrophic',
                  'value' => 'catastrophic',
                  'form_field_id' => $field->id,
              ],
            ];
            FieldValue::insert($field_values);
        }
        $risk_likelihood_score_fields = Formfield::where('name','likelihood_score')->first();

        if (!$risk_likelihood_score_fields){
            $field = Formfield::create([
                'name'          => 'likelihood_score',
                'label'         => 'Likelihood Score',
                'placeholder'   => 'Select Likelihood Score',
                'type'          => 'select',
                'status'        => 'active'
            ]);
            $field_values = [
                [
                    'label' => 'Rare',
                    'value' => 'rare',
                    'form_field_id' => $field->id,
                ],[
                    'label' => 'Unlikely',
                    'value' => 'unlikely',
                    'form_field_id' => $field->id,
                ],[
                    'label' => 'Possible',
                    'value' => 'possible',
                    'form_field_id' => $field->id,
                ],[
                    'label' => 'Likely',
                    'value' => 'likely',
                    'form_field_id' => $field->id,
                ],[
                    'label' => 'Almost Certain',
                    'value' => 'almost_certain',
                    'form_field_id' => $field->id,
                ],
            ];
            FieldValue::insert($field_values);
        }

        $form_field = Formfield::where('name', 'additional_notes')->first();
        if (!$form_field){
            Formfield::create([
                'name'          => 'additional_notes',
                'label'         => 'Additional Notes',
                'placeholder'   => 'Enter Additional Notes',
                'type'          => 'text',
                'status'        => 'active'
            ]);
        }
    }
}
