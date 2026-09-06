<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserPimpasaSeeder extends Seeder
{
    public function run(): void
    {
        // Data Resmi 53 Petugas PIMPASA Sumatera Utara per Wilayah (1 - 10)
        $pimpasaList = [
            // WILAYAH 1: Kanim Medan (upt_id = 1)
            [
                'upt_id' => 1,
                'name' => 'Irvandus Siboro',
                'nip' => '199211062017121005',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'irvandus.siboro@simpeldbi.go.id',
            ],
            [
                'upt_id' => 1,
                'name' => 'Febri Hariono Nababan',
                'nip' => '200002102019011001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'febri.nababan@simpeldbi.go.id',
            ],
            [
                'upt_id' => 1,
                'name' => 'Crist Yoel Manihuruk',
                'nip' => '200201212022031003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'crist.manihuruk@simpeldbi.go.id',
            ],
            [
                'upt_id' => 1,
                'name' => 'Ananda Bima Pratama',
                'nip' => '200112272025061004',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'ananda.pratama@simpeldbi.go.id',
            ],
            [
                'upt_id' => 1,
                'name' => 'David Rossi Alexander Purba',
                'nip' => '200510232025061004',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'david.purba@simpeldbi.go.id',
            ],

            // WILAYAH 2: Kanim Polonia (upt_id = 2)
            [
                'upt_id' => 2,
                'name' => 'Tahi Halomoan Sihombing',
                'nip' => '197601291999031001',
                'golongan' => 'Penata / (III/c)',
                'email' => 'tahi.sihombing@simpeldbi.go.id',
            ],
            [
                'upt_id' => 2,
                'name' => 'Goklas J Silalahi',
                'nip' => '199602082017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'goklas.silalahi@simpeldbi.go.id',
            ],
            [
                'upt_id' => 2,
                'name' => 'Hukeindo Sitohang',
                'nip' => '198408172014021003',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'hukeindo.sitohang@simpeldbi.go.id',
            ],
            [
                'upt_id' => 2,
                'name' => 'Andri Hizkia Surbakti',
                'nip' => '199301102017121001',
                'golongan' => 'Penata Muda / (III/a)',
                'email' => 'andri.surbakti@simpeldbi.go.id',
            ],
            [
                'upt_id' => 2,
                'name' => 'Muhammad Iman Tanjung',
                'nip' => '197407132006041001',
                'golongan' => 'Penata Muda / (III/a)',
                'email' => 'muhammad.tanjung@simpeldbi.go.id',
            ],
            [
                'upt_id' => 2,
                'name' => 'Mario Maruli Sitinjak',
                'nip' => '200010042019011001',
                'golongan' => 'Penata Muda / (III/a)',
                'email' => 'mario.sitinjak@simpeldbi.go.id',
            ],
            [
                'upt_id' => 2,
                'name' => 'Mohd Tarmizi',
                'nip' => '199602102019011001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'mohd.tarmizi@simpeldbi.go.id',
            ],
            [
                'upt_id' => 2,
                'name' => 'Almirando Ginting',
                'nip' => '199207132019011001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'almirando.ginting@simpeldbi.go.id',
            ],

            // WILAYAH 3: Kanim Belawan (upt_id = 3)
            [
                'upt_id' => 3,
                'name' => 'Dedy Depsa Ginting',
                'nip' => '197512181999031001',
                'golongan' => 'Penata / (III/c)',
                'email' => 'dedy.ginting@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'Felix Andreas Sitompul',
                'nip' => '199409202017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'felix.sitompul@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'Bayu Segara',
                'nip' => '198912272017121003',
                'golongan' => 'Penata Muda / (III/a)',
                'email' => 'bayu.segara@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'Andreas Virga Siahaan',
                'nip' => '200109172020121001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'andreas.siahaan@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'Anggi Ahmad Fahrezi Sitorus',
                'nip' => '200605242025061002',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'anggi.sitorus@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'Novry Hiskia Hamonangan Simanullang',
                'nip' => '200211112025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'novry.simanullang@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'Muhammad Fikri Hakim Nasution',
                'nip' => '199912192025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'fikri.nasution@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'M. Aditya Patrianur',
                'nip' => '200510062025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'aditya.patrianur@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'Albert Radja Sihite',
                'nip' => '200302192025061001',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'albert.sihite@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'M. Raihan Rahmadi',
                'nip' => '200510272025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'raihan.rahmadi@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'Abdillah Muhammad',
                'nip' => '199604062025061006',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'abdillah.muhammad@simpeldbi.go.id',
            ],
            [
                'upt_id' => 3,
                'name' => 'Garry Casvaroch',
                'nip' => '200404202025061002',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'garry.casvaroch@simpeldbi.go.id',
            ],

            // WILAYAH 4: Kanim Siantar (upt_id = 4)
            [
                'upt_id' => 4,
                'name' => 'Sofyan Ansori Tondang',
                'nip' => '198402022010011019',
                'golongan' => 'Penata / (III/c)',
                'email' => 'sofyan.tondang@simpeldbi.go.id',
            ],
            [
                'upt_id' => 4,
                'name' => 'Ruhut Trifosa Sitompul',
                'nip' => '199602082017121002',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'ruhut.sitompul@simpeldbi.go.id',
            ],
            [
                'upt_id' => 4,
                'name' => 'Reza Fiezri Lubis',
                'nip' => '199003162017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'reza.lubis@simpeldbi.go.id',
            ],
            [
                'upt_id' => 4,
                'name' => 'Totonafo Gulo',
                'nip' => '196901161999031001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'totonafo.gulo@simpeldbi.go.id',
            ],
            [
                'upt_id' => 4,
                'name' => 'Leo Ronald Togu Mauliate Sitorus',
                'nip' => '198208022010011016',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'leo.sitorus@simpeldbi.go.id',
            ],
            [
                'upt_id' => 4,
                'name' => 'Septian Yubil Sumurung Sihombing',
                'nip' => '198609262010011017',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'septian.sihombing@simpeldbi.go.id',
            ],

            // WILAYAH 5: Kanim Asahan (upt_id = 5)
            [
                'upt_id' => 5,
                'name' => 'Taufik Swardana Panjaitan',
                'nip' => '199208122017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'taufik.panjaitan@simpeldbi.go.id',
            ],
            [
                'upt_id' => 5,
                'name' => 'Haykal Hafidz',
                'nip' => '200605142025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'haykal.hafidz@simpeldbi.go.id',
            ],
            [
                'upt_id' => 5,
                'name' => 'Vio Aprivia Nugraha',
                'nip' => '200404242025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'vio.nugraha@simpeldbi.go.id',
            ],
            [
                'upt_id' => 5,
                'name' => 'Veronika',
                'nip' => '200404062025062001',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'veronika@simpeldbi.go.id',
            ],
            [
                'upt_id' => 5,
                'name' => 'Imam',
                'nip' => '199412192017121007',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'imam@simpeldbi.go.id',
            ],
            [
                'upt_id' => 5,
                'name' => 'David Fernando S',
                'nip' => '200509162025061001',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'david.fernando@simpeldbi.go.id',
            ],
            [
                'upt_id' => 5,
                'name' => 'Jojor Ivana Silaban',
                'nip' => '200305252025062002',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'jojor.silaban@simpeldbi.go.id',
            ],
            [
                'upt_id' => 5,
                'name' => 'Cristopel Silaban',
                'nip' => '200410182025061002',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'cristopel.silaban@simpeldbi.go.id',
            ],

            // WILAYAH 6: Kanim Sibolga (upt_id = 6)
            [
                'upt_id' => 6,
                'name' => 'Anton Swanda Sinuraya',
                'nip' => '199407252017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'anton.sinuraya@simpeldbi.go.id',
            ],
            [
                'upt_id' => 6,
                'name' => 'Vici Manalu',
                'nip' => '199106042017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'vici.manalu@simpeldbi.go.id',
            ],
            [
                'upt_id' => 6,
                'name' => 'Muhammad Abduh Dalimunthe',
                'nip' => '198612132017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'abduh.dalimunthe@simpeldbi.go.id',
            ],
            [
                'upt_id' => 6,
                'name' => 'Junaidi Doloksaribu',
                'nip' => '198906132017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'junaidi.doloksaribu@simpeldbi.go.id',
            ],
            [
                'upt_id' => 6,
                'name' => 'Muhammad Irham Ardiansyah Samosir',
                'nip' => '200210152025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'irham.samosir@simpeldbi.go.id',
            ],
            [
                'upt_id' => 6,
                'name' => 'Mhd. Zacky Bangun',
                'nip' => '200006302025061009',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'zacky.bangun@simpeldbi.go.id',
            ],
            [
                'upt_id' => 6,
                'name' => 'Edith Favian Daniel Silalahi',
                'nip' => '200210172025061006',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'edith.silalahi@simpeldbi.go.id',
            ],

            // WILAYAH 7: Kanim Mandailing Natal (upt_id = 7)
            [
                'upt_id' => 7,
                'name' => 'Vicky Harlanuari',
                'nip' => '199401142017121001',
                'golongan' => 'Penata Muda / (III/a)',
                'email' => 'vicky.harlanuari@simpeldbi.go.id',
            ],
            [
                'upt_id' => 7,
                'name' => 'Muhammad Iqbal',
                'nip' => '199712032019011001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'muhammad.iqbal@simpeldbi.go.id',
            ],

            // WILAYAH 8: Kanim Nias (upt_id = 8)
            [
                'upt_id' => 8,
                'name' => 'Jose Mario Parlambasan Sagala',
                'nip' => '200211112026021001',
                'golongan' => 'Penata Muda / (III/a)',
                'email' => 'jose.sagala@simpeldbi.go.id',
            ],
            [
                'upt_id' => 8,
                'name' => 'Periseuein Forney Zega',
                'nip' => '199708152020121001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'forney.zega@simpeldbi.go.id',
            ],

            // WILAYAH 9: Kanim Tapanuli Utara (upt_id = 9)
            [
                'upt_id' => 9,
                'name' => 'Raja Huntal Sitanggang',
                'nip' => '200208262025021001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'raja.sitanggang@simpeldbi.go.id',
            ],
            [
                'upt_id' => 9,
                'name' => 'Pangeran Migel Saragih',
                'nip' => '200601022025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'pangeran.saragih@simpeldbi.go.id',
            ],

            // WILAYAH 10: Kanim Padangsidimpuan (upt_id = 10)
            [
                'upt_id' => 10,
                'name' => 'Asnan Hanafi',
                'nip' => '200005292020121001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'asnan.hanafi@simpeldbi.go.id',
            ],
        ];

        foreach ($pimpasaList as $p) {
            User::updateOrCreate(
                ['nip' => $p['nip']],
                [
                    'name' => $p['name'],
                    'email' => $p['email'],
                    'password' => Hash::make('password123'),
                    'role' => 'pimpasa',
                    'upt_id' => $p['upt_id'],
                    'golongan' => $p['golongan'],
                    'kontak' => '0812' . rand(10000000, 99999999),
                    'is_active' => true,
                ]
            );
        }
    }
}
