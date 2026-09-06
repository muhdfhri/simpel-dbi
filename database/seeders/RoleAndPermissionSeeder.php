<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat 3 Roles Utama SIMPEL DBI
        foreach (UserRole::cases() as $roleEnum) {
            Role::firstOrCreate(['name' => $roleEnum->value]);
        }

        // Permissions granular per modul SIMPEL DBI
        $permissions = [
            // Modul Pelaporan
            'laporan.create',
            'laporan.edit',
            'laporan.delete',
            'laporan.view-all',

            // Modul Disposisi & Pembinaan
            'laporan.verify',
            'laporan.followup',
            'kegiatan.create',

            // Dokumen & Master Data
            'sk_desa.upload',
            'sk_desa.download',
            'master-data.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Assign permissions default ke Role
        $roleDesa = Role::findByName(UserRole::DESA->value);
        $roleDesa->syncPermissions([
            'laporan.create',
            'laporan.edit',
            'sk_desa.download',
        ]);

        $rolePimpasa = Role::findByName(UserRole::PIMPASA->value);
        $rolePimpasa->syncPermissions([
            'laporan.create',
            'laporan.edit',
            'laporan.verify',
            'laporan.followup',
            'kegiatan.create',
            'sk_desa.upload',
            'sk_desa.download',
        ]);

        $roleKanwil = Role::findByName(UserRole::KANWIL->value);
        $roleKanwil->syncPermissions([
            'laporan.create',
            'laporan.edit',
            'laporan.delete',
            'laporan.view-all',
            'laporan.verify',
            'laporan.followup',
            'kegiatan.create',
            'sk_desa.upload',
            'sk_desa.download',
            'master-data.manage',
        ]);
    }
}
