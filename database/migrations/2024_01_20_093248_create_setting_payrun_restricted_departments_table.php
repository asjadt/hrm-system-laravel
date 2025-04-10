<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingPayrunRestrictedDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_payrun_restricted_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("setting_payrun_id")->nullable();
            $table->foreign('setting_payrun_id')->references('id')->on('setting_payruns')->onDelete('cascade');
            $table->unsignedBigInteger("department_id");
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
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
        Schema::dropIfExists('setting_payrun_restricted_departments');
    }
}
