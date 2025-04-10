<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobListingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->text("description");
            // $table->string("location");

            $table->float("minimum_salary");
            $table->float("maximum_salary");
            $table->string("experience_level");
            $table->unsignedBigInteger("job_type_id")->nullable();
            $table->foreign('job_type_id')->references('id')->on('job_types')->onDelete('set null');

            $table->unsignedBigInteger("work_location_id");
            $table->foreign('work_location_id')->references('id')->on('work_locations')->onDelete('restrict');


            $table->text("required_skills");
            $table->date("application_deadline");
            $table->date("posted_on");




            $table->unsignedBigInteger("department_id")->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
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
        Schema::dropIfExists('job_listings');
    }
}
