<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("email");
            $table->string("phone");
            $table->integer("experience_years");

            $table->enum('education_level', [
                'no_formal_education',
                'primary_education',
                'secondary_education_or_high_school',
                'ged',
                'vocational_qualification',
                'bachelor_degree',
                'master_degree',
                'doctorate_or_higher'
            ])->nullable();

            $table->string("job_platform");



            $table->text("cover_letter")->nullable();
            $table->date("application_date");
            $table->date("interview_date")->nullable();
            $table->text("feedback");

            $table->enum('status', [
                'applied',
                'in_progress',
                'interview_stage_1',
                'interview_stage_2',
                'final_interview',
                'rejected',
                'job_offered',
                'hired'
            ])->default('applied');




            $table->unsignedBigInteger("job_listing_id");
            $table->foreign('job_listing_id')->references('id')->on('job_listings')->onDelete('restrict');
            $table->json('attachments')->nullable();

            $table->boolean("is_active")->default(true);
            $table->unsignedBigInteger("business_id");
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');

            $table->unsignedBigInteger("created_by")->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->softDeletes();
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
        Schema::dropIfExists('candidates');
    }
}
