<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAssetHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_asset_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("user_id")->nullable();


            $table->unsignedBigInteger("user_asset_id")->nullable();
            $table->foreign('user_asset_id')->references('id')->on('user_assets')->onDelete('set null');

            $table->unsignedBigInteger("business_id");
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');


            $table->string("name");
            $table->string("code");
            $table->string("serial_number");
            $table->boolean("is_working");
            $table->enum('status', ['available', 'assigned', 'returned', 'damaged', 'lost', 'reserved', 'repair_waiting'])->default('available');
            $table->string("type");
            $table->string("image")->nullable();
            $table->date("date");
            $table->text("note");


            $table->date("from_date");
            $table->date("to_date")->nullable();

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
        Schema::dropIfExists('user_asset_histories');
    }
}
