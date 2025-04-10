<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_plans', function (Blueprint $table) {
            $table->id();


            $table->string('name');
            $table->text('description')->nullable();
            $table->double('set_up_amount');
            $table->double('duration_months');
            $table->double('price');
            $table->unsignedBigInteger('business_tier_id');
            $table->foreign('business_tier_id')->references('id')->on('business_tiers')->onDelete('cascade');




            $table->unsignedBigInteger("created_by")->nullable();



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
        Schema::dropIfExists('service_plans');
    }
}
