<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeSponsorshipHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_sponsorship_histories', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger("business_id");
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->date("date_assigned");
            $table->date("expiry_date");
            $table->enum('status', ['pending', 'approved', 'denied', 'visa_granted'])->default("pending");
            $table->text("note");
            $table->text("certificate_number");
            $table->enum('current_certificate_status', ['unassigned', 'assigned', 'visa_applied','visa_rejected','visa_grantes','withdrawal'])->default("unassigned");
            $table->boolean("is_sponsorship_withdrawn");


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
        Schema::dropIfExists('employee_sponsorship_histories');
    }
}
