<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_Name');
            $table->string('middle_Name')->nullable();
            $table->string('last_Name');


            $table->string('NI_number')->nullable();

            $table->boolean('pension_eligible')->nullable();



            $table->string('user_name')->nullable();

            $table->json('emergency_contact_details')->nullable();

            $table->string('color_theme_name')->default("default");

            $table->string('user_id')->nullable();

            // $table->string('stripe_id')->nullable();


            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            $table->boolean('is_in_employee')->nullable()->default(false);

            $table->unsignedBigInteger('designation_id')->nullable();
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('set null');

            $table->unsignedBigInteger('employment_status_id')->nullable();
            $table->foreign('employment_status_id')->references('id')->on('employment_statuses')->onDelete('set null');

            $table->date('joining_date')->nullable()->default(today());
            $table->date('date_of_birth')->nullable();
            $table->double('salary_per_annum')->nullable()->default(0);
            $table->double('weekly_contractual_hours')->nullable()->default(0);
            $table->integer('minimum_working_days_per_week')->nullable()->default(0);
            $table->double('overtime_rate')->nullable()->default(0.0);

            $table->double('is_active_visa_details')->nullable()->default(0);
            $table->double('is_active_right_to_works')->nullable()->default(0);



            $table->string('phone')->nullable();
            $table->string('image')->nullable();

            $table->string("address_line_1")->nullable();
            $table->string("address_line_2")->nullable();
            $table->string("country")->nullable();
            $table->string("city")->nullable();
            $table->string("postcode")->nullable();
            $table->string("lat")->nullable();
            $table->string("long")->nullable();

            $table->string('email')->unique();
            $table->string('email_verify_token')->nullable();
            $table->string('email_verify_token_expires')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('resetPasswordToken')->nullable();
            $table->string('resetPasswordExpires')->nullable();

            $table->string('site_redirect_token')->nullable();


            $table->integer('login_attempts')->default(0);
            $table->dateTime('last_failed_login_attempt_at')->nullable();


            $table->string("background_image")->nullable();



            $table->enum('immigration_status', ['british_citizen', 'ilr', 'immigrant', 'sponsored'])->nullable();
            $table->boolean('is_sponsorship_offered')->default(0)->nullable();




            $table->unsignedBigInteger("recruitment_process_id")->nullable();
            $table->foreign('recruitment_process_id')->references('id')->on('recruitment_processes')->onDelete('restrict');





            $table->unsignedBigInteger("bank_id")->nullable();
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('restrict');
            $table->string("sort_code")->nullable();
            $table->string("account_number")->nullable();
            $table->string("account_name")->nullable();






            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger("business_id")->nullable(true);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
      $table->unsignedBigInteger("created_by")->nullable();
            $table->foreign('created_by')
        ->references('id')
        ->on('users')
        ->onDelete('set null');

            $table->softDeletes();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
