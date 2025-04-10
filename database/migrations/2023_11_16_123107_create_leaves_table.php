<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->enum('leave_duration', ['single_day', 'multiple_day', 'half_day','hours']);
            $table->enum('day_type', ['first_half', 'last_half'])->nullable();
            $table->unsignedBigInteger("leave_type_id");
            $table->foreign('leave_type_id')->references('id')->on('setting_leave_types')->onDelete('restrict');
            $table->unsignedBigInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->date('date')->nullable();
            $table->string('note');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->enum('status', ['pending_approval','in_progress', 'approved','rejected'])->default("pending_approval");
            $table->double('hourly_rate');
            $table->json('attachments')->nullable();

            $table->boolean("is_active")->default(true);
            $table->unsignedBigInteger("business_id");
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
        Schema::dropIfExists('leaves');
    }
}
