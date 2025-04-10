<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payruns', function (Blueprint $table) {
            $table->id();
            $table->enum('period_type', ['weekly', 'monthly', 'customized'])->default('weekly');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('consider_type', ['hour', 'daily_log','none']);
            $table->boolean('consider_overtime');
            $table->text('notes')->nullable();


            $table->boolean('is_active');


            $table->unsignedBigInteger("created_by")->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->timestamps(); // Created_at and updated_at columns



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payruns');
    }
}
