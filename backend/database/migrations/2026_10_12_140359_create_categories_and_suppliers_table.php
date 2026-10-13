<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kategori
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // food_prasmanan, food_paket, beverage, snack, cigarette, medicine, ingredient, equipment_kitchen, equipment_dining
            $table->boolean('is_stockable')->default(true);
            $table->boolean('is_sellable')->default(true);
            $table->boolean('is_plate_based')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Pemasok (Suppliers)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('categories');
    }
};
