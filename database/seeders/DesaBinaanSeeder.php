<?php

namespace Database\Seeders;

use App\Models\DesaBinaan;
use App\Models\Upt;
use App\Models\User;
use App\Models\WilayahAdministratif;
use Illuminate\Database\Seeder;

class DesaBinaanSeeder extends Seeder
{
    public function run(): void
    {
        // Wilayah Default
        $wilayahDefault = WilayahAdministratif::firstOrCreate(
            ['kode_kemendagri' => '12.01.01.2001'],
            ['nama' => 'Pantai Labu Pekan', 'level' => 'desa']
        );

        // 1. Matriks 10 UPT Imigrasi (Wilayah ID 1-10)
        $uptData = [
            1 => ['nama' => 'Kantor Imigrasi Kelas I Khusus TPI Medan', 'tipe' => 'Kelas I Khusus TPI'],
            2 => ['nama' => 'Kantor Imigrasi Kelas I TPI Polonia', 'tipe' => 'Kelas I TPI'],
            3 => ['nama' => 'Kantor Imigrasi Kelas II TPI Belawan', 'tipe' => 'Kelas II TPI'],
            4 => ['nama' => 'Kantor Imigrasi Kelas II TPI Pematang Siantar', 'tipe' => 'Kelas II TPI'],
            5 => ['nama' => 'Kantor Imigrasi Kelas II TPI Tanjung Balai Asahan', 'tipe' => 'Kelas II TPI'],
            6 => ['nama' => 'Kantor Imigrasi Kelas II TPI Sibolga', 'tipe' => 'Kelas II TPI'],
            7 => ['nama' => 'Kantor Imigrasi Kelas III Non TPI Mandailing Natal', 'tipe' => 'Kelas III Non TPI'],
            8 => ['nama' => 'Kantor Imigrasi Kelas III TPI Nias', 'tipe' => 'Kelas III TPI'],
            9 => ['nama' => 'Kantor Imigrasi Kelas III Non TPI Tapanuli Utara', 'tipe' => 'Kelas III Non TPI'],
            10 => ['nama' => 'Kantor Imigrasi Kelas III Non TPI Padangsidimpuan', 'tipe' => 'Kelas III Non TPI'],
        ];

        $uptMap = [];
        foreach ($uptData as $id => $u) {
            $uptMap[$id] = Upt::firstOrCreate(
                ['id' => $id],
                [
                    'nama' => $u['nama'],
                    'tipe' => $u['tipe'],
                    'wilayah_id' => $wilayahDefault->id,
                ]
            );
        }

        // 2. Data Resmi Lengkap 171 Desa Binaan Imigrasi Se-Sumut (Wilayah 1 s/d 10)
        $allDesaList = [
            // WILAYAH 1 (Kanim Medan - 9 Desa)
            ['upt_id' => 1, 'nama' => 'Desa Pantai Labu Pekan', 'lat' => 3.6301, 'lng' => 98.8802],
            ['upt_id' => 1, 'nama' => 'Desa Paya Geli', 'lat' => 3.5901, 'lng' => 98.6102],
            ['upt_id' => 1, 'nama' => 'Desa Paluh Sibaji', 'lat' => 3.6401, 'lng' => 98.8902],
            ['upt_id' => 1, 'nama' => 'Desa Tanjung Anom', 'lat' => 3.5401, 'lng' => 98.7102],
            ['upt_id' => 1, 'nama' => 'Desa Puji Mulyo', 'lat' => 3.5801, 'lng' => 98.6002],
            ['upt_id' => 1, 'nama' => 'Desa Sunggal', 'lat' => 3.5701, 'lng' => 98.6102],
            ['upt_id' => 1, 'nama' => 'Desa Medan Krio', 'lat' => 3.5601, 'lng' => 98.6002],
            ['upt_id' => 1, 'nama' => 'Desa Sei Mencirim', 'lat' => 3.5501, 'lng' => 98.5902],
            ['upt_id' => 1, 'nama' => 'Kelurahan Sei Sikambing C II', 'lat' => 3.6001, 'lng' => 98.6502],

            // WILAYAH 2 (Kanim Polonia - 24 Desa)
            ['upt_id' => 2, 'nama' => 'Desa Dalu X-A', 'lat' => 3.5301, 'lng' => 98.7202],
            ['upt_id' => 2, 'nama' => 'Desa Dalu X-B', 'lat' => 3.5302, 'lng' => 98.7203],
            ['upt_id' => 2, 'nama' => 'Desa Limau Manis', 'lat' => 3.5201, 'lng' => 98.7102],
            ['upt_id' => 2, 'nama' => 'Kelurahan Anggrung', 'lat' => 3.5701, 'lng' => 98.6702],
            ['upt_id' => 2, 'nama' => 'Kelurahan Madras Hulu', 'lat' => 3.5751, 'lng' => 98.6712],
            ['upt_id' => 2, 'nama' => 'Kelurahan Sari Rejo', 'lat' => 3.5601, 'lng' => 98.6752],
            ['upt_id' => 2, 'nama' => 'Kelurahan Polonia', 'lat' => 3.5651, 'lng' => 98.6702],
            ['upt_id' => 2, 'nama' => 'Kelurahan Suka Damai', 'lat' => 3.5551, 'lng' => 98.6722],
            ['upt_id' => 2, 'nama' => 'Kelurahan Pangkalan Mansyur', 'lat' => 3.5451, 'lng' => 98.6712],
            ['upt_id' => 2, 'nama' => 'Kelurahan Gedung Johor', 'lat' => 3.5351, 'lng' => 98.6812],
            ['upt_id' => 2, 'nama' => 'Kelurahan Titi Kuning', 'lat' => 3.5401, 'lng' => 98.6902],
            ['upt_id' => 2, 'nama' => 'Kelurahan Kwala Bekala', 'lat' => 3.5201, 'lng' => 98.6602],
            ['upt_id' => 2, 'nama' => 'Kelurahan Padang Bulan Selayang I', 'lat' => 3.5501, 'lng' => 98.6502],
            ['upt_id' => 2, 'nama' => 'Kelurahan Padang Bulan Selayang II', 'lat' => 3.5551, 'lng' => 98.6552],
            ['upt_id' => 2, 'nama' => 'Kelurahan Tanjung Sari', 'lat' => 3.5451, 'lng' => 98.6452],
            ['upt_id' => 2, 'nama' => 'Kelurahan Sempakata', 'lat' => 3.5351, 'lng' => 98.6352],
            ['upt_id' => 2, 'nama' => 'Kelurahan Asam Kumbang', 'lat' => 3.5651, 'lng' => 98.6252],
            ['upt_id' => 2, 'nama' => 'Kelurahan Aur', 'lat' => 3.5801, 'lng' => 98.6802],
            ['upt_id' => 2, 'nama' => 'Kelurahan Sukaraja', 'lat' => 3.5751, 'lng' => 98.6852],
            ['upt_id' => 2, 'nama' => 'Kelurahan Hamdan', 'lat' => 3.5851, 'lng' => 98.6752],
            ['upt_id' => 2, 'nama' => 'Kelurahan Padang Bulan', 'lat' => 3.5601, 'lng' => 98.6552],
            ['upt_id' => 2, 'nama' => 'Kelurahan Darat', 'lat' => 3.5701, 'lng' => 98.6652],
            ['upt_id' => 2, 'nama' => 'Kelurahan Merdeka', 'lat' => 3.5751, 'lng' => 98.6602],
            ['upt_id' => 2, 'nama' => 'Kelurahan Mangga', 'lat' => 3.5101, 'lng' => 98.6202],

            // WILAYAH 3 (Kanim Belawan - 20 Desa)
            ['upt_id' => 3, 'nama' => 'Desa Sena', 'lat' => 3.6201, 'lng' => 98.7902],
            ['upt_id' => 3, 'nama' => 'Desa Tanjung Sari (Batang Kuis)', 'lat' => 3.6251, 'lng' => 98.7952],
            ['upt_id' => 3, 'nama' => 'Kelurahan Labuhan Deli', 'lat' => 3.6901, 'lng' => 98.6602],
            ['upt_id' => 3, 'nama' => 'Kelurahan Paya Pasir', 'lat' => 3.7001, 'lng' => 98.6652],
            ['upt_id' => 3, 'nama' => 'Kelurahan Rengas Pulau', 'lat' => 3.7101, 'lng' => 98.6702],
            ['upt_id' => 3, 'nama' => 'Kelurahan Tanah Enam Ratus', 'lat' => 3.6801, 'lng' => 98.6502],
            ['upt_id' => 3, 'nama' => 'Kelurahan Terjun', 'lat' => 3.7201, 'lng' => 98.6552],
            ['upt_id' => 3, 'nama' => 'Desa Helvetia (Labuhan Deli)', 'lat' => 3.6601, 'lng' => 98.6402],
            ['upt_id' => 3, 'nama' => 'Desa Manunggal', 'lat' => 3.6501, 'lng' => 98.6502],
            ['upt_id' => 3, 'nama' => 'Desa Laut Dendang', 'lat' => 3.6301, 'lng' => 98.7202],
            ['upt_id' => 3, 'nama' => 'Desa Sampali', 'lat' => 3.6401, 'lng' => 98.7102],
            ['upt_id' => 3, 'nama' => 'Kelurahan Belawan I', 'lat' => 3.7901, 'lng' => 98.6802],
            ['upt_id' => 3, 'nama' => 'Kelurahan Belawan II', 'lat' => 3.7851, 'lng' => 98.6852],
            ['upt_id' => 3, 'nama' => 'Kelurahan Besar', 'lat' => 3.7401, 'lng' => 98.6702],
            ['upt_id' => 3, 'nama' => 'Kelurahan Tangkahan', 'lat' => 3.7301, 'lng' => 98.6802],
            ['upt_id' => 3, 'nama' => 'Kelurahan Nelayan Indah', 'lat' => 3.7601, 'lng' => 98.6752],
            ['upt_id' => 3, 'nama' => 'Kelurahan Pekan Labuhan', 'lat' => 3.7501, 'lng' => 98.6702],
            ['upt_id' => 3, 'nama' => 'Kelurahan Sei Mati', 'lat' => 3.7351, 'lng' => 98.6752],
            ['upt_id' => 3, 'nama' => 'Kelurahan Tanjung Mulia', 'lat' => 3.6401, 'lng' => 98.6702],
            ['upt_id' => 3, 'nama' => 'Kelurahan Titi Papan', 'lat' => 3.6701, 'lng' => 98.6752],

            // WILAYAH 4 (Kanim Pematang Siantar - 34 Desa)
            ['upt_id' => 4, 'nama' => 'Kelurahan Satria', 'lat' => 3.3201, 'lng' => 99.1602],
            ['upt_id' => 4, 'nama' => 'Kelurahan Tambangan Hulu', 'lat' => 3.3251, 'lng' => 99.1652],
            ['upt_id' => 4, 'nama' => 'Kelurahan Damar Sari', 'lat' => 3.3151, 'lng' => 99.1552],
            ['upt_id' => 4, 'nama' => 'Kelurahan Deblod Sundoro', 'lat' => 3.3301, 'lng' => 99.1702],
            ['upt_id' => 4, 'nama' => 'Kelurahan Lubuk Baru', 'lat' => 3.3101, 'lng' => 99.1502],
            ['upt_id' => 4, 'nama' => 'Kelurahan Lubuk Raya', 'lat' => 3.3051, 'lng' => 99.1452],
            ['upt_id' => 4, 'nama' => 'Kelurahan Berohol', 'lat' => 3.3401, 'lng' => 99.1802],
            ['upt_id' => 4, 'nama' => 'Kelurahan Mekar Sentosa', 'lat' => 3.3501, 'lng' => 99.1902],
            ['upt_id' => 4, 'nama' => 'Kelurahan Lalang', 'lat' => 3.3551, 'lng' => 99.1952],
            ['upt_id' => 4, 'nama' => 'Kelurahan Rantau Laban', 'lat' => 3.3601, 'lng' => 99.2002],
            ['upt_id' => 4, 'nama' => 'Wilayah Sei Bamban', 'lat' => 3.4201, 'lng' => 99.0802],
            ['upt_id' => 4, 'nama' => 'Desa Penggalangan', 'lat' => 3.3801, 'lng' => 99.1202],
            ['upt_id' => 4, 'nama' => 'Desa Pon', 'lat' => 3.4301, 'lng' => 99.0902],
            ['upt_id' => 4, 'nama' => 'Desa Sei Bamban Estate', 'lat' => 3.4251, 'lng' => 99.0852],
            ['upt_id' => 4, 'nama' => 'Desa Suka Damai (Sergai)', 'lat' => 3.4151, 'lng' => 99.0752],
            ['upt_id' => 4, 'nama' => 'Desa Bagan Kuala', 'lat' => 3.5101, 'lng' => 99.1402],
            ['upt_id' => 4, 'nama' => 'Desa Mangga Dua (Sergai)', 'lat' => 3.5001, 'lng' => 99.1302],
            ['upt_id' => 4, 'nama' => 'Desa Tebing Tinggi', 'lat' => 3.3351, 'lng' => 99.1652],
            ['upt_id' => 4, 'nama' => 'Pekan Tanjung Beringin', 'lat' => 3.4901, 'lng' => 99.1202],
            ['upt_id' => 4, 'nama' => 'Desa Nagur', 'lat' => 3.4801, 'lng' => 99.1102],
            ['upt_id' => 4, 'nama' => 'Kawasan Tuk-tuk Siadong', 'lat' => 2.6701, 'lng' => 98.8402],
            ['upt_id' => 4, 'nama' => 'Desa Tomok', 'lat' => 2.6601, 'lng' => 98.8502],
            ['upt_id' => 4, 'nama' => 'Desa Ambarita', 'lat' => 2.6801, 'lng' => 98.8302],
            ['upt_id' => 4, 'nama' => 'Desa Situngkir', 'lat' => 2.6201, 'lng' => 98.7102],
            ['upt_id' => 4, 'nama' => 'Nagori Perdagangan I', 'lat' => 3.1601, 'lng' => 99.3202],
            ['upt_id' => 4, 'nama' => 'Nagori Perdagangan II', 'lat' => 3.1651, 'lng' => 99.3252],
            ['upt_id' => 4, 'nama' => 'Nagori Bandar', 'lat' => 3.1501, 'lng' => 99.3102],
            ['upt_id' => 4, 'nama' => 'Kelurahan Serbelawan', 'lat' => 3.1101, 'lng' => 99.1302],
            ['upt_id' => 4, 'nama' => 'Kelurahan Amansari', 'lat' => 3.1151, 'lng' => 99.1352],
            ['upt_id' => 4, 'nama' => 'Nagori Bandar Selamat', 'lat' => 3.1051, 'lng' => 99.1252],
            ['upt_id' => 4, 'nama' => 'Nagori Dolok Maraja', 'lat' => 3.0501, 'lng' => 99.0702],
            ['upt_id' => 4, 'nama' => 'Nagori Purba Sari', 'lat' => 3.0401, 'lng' => 99.0602],
            ['upt_id' => 4, 'nama' => 'Kelurahan Sinaksak', 'lat' => 3.0101, 'lng' => 99.0502],
            ['upt_id' => 4, 'nama' => 'Nagori Dolok Kaehan', 'lat' => 3.0301, 'lng' => 99.0552],

            // WILAYAH 5 (Kanim Tanjung Balai Asahan - 57 Desa)
            ['upt_id' => 5, 'nama' => 'Desa Sei Lunang', 'lat' => 2.9801, 'lng' => 99.8502],
            ['upt_id' => 5, 'nama' => 'Desa Sei Pasir', 'lat' => 2.9851, 'lng' => 99.8552],
            ['upt_id' => 5, 'nama' => 'Desa Sei Tempurung', 'lat' => 2.9751, 'lng' => 99.8452],
            ['upt_id' => 5, 'nama' => 'Desa Sarang Helang', 'lat' => 2.9901, 'lng' => 99.8602],
            ['upt_id' => 5, 'nama' => 'Desa Sei Sembilang', 'lat' => 2.9651, 'lng' => 99.8352],
            ['upt_id' => 5, 'nama' => 'Desa Simpang Empat', 'lat' => 2.9201, 'lng' => 99.6402],
            ['upt_id' => 5, 'nama' => 'Desa Sipaku Area', 'lat' => 2.9251, 'lng' => 99.6452],
            ['upt_id' => 5, 'nama' => 'Desa Sei Dua Hulu', 'lat' => 2.9151, 'lng' => 99.6352],
            ['upt_id' => 5, 'nama' => 'Desa Sei Lama', 'lat' => 2.9301, 'lng' => 99.6502],
            ['upt_id' => 5, 'nama' => 'Desa Anjung Ganjang', 'lat' => 2.9101, 'lng' => 99.6302],
            ['upt_id' => 5, 'nama' => 'Desa Silomlom', 'lat' => 2.9051, 'lng' => 99.6252],
            ['upt_id' => 5, 'nama' => 'Desa Perkebunan Hessa', 'lat' => 2.9351, 'lng' => 99.6552],
            ['upt_id' => 5, 'nama' => 'Desa Sukaraja (Asahan)', 'lat' => 2.9401, 'lng' => 99.6602],
            ['upt_id' => 5, 'nama' => 'Desa Sei Nangka', 'lat' => 2.9551, 'lng' => 99.8152],
            ['upt_id' => 5, 'nama' => 'Desa Jawi-Jawi', 'lat' => 2.9501, 'lng' => 99.8102],
            ['upt_id' => 5, 'nama' => 'Desa Serindan', 'lat' => 2.9601, 'lng' => 99.8202],
            ['upt_id' => 5, 'nama' => 'Desa Sei Tualang Pandau', 'lat' => 2.9451, 'lng' => 99.8052],
            ['upt_id' => 5, 'nama' => 'Desa Sei Lendir', 'lat' => 2.9651, 'lng' => 99.8252],
            ['upt_id' => 5, 'nama' => 'Desa Sei Kepayang Kiri', 'lat' => 2.9701, 'lng' => 99.8302],
            ['upt_id' => 5, 'nama' => 'Kelurahan Binjai Serbangan', 'lat' => 3.0201, 'lng' => 99.6802],
            ['upt_id' => 5, 'nama' => 'Desa Subur', 'lat' => 3.0251, 'lng' => 99.6852],
            ['upt_id' => 5, 'nama' => 'Desa Punggulan', 'lat' => 3.0151, 'lng' => 99.6752],
            ['upt_id' => 5, 'nama' => 'Desa Pasar Lembu', 'lat' => 3.0301, 'lng' => 99.6902],
            ['upt_id' => 5, 'nama' => 'Desa Banjar', 'lat' => 3.0101, 'lng' => 99.6702],
            ['upt_id' => 5, 'nama' => 'Desa Air Joman', 'lat' => 3.0351, 'lng' => 99.6952],
            ['upt_id' => 5, 'nama' => 'Desa Air Joman Baru', 'lat' => 3.0401, 'lng' => 99.7002],
            ['upt_id' => 5, 'nama' => 'Desa Sei Kepayang Tengah', 'lat' => 3.0451, 'lng' => 99.7052],
            ['upt_id' => 5, 'nama' => 'Desa Sei Kepayang Kanan', 'lat' => 3.0501, 'lng' => 99.7102],
            ['upt_id' => 5, 'nama' => 'Desa Sei Paham', 'lat' => 3.0051, 'lng' => 99.6652],
            ['upt_id' => 5, 'nama' => 'Desa Pertahanan', 'lat' => 3.0001, 'lng' => 99.6602],
            ['upt_id' => 5, 'nama' => 'Desa Perbaungan (Asahan)', 'lat' => 3.0551, 'lng' => 99.7152],
            ['upt_id' => 5, 'nama' => 'Desa Bangun Baru', 'lat' => 3.0601, 'lng' => 99.7202],
            ['upt_id' => 5, 'nama' => 'Desa Silo Laut', 'lat' => 3.1201, 'lng' => 99.6702],
            ['upt_id' => 5, 'nama' => 'Desa Silo Buntu', 'lat' => 3.1251, 'lng' => 99.6752],
            ['upt_id' => 5, 'nama' => 'Desa Silo Lama', 'lat' => 3.1151, 'lng' => 99.6652],
            ['upt_id' => 5, 'nama' => 'Desa Bangun Sari (Silau Laut)', 'lat' => 3.1301, 'lng' => 99.6802],
            ['upt_id' => 5, 'nama' => 'Desa Lubuk Palas', 'lat' => 3.1101, 'lng' => 99.6602],
            ['upt_id' => 5, 'nama' => 'Desa Bagan Dalam', 'lat' => 3.2201, 'lng' => 99.5802],
            ['upt_id' => 5, 'nama' => 'Desa Bandar Rahmat', 'lat' => 3.2251, 'lng' => 99.5852],
            ['upt_id' => 5, 'nama' => 'Desa Guntung', 'lat' => 3.2151, 'lng' => 99.5752],
            ['upt_id' => 5, 'nama' => 'Desa Bogak', 'lat' => 3.2301, 'lng' => 99.5902],
            ['upt_id' => 5, 'nama' => 'Desa Suka Maju (Batu Bara)', 'lat' => 3.2101, 'lng' => 99.5702],
            ['upt_id' => 5, 'nama' => 'Desa Kuala Beringin', 'lat' => 2.5801, 'lng' => 99.6402],
            ['upt_id' => 5, 'nama' => 'Desa Parpaundangan', 'lat' => 2.5851, 'lng' => 99.6452],
            ['upt_id' => 5, 'nama' => 'Desa Perkebunan Hanna', 'lat' => 2.5751, 'lng' => 99.6352],
            ['upt_id' => 5, 'nama' => 'Desa Labuhan Haji', 'lat' => 2.5901, 'lng' => 99.6502],
            ['upt_id' => 5, 'nama' => 'Desa Londut', 'lat' => 2.5701, 'lng' => 99.6302],
            ['upt_id' => 5, 'nama' => 'Desa Sakat', 'lat' => 2.4801, 'lng' => 99.9802],
            ['upt_id' => 5, 'nama' => 'Desa Sei Baru (Panai Hilir)', 'lat' => 2.5401, 'lng' => 100.0802],
            ['upt_id' => 5, 'nama' => 'Desa Sei Lumut', 'lat' => 2.5451, 'lng' => 100.0852],
            ['upt_id' => 5, 'nama' => 'Desa Sanggul', 'lat' => 2.5351, 'lng' => 100.0752],
            ['upt_id' => 5, 'nama' => 'Desa Wonosari', 'lat' => 2.5501, 'lng' => 100.0902],
            ['upt_id' => 5, 'nama' => 'Desa Aek Batu', 'lat' => 1.8801, 'lng' => 100.1202],
            ['upt_id' => 5, 'nama' => 'Desa Aek Raso', 'lat' => 1.8851, 'lng' => 100.1252],
            ['upt_id' => 5, 'nama' => 'Desa Asam Jawa', 'lat' => 1.8751, 'lng' => 100.1152],
            ['upt_id' => 5, 'nama' => 'Desa Torgamba', 'lat' => 1.8901, 'lng' => 100.1302],
            ['upt_id' => 5, 'nama' => 'Desa Beringin Jaya (Torgamba)', 'lat' => 1.8701, 'lng' => 100.1102],

            // WILAYAH 6 (Kanim Sibolga - 9 Desa)
            ['upt_id' => 6, 'nama' => 'Desa Sitio-Tio Hilir', 'lat' => 1.7001, 'lng' => 98.8102],
            ['upt_id' => 6, 'nama' => 'Desa Purba Sinomba', 'lat' => 1.4801, 'lng' => 99.6402],
            ['upt_id' => 6, 'nama' => 'Kelurahan Lumut', 'lat' => 1.5801, 'lng' => 98.9202],
            ['upt_id' => 6, 'nama' => 'Desa Sipaho', 'lat' => 1.5201, 'lng' => 99.6102],
            ['upt_id' => 6, 'nama' => 'Desa Parsihotangan', 'lat' => 2.1201, 'lng' => 98.3902],
            ['upt_id' => 6, 'nama' => 'Desa Binjohara Baru', 'lat' => 2.1251, 'lng' => 98.3952],
            ['upt_id' => 6, 'nama' => 'Desa Saragih Timur', 'lat' => 2.1151, 'lng' => 98.3852],
            ['upt_id' => 6, 'nama' => 'Desa Manduamas Lama', 'lat' => 2.1301, 'lng' => 98.4002],
            ['upt_id' => 6, 'nama' => 'Desa Tumba', 'lat' => 2.1101, 'lng' => 98.3802],

            // WILAYAH 7 (Kanim Mandailing Natal - 5 Desa)
            ['upt_id' => 7, 'nama' => 'Desa Rumbio', 'lat' => 0.8901, 'lng' => 99.5402],
            ['upt_id' => 7, 'nama' => 'Desa Hasahatan Julu', 'lat' => 1.0501, 'lng' => 99.7102],
            ['upt_id' => 7, 'nama' => 'Desa Pasar Baru Malintang', 'lat' => 0.8401, 'lng' => 99.5302],
            ['upt_id' => 7, 'nama' => 'Desa Sibanggor Tonga', 'lat' => 0.6901, 'lng' => 99.5402],
            ['upt_id' => 7, 'nama' => 'Desa Tanjung Mompang', 'lat' => 0.8951, 'lng' => 99.5452],

            // WILAYAH 8 (Kanim Nias - 5 Desa)
            ['upt_id' => 8, 'nama' => 'Desa Botohili Sorake', 'lat' => 0.5601, 'lng' => 97.7202],
            ['upt_id' => 8, 'nama' => 'Desa Miga', 'lat' => 1.2701, 'lng' => 97.6102],
            ['upt_id' => 8, 'nama' => 'Desa Mudik', 'lat' => 1.2801, 'lng' => 97.6202],
            ['upt_id' => 8, 'nama' => 'Desa Bawomataluo', 'lat' => 0.6101, 'lng' => 97.7402],
            ['upt_id' => 8, 'nama' => 'Desa Lasara Bahili', 'lat' => 1.2901, 'lng' => 97.6302],

            // WILAYAH 9 (Kanim Tapanuli Utara - 5 Desa)
            ['upt_id' => 9, 'nama' => 'Desa Bahal Batu I', 'lat' => 2.1401, 'lng' => 98.9802],
            ['upt_id' => 9, 'nama' => 'Desa Bahal Batu II', 'lat' => 2.1451, 'lng' => 98.9852],
            ['upt_id' => 9, 'nama' => 'Desa Bahal Batu III', 'lat' => 2.1351, 'lng' => 98.9752],
            ['upt_id' => 9, 'nama' => 'Desa Paniaran', 'lat' => 2.1501, 'lng' => 98.9902],
            ['upt_id' => 9, 'nama' => 'Lumban Tonga-tonga', 'lat' => 2.1301, 'lng' => 98.9702],

            // WILAYAH 10 (Kanim Padangsidimpuan - 3 Desa)
            ['upt_id' => 10, 'nama' => 'Kelurahan Sabungan Jae', 'lat' => 1.3901, 'lng' => 99.2502],
            ['upt_id' => 10, 'nama' => 'Partihaman', 'lat' => 1.3951, 'lng' => 99.2552],
            ['upt_id' => 10, 'nama' => 'Hutaimbaru', 'lat' => 1.3851, 'lng' => 99.2452],
        ];

        foreach ($allDesaList as $idx => $d) {
            $upt = $uptMap[$d['upt_id']];

            // Ambil PIMPASA resmi di UPT tersebut secara acak/berurutan
            $assignedPimpasa = User::where('role', 'pimpasa')
                ->where('upt_id', $upt->id)
                ->inRandomOrder()
                ->first();

            DesaBinaan::firstOrCreate(
                ['nama' => $d['nama']],
                [
                    'wilayah_id' => $wilayahDefault->id,
                    'upt_id' => $upt->id,
                    'pimpasa_id' => $assignedPimpasa?->id,
                    'lat' => $d['lat'],
                    'lng' => $d['lng'],
                    'status_terkini' => 'aman',
                ]
            );
        }
    }
}
