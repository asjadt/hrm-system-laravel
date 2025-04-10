<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('logs')->create('activity_logs', function (Blueprint $table) {
            $table->id();


            
            $table->string("api_url")->nullable();
            $table->text("fields")->nullable();
            $table->string("token")->nullable();



            $table->text("user")->nullable();
            $table->unsignedBigInteger("user_id")->nullable();
            $table->text("activity")->nullable();
            $table->text("description")->nullable();
            $table->string("ip_address")->nullable();
            $table->string("request_method")->nullable();
            $table->string("device")->nullable();


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
        Schema::dropIfExists('activity_logs');
    }
}
