<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCandidateRecruitmentProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidate_recruitment_processes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('recruitment_process_id')->nullable();
            $table->foreign('recruitment_process_id')->references('id')->on('recruitment_processes')->onDelete('cascade');

            $table->unsignedBigInteger('candidate_id')->nullable();
            $table->foreign('candidate_id')->references('id')->on('candidates')->onDelete('cascade');

            $table->text("description")->nullable();
            $table->json('attachments')->nullable();

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
        Schema::dropIfExists('candidate_recruitment_processes');
    }
}
