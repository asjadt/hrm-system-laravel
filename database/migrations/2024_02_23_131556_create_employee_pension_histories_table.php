<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeePensionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_pension_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger("business_id");
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');



            $table->boolean('pension_eligible');
            $table->date('pension_enrollment_issue_date')->nullable();
            $table->json('pension_letters')->nullable();
            $table->enum('pension_scheme_status', ["opt_in", "opt_out"])->nullable();

            $table->date('pension_scheme_opt_out_date')->nullable();
            $table->date('pension_re_enrollment_due_date')->nullable();



            $table->date("from_date");
            $table->date("to_date")->nullable();
       

            $table->boolean("is_manual")->default(0);
            $table->unsignedBigInteger("created_by")->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
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
        Schema::dropIfExists('employee_pension_histories');
    }
}
