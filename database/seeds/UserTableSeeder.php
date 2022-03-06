<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customer = App\User::create([
            'first_name' => 'Customer',
            'middle_name' => '',
            'last_name' => 'User',
            'email' => 'customer@gmail.com',
            'phone_number' => '9860123450',
            'status' => 'Active',
            'password' => Hash::make('password')
        ]);

        $staff = App\User::create([
            'first_name' => 'Staff',
            'middle_name' => '',
            'last_name' => 'User',
            'email' => 'staff@gmail.com',
            'phone_number' => '9860123451',
            'status' => 'Active',
            'password' => Hash::make('password')
        ]);

        $admin = App\User::create([
            'first_name' => 'Admin',
            'middle_name' => '',
            'last_name' => 'User',
            'email' => 'admin@gmail.com',
            'phone_number' => '9860123452',
            'is_admin' => 1,
            'status' => 'Active',
            'password' => Hash::make('password')
        ]);

        $data = [[
            'user_id' => $customer->id,
            'role_id' => 1
        ], [
            'user_id' => $staff->id,
            'role_id' => 2
        ], [
            'user_id' => $admin->id,
            'role_id' => 3
        ]];
        foreach($data as $d) {
            App\UserRole::insert($d);
        }
    }
}
