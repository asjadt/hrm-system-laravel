<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingUnpaidLeaveEmploymentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unpaid_leave_employment_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("setting_leave_id")->nullable();
            $table->foreign('setting_leave_id')->references('id')->on('setting_leaves')->onDelete('cascade');
            $table->unsignedBigInteger('employment_status_id')->nullable();
            $table->foreign('employment_status_id')->references('id')->on('employment_statuses')->onDelete('restrict');
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
        Schema::dropIfExists('setting_unpaid_leave_employment_statuses');
    }
}
