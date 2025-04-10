<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();


            $table->text('note')->nullable();
            $table->string('in_geolocation')->nullable();
            $table->string('out_geolocation')->nullable();

            $table->unsignedBigInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger("work_location_id");
            $table->foreign('work_location_id')->references('id')->on('work_locations')->onDelete('restrict');

          



            $table->date('in_date');

            $table->boolean("does_break_taken");

            $table->boolean("is_present");





            $table->double('capacity_hours');

            $table->enum('behavior', ['absent', 'late','regular','early']);


            $table->double('work_hours_delta');
            $table->double('regular_work_hours');
            $table->double('total_paid_hours');

            $table->enum('break_type', ['paid', 'unpaid']);
            $table->double('break_hours');


              $table->boolean('is_weekend');
            $table->unsignedBigInteger('holiday_id')->nullable();
            $table->unsignedBigInteger('leave_record_id')->nullable();


            $table->double('overtime_hours');
            $table->time('work_shift_start_at')->nullable();
            $table->time('work_shift_end_at')->nullable();
            $table->unsignedBigInteger('work_shift_history_id');
            $table->double('punch_in_time_tolerance')->nullable();

            $table->enum('status', ['pending_approval', 'approved','rejected'])->default("pending_approval");









            $table->boolean("is_active")->default(true);

            $table->unsignedBigInteger("business_id");
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');



            $table->double('regular_hours_salary');
            $table->double('overtime_hours_salary');


            $table->json('attendance_records');







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
        Schema::dropIfExists('attendances');
    }
}
