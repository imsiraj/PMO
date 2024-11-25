<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country_phonecode',4)->nullable();
            $table->string('mobile_number',15)->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('u_roles')->nullable();
            $table->boolean('status')->default(false);
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
            $table->dropcolumn('country_phonecode');
            $table->dropcolumn('mobile_number');
            $table->dropcolumn('mobile_verified_at');
            $table->dropcolumn('u_roles');
            $table->dropcolumn('status');
        });
    }
}
