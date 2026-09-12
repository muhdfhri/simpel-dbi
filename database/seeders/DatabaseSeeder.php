<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            KategoriLaporanSeeder::class,
            UptSeeder::class,
            UserSeeder::class,
            UserPimpasaSeeder::class,
            DesaBinaanSeeder::class,
            WilayahAdministratifFromMdSeeder::class,
            PerangkatDesaSeeder::class,
            NotificationSeeder::class,
            KegiatanPembinaanSeeder::class,
        ]);
    }
}
