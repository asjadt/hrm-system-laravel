<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable();
            $table->string("type");
            $table->text("title_template");
            $table->text("template");
            $table->text("link");
            $table->boolean("is_active");
            $table->timestamps();
        });

        DB::table('notification_templates')->insert(
            array(

                [
                    'type' => 'reminder_before_expiry',
                    "title_template"=> ("[title]"),
                    "template"=> ("[entity] expires in [duration] days. Renew now."),
                    "link"=> ("/[entity_name]/[entity_id]"),
                    "is_active" => 1
                ],

                [
                    'type' => 'reminder_after_expiry',
                    "title_template"=> ("[title]"),
                    "template"=> ("[entity] expired [duration] days ago. Please renew it now."),
                    "link"=> ("/[entity_name]/[entity_id]"),
                    "is_active" => 1
                ],
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_templates');
    }
}
