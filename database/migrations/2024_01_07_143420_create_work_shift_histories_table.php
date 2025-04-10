<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkShiftHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('work_shift_histories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            $table->enum('type', ['regular', 'scheduled', 'flexible'])->default("regular");




            $table->date("from_date");
            $table->date("to_date")->nullable();

            $table->boolean("is_default")->default(false);

            $table->boolean("is_active")->default(true);
            $table->boolean("is_business_default")->default(false);
            $table->boolean("is_personal")->default(false);

            $table->enum('break_type', ['paid', 'unpaid']);
            $table->double("break_hours")->default(0.0);




            $table->unsignedBigInteger("business_id")->nullable();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');


            $table->unsignedBigInteger('work_shift_id')->nullable();





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
        Schema::dropIfExists('work_shift_histories');
    }
}
