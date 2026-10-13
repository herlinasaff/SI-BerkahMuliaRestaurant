<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'shift_id',
        'customer_type',
        'sale_date',
        'original_total',
        'total_discount',
        'yarwe_total',
        'total_plates',
        'final_total',
        'payment_method',
        'cash_received',
        'cash_change',
        'status',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'original_total' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'yarwe_total' => 'decimal:2',
        'final_total' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'cash_change' => 'decimal:2',
        'total_plates' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }
}
