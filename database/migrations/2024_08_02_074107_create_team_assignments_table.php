<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('sprint_id')->nullable();
            // $table->unsignedBigInteger('phase_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->string('alloted_sprint')->nullable();
            $table->string('status_id')->nullable();
            
            $table->string('revision_number')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();

            $table->timestamps();
            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('sprint_id')->references('id')->on('sprints');
            // $table->foreign('phase_id')->references('id')->on('phases');
            $table->foreign('team_id')->references('id')->on('teams');
            // $table->foreign('status_id')->references('id')->on('global_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_assignments');
    }
}
