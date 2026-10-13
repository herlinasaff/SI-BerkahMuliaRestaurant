<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'status'];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // Mendapatkan shift yang sedang aktif otomatis sesuai jam sekarang
    public static function getCurrentShift(): ?self
    {
        $currentTime = now()->format('H:i:s');

        // Shift Siang (08:00 - 20:00)
        $siang = self::where('name', 'Shift Siang')
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->first();

        if ($siang) return $siang;

        // Shift Malam (20:00 - 08:00)
        $malam = self::where('name', 'Shift Malam')
            ->where(function ($q) use ($currentTime) {
                $q->where('start_time', '<=', $currentTime)
                  ->orWhere('end_time', '>=', $currentTime);
            })->first();

        return $malam ?? self::first();
    }
}
