<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $roles = [
            [
                'name' => 'Administrator',
                'description' => 'Full system access with all permissions',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Master',
                'description' => 'Master user with advanced permissions',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Employee',
                'description' => 'Basic employee access for daily operations',
                'deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}
