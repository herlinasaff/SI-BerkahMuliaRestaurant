<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'qty',
        'unit_price',
        'original_price',
        'discount_percent',
        'discount_amount',
        'subtotal',
        'final_price',
        'is_plate_based',
        'plate_count',
        'yarwe_fee_applied',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'final_price' => 'decimal:2',
        'is_plate_based' => 'boolean',
        'plate_count' => 'integer',
        'yarwe_fee_applied' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
