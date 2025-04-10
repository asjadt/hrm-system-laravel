<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_projects', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("attendance_id");
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');

            $table->unsignedBigInteger("project_id")->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');


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
        Schema::dropIfExists('attendance_projects');
    }
}
