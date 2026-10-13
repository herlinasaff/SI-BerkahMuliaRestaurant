<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Header Pembelian
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // PO-YYYYMMDD-XXXX
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamp('purchase_date')->useCurrent();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('shipping_cost', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('payment_method')->default('cash'); // cash, transfer, debit
            $table->string('status')->default('received'); // pending, received, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('purchase_date');
        });

        // 2. Rincian Item Pembelian
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->integer('qty');
            $table->decimal('unit_price', 14, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
        Schema::dropIfExists('purchases');
    }
};
