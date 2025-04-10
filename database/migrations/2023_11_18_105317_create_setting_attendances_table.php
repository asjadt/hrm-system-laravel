<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_attendances', function (Blueprint $table) {
            $table->id();
            $table->double('punch_in_time_tolerance')->nullable();
            $table->integer('work_availability_definition')->nullable();
            $table->boolean('punch_in_out_alert')->nullable();
            $table->integer('punch_in_out_interval')->nullable();
            $table->json('alert_area')->nullable();

            $table->string('service_name')->nullable();
            $table->text('api_key')->nullable();

            $table->boolean('auto_approval')->nullable();
            

            $table->boolean('is_geolocation_enabled')->nullable();



            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger("business_id")->nullable();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
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
        Schema::dropIfExists('setting_attendances');
    }
}
