<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveRecordHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_record_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("leave_id");
            $table->foreign('leave_id')->references('id')->on('leave_histories')->onDelete('cascade');
            $table->date("date");
            $table->time("start_time");
            $table->time("end_time");
            $table->double("capacity_hours");
            $table->double("leave_hours");

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
        Schema::dropIfExists('leave_record_histories');
    }
}
