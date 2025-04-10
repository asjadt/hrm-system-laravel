<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');

            $table->unsignedBigInteger('service_plan_id');
            $table->foreign('service_plan_id')->references('id')->on('service_plans')->onDelete('cascade');

            $table->dateTime('start_date');
            $table->dateTime('end_date');

            $table->enum('status', ['active', 'canceled'])->default('active');


            $table->double('amount')->nullable();
            $table->timestamp('paid_at')->nullable();



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
        Schema::dropIfExists('business_subscriptions');
    }
}
