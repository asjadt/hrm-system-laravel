<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("payroll_id");
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');

            $table->unsignedBigInteger("attendance_id");
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');


            // $table->boolean('is_weekend');
            // $table->unsignedBigInteger('holiday_id')->nullable();
            // $table->unsignedBigInteger('leave_record_id')->nullable();

            
            // $table->double('overtime_hours')->nullable();



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
        Schema::dropIfExists('payroll_attendances');
    }
}
