<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use ProjectsDetails;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('projects')->insert([
            'code' => ProjectsDetails::PROJECT_CODE,
            'start_date' => '2024-01-07',
            'sprint_start_date' => '2024-01-07',
            'revision_number' => '1723273111',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'sheet_link' => 'https://docs.google.com/spreadsheets/d/15IOw4pKIoqpQfWOMqQDxjF55-_3mrKa3Ab9T3LkX1fU/edit?gid=278098346#gid=278098346',
            'sheet_name' => 'Actual Master',
            'sheet_range' => 'A29:DC',
            'no_of_days_in_sprint'=>ProjectsDetails::NO_OF_SPRINTS,
            'no_of_phases'=>ProjectsDetails::NO_OF_PAHSES,
            'no_of_resource_groups'=>ProjectsDetails::NO_OF_RESOURCE_GROUP,
        ]);
    }
}
