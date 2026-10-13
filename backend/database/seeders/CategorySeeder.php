<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan Prasmanan', 'type' => 'food_prasmanan', 'is_stockable' => true, 'is_sellable' => true, 'is_plate_based' => true, 'description' => 'Menu prasmanan yang diambil pelanggan'],
            ['name' => 'Makanan Paket', 'type' => 'food_paket', 'is_stockable' => true, 'is_sellable' => true, 'is_plate_based' => true, 'description' => 'Menu paket porsi tetap'],
            ['name' => 'Minuman', 'type' => 'beverage', 'is_stockable' => true, 'is_sellable' => true, 'is_plate_based' => false, 'description' => 'Aneka minuman segar'],
            ['name' => 'Cemilan / Snack', 'type' => 'snack', 'is_stockable' => true, 'is_sellable' => true, 'is_plate_based' => false, 'description' => 'Kerupuk, keripik, cemilan'],
            ['name' => 'Rokok', 'type' => 'cigarette', 'is_stockable' => true, 'is_sellable' => true, 'is_plate_based' => false, 'description' => 'Produk rokok'],
            ['name' => 'Obat', 'type' => 'medicine', 'is_stockable' => true, 'is_sellable' => true, 'is_plate_based' => false, 'description' => 'Obat-obatan ringan'],
            ['name' => 'Bahan Olah Dapur', 'type' => 'ingredient', 'is_stockable' => true, 'is_sellable' => false, 'is_plate_based' => false, 'description' => 'Bahan mentah dapur (beras, minyak, dll)'],
            ['name' => 'Alat Olah', 'type' => 'equipment_kitchen', 'is_stockable' => true, 'is_sellable' => false, 'is_plate_based' => false, 'description' => 'Peralatan masak dapur'],
            ['name' => 'Alat Saji', 'type' => 'equipment_dining', 'is_stockable' => true, 'is_sellable' => false, 'is_plate_based' => false, 'description' => 'Piring, sendok, mangkok'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
