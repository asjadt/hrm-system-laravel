<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("entity_id");
            $table->json("entity_ids")->nullable();



            $table->unsignedBigInteger("entity_name");

            $table->text("notification_title");
            $table->text("notification_description");
            $table->text("notification_link");

            $table->unsignedBigInteger("sender_id");
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger("receiver_id");
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');


            $table->unsignedBigInteger("business_id")->nullable();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');

            $table->boolean("is_system_generated")->nullable();



            $table->unsignedBigInteger("notification_template_id")->nullable();
            $table->foreign('notification_template_id')->references('id')->on('notification_templates')->onDelete('cascade');


            $table->enum("status",['read', 'unread'])->default("unread")->nullable();


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
        Schema::dropIfExists('notifications');
    }
}
