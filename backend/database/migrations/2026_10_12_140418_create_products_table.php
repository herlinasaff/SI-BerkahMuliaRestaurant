<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->string('code')->unique(); // Kode Menu / SKU (misal: PRD-001)
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->default('porsi'); // porsi, piring, pcs, botol, kg
            $table->decimal('price', 14, 2)->default(0); // Harga Jual
            $table->boolean('is_plate_based')->default(false); // Dihitung piring
            $table->boolean('is_prasmanan')->default(false);
            $table->decimal('price_per_plate', 14, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['code', 'name']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
