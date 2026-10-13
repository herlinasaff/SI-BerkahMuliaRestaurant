<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'PT Sumber Pangan Sejahtera', 'contact_person' => 'Bpk. Ahmad', 'phone' => '081234567890', 'address' => 'Pasar Induk Kramat Jati', 'status' => 'active'],
            ['name' => 'Distributor Minuman Segar', 'contact_person' => 'Ibu Maria', 'phone' => '081298765432', 'address' => 'Kawasan Industri Pulogadung', 'status' => 'active'],
        ];

        foreach ($suppliers as $sup) {
            Supplier::firstOrCreate(['name' => $sup['name']], $sup);
        }
    }
}
