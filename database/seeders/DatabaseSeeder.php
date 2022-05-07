<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
             MetaTagSeeder::class,
             PermissionsTableSeeder::class,
             // CountriesSeeder::class,
               RolesTableSeeder::class,
               UsersTableSeeder::class,
              RoleUserTableSeeder::class,
               PermissionRoleTableSeeder::class,
               DummyDataSeeder::class,
        ]);
    }
}
