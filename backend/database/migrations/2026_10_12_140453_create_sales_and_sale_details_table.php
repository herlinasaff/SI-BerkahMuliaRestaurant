<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Header Transaksi Penjualan
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // INV-YYYYMMDD-XXXX
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // Kasir
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('restrict');
            $table->string('customer_type')->default('umum'); // umum, vip, yarwe
            $table->timestamp('sale_date')->useCurrent();
            
            $table->decimal('original_total', 14, 2)->default(0); // Total kotor
            $table->decimal('total_discount', 14, 2)->default(0); // Potongan (VIP 100%)
            $table->decimal('yarwe_total', 14, 2)->default(0);    // Biaya tambahan Biro
            $table->integer('total_plates')->default(0);          // Jumlah piring Biro
            $table->decimal('final_total', 14, 2)->default(0);    // Total bayar akhir
            
            $table->string('payment_method')->default('cash'); // cash, qris, transfer, debit
            $table->decimal('cash_received', 14, 2)->default(0);
            $table->decimal('cash_change', 14, 2)->default(0);
            $table->string('status')->default('completed'); // completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sale_date');
            $table->index('customer_type');
        });

        // 2. Rincian Item Penjualan
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->string('product_name');
            $table->integer('qty');
            $table->decimal('unit_price', 14, 2);
            $table->decimal('original_price', 14, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('final_price', 14, 2);
            $table->boolean('is_plate_based')->default(false);
            $table->integer('plate_count')->default(0);
            $table->decimal('yarwe_fee_applied', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_details');
        Schema::dropIfExists('sales');
    }
};
