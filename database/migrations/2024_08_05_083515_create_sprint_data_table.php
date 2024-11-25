<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSprintDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sprint_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sprint_id')->nullable();
            $table->string('new_sr_no')->nullable(); 
            $table->string('new_module_screens')->nullable(); 
            $table->integer('sprint_number')->nullable();
            $table->string('team_name')->nullable(); 
            $table->string('module_screens')->nullable(); 
            $table->string('access_required_for_role')->nullable(); 
            $table->string('extra_column_one')->nullable(); 
            $table->string('extra_column_two')->nullable(); 
            $table->string('web_backend')->nullable(); 
            $table->string('priority_bs')->nullable(); 
            $table->string('responsible_owner')->nullable(); 
            $table->string('responsible_team')->nullable(); 
            $table->string('owner_or_team_comments')->nullable(); 
            $table->string('comments_post_discussion_with_stakeholder')->nullable(); 
            $table->string('derived_priority')->nullable(); 
            $table->string('member_size')->nullable(); 
            $table->string('in_app_communications')->nullable(); 
            $table->string('changes_done_in_actual_master')->nullable(); 
            $table->string('change_type')->nullable(); 
            $table->longText('description_of_changes')->nullable();
            $table->string('change_done_on_date')->nullable(); 
            $table->string('where_these_tasks_have_been_added')->nullable(); 
            $table->string('extra_column_three')->nullable(); 
            $table->string('extra_column_four')->nullable(); 
            $table->string('incomplete_basis_weightages')->nullable();
            $table->string('completed_basis_actual_progress')->nullable();
            $table->string('complete_status_percentage')->nullable();
            $table->string('incomplete_status_percentage')->nullable();
            $table->string('incomplete_basis_actual')->nullable();
            $table->string('incomplete_sprint_size_basis_weightage')->nullable();
            $table->string('feature')->nullable(); 
            $table->string('demo_given')->nullable();
            $table->date('demo_given_on')->nullable();
            $table->string('approved_by')->nullable(); 
            $table->string('approved_on')->nullable(); 
            $table->longText('feedback_by_stakeholder')->nullable();
            $table->longText('stakeholder_comments')->nullable();
            $table->longText('screenshot_link_one')->nullable();
            $table->longText('screenshot_link_two')->nullable();
            $table->longText('screenshot_link_three')->nullable();
            $table->longText('screenshot_link_four')->nullable();
            $table->longText('screenshot_link_five')->nullable();
            $table->string('uat_demo_status')->nullable(); 
            $table->longText('web_login_details')->nullable();
            $table->string('revision_number')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            
            $table->timestamps();
            $table->foreign('sprint_id')->references('id')->on('sprints');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sprint_data');
    }
}
