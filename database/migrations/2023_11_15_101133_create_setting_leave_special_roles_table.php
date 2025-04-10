<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingLeaveSpecialRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_leave_special_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("setting_leave_id")->nullable();
            $table->foreign('setting_leave_id')->references('id')->on('setting_leaves')->onDelete('cascade');
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
        Schema::dropIfExists('setting_leave_special_roles');
    }
}
