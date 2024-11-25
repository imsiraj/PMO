<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use UserRoles;
use SuperAdminDetails;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => SuperAdminDetails::SUPER_ADMIN_NAME,
            'email' => SuperAdminDetails::SUPER_ADMIN_EMAIL,
            'password' => bcrypt(SuperAdminDetails::SUPER_ADMIN_PASSWORD),
            'u_roles' => UserRoles::SUPER_ADMIN_ID,
            'status' => true,
        ]);
    }
}
