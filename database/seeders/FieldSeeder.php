<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Field;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $form_field = Field::where('name', 'checklist')->first();
        if (!$form_field) {
            Field::create([
                'name'          => 'checklist',
                'label'         => 'CheckBox',
                'placeholder'   => 'check the box',
                'type'          => 'checkbox',
                'status'        => 'active'
            ]);
        }
    }
}
