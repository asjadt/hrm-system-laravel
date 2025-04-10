<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeUserWorkShiftHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_user_work_shift_histories', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger("user_id")->nullable();
            $table->date("from_date");
            $table->date("to_date")->nullable();

            $table->unsignedBigInteger("work_shift_id")->nullable();



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
        Schema::dropIfExists('employee_user_work_shift_histories');
    }
}
