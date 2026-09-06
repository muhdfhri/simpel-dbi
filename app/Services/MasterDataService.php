<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\DesaBinaan;
use App\Models\Upt;
use App\Models\User;
use App\Models\WilayahAdministratif;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class MasterDataService
{
    /**
     * Ambil / buat Wilayah Default untuk Foreign Key
     */
    protected function getDefaultWilayahId(): int
    {
        $wilayah = WilayahAdministratif::firstOrCreate(
            ['kode_kemendagri' => '12.01.01.2001'],
            ['nama' => 'Pantai Labu Pekan', 'level' => 'desa']
        );
        return $wilayah->id;
    }

    // --- LOGIC CRUD DESA BINAAN ---
    public function createDesaBinaan(array $data): DesaBinaan
    {
        $pimpasaId = !empty($data['pimpasa_id']) ? (int) $data['pimpasa_id'] : null;

        return DesaBinaan::create([
            'nama' => trim($data['nama']),
            'upt_id' => (int) $data['upt_id'],
            'pimpasa_id' => $pimpasaId,
            'wilayah_id' => $this->getDefaultWilayahId(),
            'lat' => $data['lat'] ?? 3.5952,
            'lng' => $data['lng'] ?? 98.6722,
            'status_terkini' => $data['status_terkini'] ?? 'aman',
        ]);
    }

    public function updateDesaBinaan(DesaBinaan $desa, array $data): bool
    {
        $pimpasaId = !empty($data['pimpasa_id']) ? (int) $data['pimpasa_id'] : null;

        return $desa->update([
            'nama' => trim($data['nama']),
            'upt_id' => (int) $data['upt_id'],
            'pimpasa_id' => $pimpasaId,
            'lat' => array_key_exists('lat', $data) && $data['lat'] !== null ? $data['lat'] : $desa->lat,
            'lng' => array_key_exists('lng', $data) && $data['lng'] !== null ? $data['lng'] : $desa->lng,
            'status_terkini' => $data['status_terkini'] ?? $desa->status_terkini,
        ]);
    }

    public function deleteDesaBinaan(DesaBinaan $desa): bool
    {
        return $desa->delete();
    }

    // --- LOGIC CRUD PETUGAS PIMPASA ---
    public function createPimpasaUser(array $data): User
    {
        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'nip' => trim($data['nip']),
            'golongan' => trim($data['golongan']),
            'upt_id' => (int) $data['upt_id'],
            'password' => Hash::make($data['password']),
            'role' => 'pimpasa',
            'kontak' => '0812' . rand(10000000, 99999999),
            'is_active' => true,
        ]);

        // Penugasan Spatie Permission Role
        $user->assignRole(UserRole::PIMPASA);

        return $user;
    }

    public function updatePimpasaUser(User $user, array $data): bool
    {
        $updateData = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'nip' => trim($data['nip']),
            'golongan' => trim($data['golongan']),
            'upt_id' => (int) $data['upt_id'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        return $user->update($updateData);
    }

    public function deletePimpasaUser(User $user): bool
    {
        return $user->delete();
    }

    // --- LOGIC CRUD USER KANWIL (SUPER-ADMIN) ---
    public function createKanwilUser(array $data): User
    {
        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'nip' => !empty($data['nip']) ? trim($data['nip']) : null,
            'golongan' => !empty($data['golongan']) ? trim($data['golongan']) : null,
            'kontak' => !empty($data['kontak']) ? trim($data['kontak']) : ('0812' . rand(10000000, 99999999)),
            'password' => Hash::make($data['password']),
            'role' => 'kanwil',
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
        ]);

        // Penugasan Spatie Permission Role
        $user->assignRole(UserRole::KANWIL);

        return $user;
    }

    public function updateKanwilUser(User $user, array $data): bool
    {
        $updateData = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'nip' => !empty($data['nip']) ? trim($data['nip']) : null,
            'golongan' => !empty($data['golongan']) ? trim($data['golongan']) : null,
            'kontak' => !empty($data['kontak']) ? trim($data['kontak']) : $user->kontak,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $user->is_active,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        return $user->update($updateData);
    }

    public function deleteKanwilUser(User $user): bool
    {
        return $user->delete();
    }

    // --- LOGIC CRUD SATKER UPT ---
    public function createUpt(array $data): Upt
    {
        return Upt::create([
            'nama' => trim($data['nama']),
            'tipe' => trim($data['tipe']),
            'wilayah_id' => $this->getDefaultWilayahId(),
        ]);
    }

    public function updateUpt(Upt $upt, array $data): bool
    {
        return $upt->update([
            'nama' => trim($data['nama']),
            'tipe' => trim($data['tipe']),
        ]);
    }

    public function deleteUpt(Upt $upt): bool
    {
        return $upt->delete();
    }

    // --- LOGIC CRUD USER PERANGKAT DESA ---
    public function createDesaUser(array $data): User
    {
        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'desa_id' => (int) $data['desa_id'],
            'kontak' => !empty($data['kontak']) ? trim($data['kontak']) : ('0812' . rand(10000000, 99999999)),
            'password' => Hash::make($data['password']),
            'role' => 'desa',
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
        ]);

        // Penugasan Spatie Permission Role
        $user->assignRole(UserRole::DESA);

        return $user;
    }

    public function updateDesaUser(User $user, array $data): bool
    {
        $updateData = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'desa_id' => (int) $data['desa_id'],
            'kontak' => !empty($data['kontak']) ? trim($data['kontak']) : $user->kontak,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $user->is_active,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        return $user->update($updateData);
    }

    public function deleteDesaUser(User $user): bool
    {
        return $user->delete();
    }

    // --- LOGIC MATRIKS HAK AKSES (ROLE PERMISSION CONTROL) ---
    public function getPermissionsMatrix(): array
    {
        $roleDesa = SpatieRole::findByName(UserRole::DESA->value);
        $rolePimpasa = SpatieRole::findByName(UserRole::PIMPASA->value);

        $desaPermissionsConfig = [
            ['key' => 'laporan.create', 'label' => 'Form Input Laporan Baru', 'desc' => 'Dapat menginputkan entri laporan Pekerja Migran ke dalam sistem', 'category' => 'Modul Pelaporan'],
            ['key' => 'laporan.edit', 'label' => 'Edit Data Laporan Draft', 'desc' => 'Dapat mengubah rincian laporan sebelum diverifikasi oleh Petugas PIMPASA', 'category' => 'Modul Pelaporan'],
            ['key' => 'sk_desa.download', 'label' => 'Unduh Berkas SK Desa Binaan', 'desc' => 'Dapat mengunduh PDF/arsip dokumen fisik SK Keputusan Desa Binaan', 'category' => 'Dokumen & Administrasi'],
        ];

        $pimpasaPermissionsConfig = [
            ['key' => 'laporan.verify', 'label' => 'Verifikasi Worklist & Disposisi SLA', 'desc' => 'Dapat memverifikasi laporan masuk dan memberikan instruksi disposisi SLA', 'category' => 'Modul Verifikasi & SLA'],
            ['key' => 'laporan.followup', 'label' => 'Input Tindak Lanjut Lapangan', 'desc' => 'Dapat mencatat hasil penanganan dan status tindak lanjut di lapangan', 'category' => 'Modul Verifikasi & SLA'],
            ['key' => 'kegiatan.create', 'label' => 'Input Agenda Pembinaan Desa', 'desc' => 'Dapat mencatat agenda sosialisasi dan kegiatan pembinaan Desa Binaan', 'category' => 'Pembinaan Lapangan'],
            ['key' => 'sk_desa.upload', 'label' => 'Upload Berkas SK Desa Binaan', 'desc' => 'Dapat mengunggah dokumen fisik SK Keputusan penetapan Desa Binaan', 'category' => 'Dokumen & Administrasi'],
            ['key' => 'sk_desa.download', 'label' => 'Unduh Berkas SK Desa Binaan', 'desc' => 'Dapat mengunduh PDF/arsip dokumen fisik SK Keputusan Desa Binaan', 'category' => 'Dokumen & Administrasi'],
        ];

        $mapItems = function ($items, $roleModel) {
            return array_map(function ($item) use ($roleModel) {
                return [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'desc' => $item['desc'],
                    'category' => $item['category'],
                    'enabled' => $roleModel->hasPermissionTo($item['key']),
                ];
            }, $items);
        };

        return [
            'desa' => [
                'role' => 'desa',
                'role_label' => 'Perangkat Desa Binaan',
                'role_desc' => 'Pengaturan sakelar hak akses fitur & CRUD khusus untuk akun pengelola di tingkat Desa Binaan.',
                'items' => $mapItems($desaPermissionsConfig, $roleDesa),
            ],
            'pimpasa' => [
                'role' => 'pimpasa',
                'role_label' => 'Petugas PIMPASA / UPT Imigrasi',
                'role_desc' => 'Pengaturan sakelar hak akses fitur & CRUD khusus untuk personel pengampu PIMPASA & Satker UPT.',
                'items' => $mapItems($pimpasaPermissionsConfig, $rolePimpasa),
            ],
        ];
    }

    public function toggleRolePermission(string $roleName, string $permissionName, bool $enable): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = SpatieRole::findByName($roleName);

        if ($enable) {
            $role->givePermissionTo($permissionName);
        } else {
            $role->revokePermissionTo($permissionName);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    // --- LOGIC CRUD KATEGORI LAPORAN ---
    public function createKategoriLaporan(array $data): \App\Models\KategoriLaporan
    {
        return \App\Models\KategoriLaporan::create([
            'kode' => trim($data['kode']),
            'nama_kategori' => trim($data['nama_kategori']),
            'deskripsi' => $data['deskripsi'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function updateKategoriLaporan(\App\Models\KategoriLaporan $kategori, array $data): bool
    {
        return $kategori->update([
            'kode' => trim($data['kode']),
            'nama_kategori' => trim($data['nama_kategori']),
            'deskripsi' => $data['deskripsi'] ?? null,
            'is_active' => $data['is_active'] ?? $kategori->is_active,
        ]);
    }

    public function toggleKategoriLaporanStatus(\App\Models\KategoriLaporan $kategori): bool
    {
        return $kategori->update([
            'is_active' => !$kategori->is_active,
        ]);
    }

    public function deleteKategoriLaporan(\App\Models\KategoriLaporan $kategori): bool
    {
        return $kategori->delete();
    }
}
