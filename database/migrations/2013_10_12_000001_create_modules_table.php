<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->boolean('is_enabled')->default(false);







            $table->unsignedBigInteger("created_by")->nullable();



            $table->timestamps();
        });




        DB::table('modules')
        ->insert(array(
           

           [
            "name" => "user_activity",
            "is_enabled" => 1,
           ],
        ));





    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
    }
}
