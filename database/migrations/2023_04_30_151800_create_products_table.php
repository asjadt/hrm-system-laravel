<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string("name");
            $table->string("sku")->nullable();
            $table->enum("type",['single', 'variable']);


            $table->string("description")->nullable();
            $table->string("image")->nullable();
            $table->boolean("is_active")->default(true);

            $table->boolean("is_default");

            $table->unsignedBigInteger("product_category_id")->nullable();
            $table->foreign('product_category_id')->references('id')->on('product_categories')->onDelete('cascade');

           
            $table->softDeletes();
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
        Schema::dropIfExists('products');
    }
}
