<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('total_amount', 10, 2);
            $table->string('tax');
            $table->string('order_number');
            $table->string('order_status');
            $table->string('payment_status');
            $table->string('shipping_charge');
            $table->string('delivery_status')->nullable();
            $table->string('status');
            $table->text('shipping_address');
            $table->string('payment_method');
            $table->string('billing_address');
            $table->string('email');
            $table->text('notes');
            $table->timestamps();
            $table->string('delivery_date')->nullable()->default(Carbon::now()->addDays(10));
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
