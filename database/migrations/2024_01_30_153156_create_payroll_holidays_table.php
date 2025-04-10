<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollHolidaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_holidays', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger("payroll_id");
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');

            $table->unsignedBigInteger("holiday_id");
            $table->foreign('holiday_id')->references('id')->on('holidays')->onDelete('cascade');




            $table->date("date");
            $table->double("hours");
            $table->double("hourly_salary");


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
        Schema::dropIfExists('payroll_holidays');
    }
}
