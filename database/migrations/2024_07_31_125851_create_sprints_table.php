<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSprintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->string('task_id',50)->nullable();
            $table->text('title')->nullable();
            $table->string('complexity')->nullable();
            $table->longText('resources')->nullable();

            $table->string('sprint_estimation')->nullable();
            $table->string('sprint_adjustment')->nullable();
            $table->string('sprint_size')->nullable();
            $table->string('sprint_adhoc_task')->nullable();
            $table->string('sprint_total')->nullable();
            // $table->date('sprint_start_date')->nullable();
            // $table->date('sprint_end_date')->nullable();
            $table->string('status_id')->nullable();
            $table->string('priority_id')->nullable();
            $table->string('priority_id_stakeholder')->nullable();
            $table->text('comments')->nullable();
            $table->string('mobile_completion_status')->nullable();
            $table->string('sprint_number')->nullable();
            $table->longText('requirement')->nullable();
            $table->string('revision_number')->nullable();


            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();

            $table->timestamps();
            // $table->foreign('status_id')->references('id')->on('global_status');
            // $table->foreign('priority_id')->references('id')->on('priority');
            // $table->foreign('priority_id_stakeholder')->references('id')->on('priority');
            // $table->foreign('module_id')->references('id')->on('module_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sprints');
    }
}
