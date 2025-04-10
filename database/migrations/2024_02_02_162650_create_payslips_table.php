<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayslipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->unsignedBigInteger("payroll_id")->nullable();
            $table->foreign('payroll_id')
                    ->references('id')
                    ->on('payrolls')
                    ->onDelete('cascade');
            $table->enum('payment_method', ['bank_transfer', 'cash', 'cheque', 'other']);
            $table->integer('month');
            $table->integer('year');
            $table->double('payment_amount');
            $table->text('payment_notes')->nullable();
            $table->date('payment_date');
            $table->string('payslip_file')->nullable();
            $table->json('payment_record_file')->nullable();
            $table->double('gross_pay')->default(0);
            $table->double('tax')->default(0);
            $table->double('employee_ni_deduction')->default(0);
            $table->double('employer_ni')->default(0);


             // Bank details
             $table->unsignedBigInteger("bank_id")->nullable();
             $table->foreign('bank_id')->references('id')->on('banks')->onDelete('restrict');
             $table->string("sort_code")->nullable();
             $table->string("account_number")->nullable();
             $table->string("account_name")->nullable();





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
        Schema::dropIfExists('payslips');
    }
}
