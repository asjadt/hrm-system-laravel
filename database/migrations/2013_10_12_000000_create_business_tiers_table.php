<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBusinessTiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_tiers', function (Blueprint $table) {
            $table->id();
             $table->string('name');

             $table->boolean("is_active")->default(false);
             $table->unsignedBigInteger("created_by")->nullable();
             $table->softDeletes();
            $table->timestamps();
        });

        DB::table('business_tiers')
        ->insert(array(
           [
            "name" => "Basic",
            "is_active" => 1,
            'created_by' => NULL
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
        Schema::dropIfExists('business_tiers');
    }
}
