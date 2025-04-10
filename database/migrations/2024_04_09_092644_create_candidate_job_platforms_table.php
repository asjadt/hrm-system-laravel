<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCandidateJobPlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidate_job_platforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("job_platform_id")->nullable();
            $table->foreign('job_platform_id')->references('id')->on('job_platforms')->onDelete('set null');
            $table->unsignedBigInteger("candidate_id");
            $table->foreign('candidate_id')->references('id')->on('candidates')->onDelete('cascade');
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
        Schema::dropIfExists('candidate_job_platforms');
    }
}
