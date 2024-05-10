<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@gmail.com')->first();
        if (!$user){
            $user = User::create([
                'company_id'    => null,
                'first_name'    => 'Super',
                'last_name'     => 'Admin',
                'name'          => 'John Smith',
                'email'         => 'admin@gmail.com',
                'password'      => Hash::make("123456"),
                'cnic'          => '35202-23456-3',
                'phone'         => '123456789',
                'address'       => 'Johar Town, Lahore, Pakistan',
            ]);
        }
        $user->assignRole('Super Admin');
    }
}
