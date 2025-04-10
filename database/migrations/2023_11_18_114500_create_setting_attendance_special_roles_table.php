<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingAttendanceSpecialRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_attendance_special_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("setting_attendance_id")->nullable();
            $table->foreign('setting_attendance_id')->references('id')->on('setting_attendances')->onDelete('cascade');
            $table->unsignedBigInteger("role_id");
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
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
        Schema::dropIfExists('setting_attendance_special_roles');
    }
}
