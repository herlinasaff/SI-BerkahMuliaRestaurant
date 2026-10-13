<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_stockable',
        'is_sellable',
        'is_plate_based',
        'description',
    ];

    protected $casts = [
        'is_stockable' => 'boolean',
        'is_sellable' => 'boolean',
        'is_plate_based' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
