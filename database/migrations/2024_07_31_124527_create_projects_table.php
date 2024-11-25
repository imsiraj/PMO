<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code',50);
            $table->string('title',255)->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('reporting_pm')->nullable();
            $table->unsignedBigInteger('reporting_ba')->nullable();
            $table->string('members')->nullable();
            $table->unsignedBigInteger('priority_id')->nullable();
            $table->string('documentation')->nullable();
            $table->string('release_notes')->nullable();
            $table->json('web_data')->nullable();
            $table->json('ios_app_data')->nullable();
            $table->json('android_app_data')->nullable();
            $table->json('dev_ops_data')->nullable();
            $table->date('start_date')->nullable();
            $table->json('tech_stack')->nullable();
            $table->float('total_sprints',4,2)->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('sprint_start_date')->nullable();
            $table->string('revision_number')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('reporting_pm')->references('id')->on('users');
            $table->foreign('reporting_ba')->references('id')->on('users');
            $table->foreign('priority_id')->references('id')->on('priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
