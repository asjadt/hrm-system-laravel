<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessTierModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_tier_modules', function (Blueprint $table) {
            $table->id();



            $table->boolean('is_enabled')->default(false);


            $table->unsignedBigInteger("business_tier_id")->nullable();
            $table->foreign('business_tier_id')->references('id')->on('business_tiers')->onDelete('cascade');

            $table->unsignedBigInteger("module_id")->nullable();
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');

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
        Schema::dropIfExists('business_tier_modules');
    }
}
