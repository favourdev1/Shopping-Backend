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
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('office_address');
            $table->decimal('shipping_cost_per_meter', 8, 2);
            $table->string('account_number_1')->nullable();
            $table->string('account_name_1')->nullable();
            $table->string('account_number_2')->nullable();
            $table->string('account_name_2')->nullable();
            $table->string('bank_name_1')->nullable();
            $table->string('bank_name_2')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
