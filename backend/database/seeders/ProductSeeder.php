<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $prasmanan = Category::where('type', 'food_prasmanan')->first();
        $minuman = Category::where('type', 'beverage')->first();
        $paket = Category::where('type', 'food_paket')->first();

        $products = [
            // Menu Prasmanan
            ['category_id' => $prasmanan->id, 'code' => 'PRS-001', 'name' => 'Nasi Putih + Ayam Goreng Lengkuas', 'price' => 22000, 'price_per_plate' => 22000, 'is_plate_based' => true, 'is_prasmanan' => true, 'stock' => 100, 'min_stock' => 20, 'unit' => 'piring'],
            ['category_id' => $prasmanan->id, 'code' => 'PRS-002', 'name' => 'Nasi Putih + Rendang Daging Sapi', 'price' => 27000, 'price_per_plate' => 27000, 'is_plate_based' => true, 'is_prasmanan' => true, 'stock' => 80, 'min_stock' => 15, 'unit' => 'piring'],
            ['category_id' => $prasmanan->id, 'code' => 'PRS-003', 'name' => 'Nasi Putih + Ikan Bakar Nila', 'price' => 24000, 'price_per_plate' => 24000, 'is_plate_based' => true, 'is_prasmanan' => true, 'stock' => 60, 'min_stock' => 10, 'unit' => 'piring'],
            
            // Menu Paket
            ['category_id' => $paket->id, 'code' => 'PKT-001', 'name' => 'Paket Hemat Bebek Goreng Sambal Ijo', 'price' => 30000, 'price_per_plate' => 30000, 'is_plate_based' => true, 'is_prasmanan' => false, 'stock' => 50, 'min_stock' => 10, 'unit' => 'porsi'],
            
            // Minuman
            ['category_id' => $minuman->id, 'code' => 'MNM-001', 'name' => 'Es Teh Manis', 'price' => 5000, 'is_plate_based' => false, 'is_prasmanan' => false, 'stock' => 200, 'min_stock' => 30, 'unit' => 'gelas'],
            ['category_id' => $minuman->id, 'code' => 'MNM-002', 'name' => 'Es Jeruk Peras Segar', 'price' => 8000, 'is_plate_based' => false, 'is_prasmanan' => false, 'stock' => 150, 'min_stock' => 25, 'unit' => 'gelas'],
        ];

        foreach ($products as $prod) {
            Product::firstOrCreate(['code' => $prod['code']], $prod);
        }
    }
}
