<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceArrearsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_arrears', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("attendance_id");
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->enum('status', ['pending_approval', 'approved','rejected','completed'])->default("pending_approval");
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
        Schema::dropIfExists('attendance_arrears');
    }
}
