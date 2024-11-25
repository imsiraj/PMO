<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('title',255)->nullable();
            $table->string('members')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            // $table->float('alloted_sprint',8,2)->nullable();
            // $table->unsignedBigInteger('status_id')->nullable();
            $table->string('revision_number')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();


            $table->timestamps();
            $table->foreign('project_id')->references('id')->on('projects');
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
        Schema::dropIfExists('teams');
    }
}
