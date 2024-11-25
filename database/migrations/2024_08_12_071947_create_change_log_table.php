<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_log', function (Blueprint $table) {
            $table->id();
            $table->string('project_id')->nullable();
            $table->string('revision_number')->nullable();
            $table->longText('sprint_id')->nullable();
            $table->string('column_name')->nullable();
            $table->longText('before_data')->nullable();
            $table->longText('after_data')->nullable();
            $table->dateTime('date_time')->nullable();
            $table->integer('row_number')->nullable();
            $table->boolean('is_updated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('change_log');
    }
}
