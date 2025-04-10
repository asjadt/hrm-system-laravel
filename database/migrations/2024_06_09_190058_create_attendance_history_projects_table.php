<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceHistoryProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_history_projects', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("attendance_id");
            $table->foreign('attendance_id')->references('id')->on('attendance_histories')->onDelete('cascade');

            $table->unsignedBigInteger("project_id");
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');


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
        Schema::dropIfExists('attendance_history_projects');
    }
}
