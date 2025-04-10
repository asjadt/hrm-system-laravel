<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollLeaveRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_leave_records', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("payroll_id");
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');

            $table->unsignedBigInteger("leave_record_id");
            $table->foreign('leave_record_id')->references('id')->on('leave_records')->onDelete('cascade');

            // $table->date("date");
            // $table->time("start_time");
            // $table->time("end_time");
            // $table->double("leave_hours");

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
        Schema::dropIfExists('payroll_leave_records');
    }
}
