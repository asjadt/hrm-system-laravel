<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveRecordArrearsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_record_arrears', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("leave_record_id");
            $table->foreign('leave_record_id')->references('id')->on('leave_records')->onDelete('cascade');
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
        Schema::dropIfExists('leave_arrears');
    }
}
