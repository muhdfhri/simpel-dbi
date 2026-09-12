<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\DesaBinaan;
use App\Models\Upt;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $desa = DesaBinaan::first();
        $upt = Upt::first();

        // 1. Akun Perangkat Desa
        $userDesa = User::create([
            'name' => 'Pak Kades Mangga',
            'email' => 'desa@simpeldbi.go.id',
            'password' => Hash::make('password123'),
            'role' => UserRole::DESA,
            'desa_id' => $desa?->id,
            'upt_id' => $desa?->upt_id ?? $upt?->id,
            'kontak' => '081234567890',
            'is_active' => true,
        ]);
        $userDesa->assignRole(UserRole::DESA->value);

        // 2. Akun PIMPASA / Staf UPT Imigrasi (PIMPASA adalah bagian dari Satker UPT)
        $userPimpasa = User::create([
            'name' => 'Budi PIMPASA, S.H.',
            'email' => 'pimpasa@simpeldbi.go.id',
            'password' => Hash::make('password123'),
            'role' => UserRole::PIMPASA,
            'upt_id' => $upt?->id,
            'desa_id' => $desa?->id,
            'nip' => '198501012010011001',
            'golongan' => 'Penata (III/c)',
            'kontak' => '081298765432',
            'is_active' => true,
        ]);
        $userPimpasa->assignRole(UserRole::PIMPASA->value);

        // Connect PIMPASA ke Desa Binaan
        if ($desa) {
            $desa->update(['pimpasa_id' => $userPimpasa->id]);
        }

        // 3. Akun Admin / Pimpinan Kanwil Sumut
        $userKanwil = User::create([
            'name' => 'Kadiv Keimigrasian Kanwil Sumut',
            'email' => 'kanwil@simpeldbi.go.id',
            'password' => Hash::make('password123'),
            'role' => UserRole::KANWIL,
            'nip' => '197505051998031002',
            'golongan' => 'Pembina Utama Muda (IV/c)',
            'kontak' => '081100998877',
            'is_active' => true,
        ]);
        $userKanwil->assignRole(UserRole::KANWIL->value);
    }
}
