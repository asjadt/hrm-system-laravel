<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkShiftDetailHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_shift_detail_histories', function (Blueprint $table) {
            $table->id();



            $table->unsignedBigInteger('work_shift_id');
            $table->foreign('work_shift_id')->references('id')->on('work_shift_histories')->onDelete('cascade');


            $table->unsignedTinyInteger('day');
            $table->boolean('is_weekend');
            $table->time('start_at')->nullable();
            $table->time('end_at')->nullable();



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
        Schema::dropIfExists('work_shift_detail_histories');
    }
}
