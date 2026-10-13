<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $kasirRole = Role::where('name', 'kasir')->first();
        $pemilikRole = Role::where('name', 'pemilik')->first();

        // 1. Akun Admin
        User::firstOrCreate(['username' => 'admin'], [
            'role_id' => $adminRole->id,
            'name' => 'Admin Sistem',
            'username' => 'admin',
            'email' => 'admin@berkahmulia.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        // 2. Akun Kasir 1
        User::firstOrCreate(['username' => 'kasir1'], [
            'role_id' => $kasirRole->id,
            'name' => 'Kasir Siang',
            'username' => 'kasir1',
            'email' => 'kasir1@berkahmulia.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        // 3. Akun Pemilik
        User::firstOrCreate(['username' => 'pemilik'], [
            'role_id' => $pemilikRole->id,
            'name' => 'Pemilik Restoran',
            'username' => 'pemilik',
            'email' => 'pemilik@berkahmulia.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
    }
}
