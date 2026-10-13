<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'user_id',
        'purchase_date',
        'subtotal',
        'tax',
        'shipping_cost',
        'total_amount',
        'payment_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }
}
