<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class AddUsersToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            DB::table('users')->insert([
                [
                    'name' => 'Palllav Purohit',
                    'email' => 'pallav@bombaysoftwares.com',
                    'password' => bcrypt('Qwerty@123'), // Hashing the password
                    'status' => true, // Setting status to 1
                ],
                [
                    'name' => 'Haresh Pansare',
                    'email' => 'haresh.pansare@bombaysoftwares.com',
                    'password' => bcrypt('Qwerty@123'), // Hashing the password
                    'status' => true, // Setting status to 1
                ],
                [
                    'name' => 'Indrajeet Marve',
                    'email' => 'indrajeet.marve@bombaysoftwares.com',
                    'password' => bcrypt('Qwerty@123'), // Hashing the password
                    'status' => true, // Setting status to 1
                ],
                [
                    'name' => 'Priti Kumari',
                    'email' => 'priti.kumari@bombaysoftwares.com',
                    'password' => bcrypt('Qwerty@123'), // Hashing the password
                    'status' => true, // Setting status to 1
                ],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            DB::table('users')->whereIn('email', [
                'pallav.purohit@bombaysoftwares.com',
                'haresh.pansare@bombaysoftwares.com',
                'indrajeet.marve@bombaysoftwares.com',
                'priti.kumari@bombaysoftwares.com'
            ])->delete();
        });
    }
}
