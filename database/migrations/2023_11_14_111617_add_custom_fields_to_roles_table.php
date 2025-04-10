<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomFieldsToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_system_default')->default(false);
            $table->boolean('is_default_for_business')->default(false);
            $table->string('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('business_id');
            $table->dropColumn('is_default');
            $table->dropColumn('is_system_default');
            $table->dropColumn('is_default_for_business');


        });
    }
}
