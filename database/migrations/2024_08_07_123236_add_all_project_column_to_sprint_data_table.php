<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllProjectColumnToSprintDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sprint_data', function (Blueprint $table) {
            $table->string('all_project')->nullable();
            $table->string('serial_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sprint_data', function (Blueprint $table) {
            $table->dropColumn('all_project');
            $table->dropColumn('serial_number');
        });
    }
}
