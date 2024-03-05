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
    
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_method_id'); // reference to the payment_methods table
            $table->string('payment_status')->default('pending');
            $table->string('payment_amount');
            $table->string('image')->nullable(); // make this nullable as it may not be applicable for all payment methods
            $table->date('payment_date');
            $table->enum('approval_status', ['approved', 'pending', 'rejected'])->default('pending')->nullable()->comment('Possible values: approved, pending, rejected');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
