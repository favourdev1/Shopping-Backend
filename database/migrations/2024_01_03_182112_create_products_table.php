<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->unsignedBigInteger('category_id');
            $table->text('description')->nullable();
            $table->decimal('regular_price', 8, 2);
            $table->string('brand')->nullable();
            $table->string('product_img1')->nullable();
            $table->string('product_img2')->nullable();
            $table->string('product_img3')->nullable();
            $table->string('product_img4')->nullable();
            $table->string('product_img5')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->integer('quantity_in_stock');
            $table->text('tags')->nullable()->nullable();
            $table->string('refundable')->default('false');
            $table->enum('status', ['active', 'inactive']);
            $table->decimal('sales_price', 8, 2);
            $table->string('meta_title');
            $table->text('meta_description');
            $table->string('cash_on_delivery')->default('false');
            $table->string('sku')->nullable();
            $table->string('free_shipping')->default('false');
            $table->decimal('shipping_cost')->nullable();
            $table->decimal('tax')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
