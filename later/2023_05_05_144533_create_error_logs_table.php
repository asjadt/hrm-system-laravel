<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('logs')->create('error_logs', function (Blueprint $table) {
            $table->id();

            $table->string("api_url")->nullable();
            $table->text("fields")->nullable();
            $table->string("token")->nullable();


            $table->text("user")->nullable();
            $table->unsignedBigInteger("user_id")->nullable();


            $table->text("message")->nullable();
            $table->integer("status_code")->nullable();
            $table->string("line")->nullable();
            $table->string("file")->nullable();
            $table->string("ip_address")->nullable();


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
        Schema::dropIfExists('error_logs');
    }
}
