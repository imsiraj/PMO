<?php

use App\Models\Projects;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
// use ProjectsDetails;
class InsertInitialDataIntoProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Projects::where('code', ProjectsDetails::PROJECT_CODE)
            ->update([
                'no_of_days_in_sprint' => ProjectsDetails::NO_OF_SPRINTS,
                'no_of_phases' => ProjectsDetails::NO_OF_PAHSES,
                'no_of_resource_groups' => ProjectsDetails::NO_OF_RESOURCE_GROUP,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('projects')->where('code', ProjectsDetails::PROJECT_CODE)->update([
            'no_of_days_in_sprint' => null,
            'no_of_phases' => null,
            'no_of_resource_groups' => null,
        ]);
    }
}
