<?php

use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [[
            'id' => 1,
            'name' => 'Customer',
        ],
        [
            'id' => 2,
            'name' => 'Staff',
        ],
        [
            'id' => 3,
            'name' => 'Admin',
        ]];
        foreach($data as $d) {
            App\Role::insert($d);
        }
    }
}
