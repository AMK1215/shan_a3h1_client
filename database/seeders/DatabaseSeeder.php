<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PaymentTypeTableSeeder::class,
            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            SubAgentPermissionSeeder::class,
            PermissionRoleTableSeeder::class,
            UsersTableSeeder::class,
            RoleUserTableSeeder::class,
            GameTypeTableSeeder::class,
            GscPlusProductTableSeeder::class,
            GameTypeProductTableSeeder::class,      
        ]);
    }
}
