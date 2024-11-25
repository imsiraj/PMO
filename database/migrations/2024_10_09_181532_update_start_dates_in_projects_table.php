<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateStartDatesInProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('projects')->update([
            'start_date' => '2024-07-01',
            'sprint_start_date' => '2024-07-01',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('projects')->update([
            'start_date' => '2024-07-01', // or set to a previous date if needed
            'sprint_start_date' => '2024-07-01', // or set to a previous date if needed
        ]);
    }
}
