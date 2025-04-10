<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeRotaDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_rota_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_rota_id');
            $table->foreign('employee_rota_id')->references('id')->on('employee_rotas')->onDelete('cascade');
            $table->unsignedTinyInteger('day');
            $table->enum('break_type', ['paid', 'unpaid']);
            $table->double("break_hours")->default(0.0);
            $table->time('start_at')->nullable();
            $table->time('end_at')->nullable();


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
        Schema::dropIfExists('employee_rota_details');
    }
}
