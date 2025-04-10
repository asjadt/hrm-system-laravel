<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicePlanDiscountCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_plan_discount_codes', function (Blueprint $table) {
            $table->id();
    $table->string('code')->unique();
    $table->double('discount_amount');
    $table->unsignedBigInteger('service_plan_id');
    $table->foreign('service_plan_id')->references('id')->on('service_plans')->onDelete('cascade');
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
        Schema::dropIfExists('service_plan_discount_codes');
    }
}
