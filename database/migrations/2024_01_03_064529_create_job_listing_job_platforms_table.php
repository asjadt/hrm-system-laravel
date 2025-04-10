<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobListingJobPlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_listing_job_platforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("job_platform_id")->nullable();
            $table->foreign('job_platform_id')->references('id')->on('job_platforms')->onDelete('set null');
            $table->unsignedBigInteger("job_listing_id");
            $table->foreign('job_listing_id')->references('id')->on('job_listings')->onDelete('cascade');


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
        Schema::dropIfExists('job_listing_job_platforms');
    }
}
