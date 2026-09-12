<?php

namespace Database\Seeders;

use App\Models\DesaBinaan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserPimpasaSeeder extends Seeder
{
    public function run(): void
    {
        // Data Resmi Petugas PIMPASA Sumatera Utara per Wilayah (1 - 10)
        $pimpasaList = [
            // WILAYAH 1: Kanim Medan (upt_id = 1)
            [
                'upt_id' => 1,
                'name' => 'Irvandus Siboro',
                'nip' => '199211062017121005',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'irvanboro92@gmail.com',
            ],
            [
                'upt_id' => 1,
                'name' => 'Febri Hariono Nababan',
                'nip' => '200002102019011001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'febrihariononababan@gmail.com',
            ],
            [
                'upt_id' => 1,
                'name' => 'Crist Yoel Manihuruk',
                'nip' => '200201212022031003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'crist.yoel.manihuruk77@gmail.com',
            ],
            [
                'upt_id' => 1,
                'name' => 'Ananda Bima Pratama',
                'nip' => '200112272025061004',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'anandabimaaa27@gmail.com',
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
                'email' => 'moansihombing@gmail.com',
            ],
            [
                'upt_id' => 2,
                'name' => 'Goklas J Silalahi',
                'nip' => '199602082017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'Goklas.Januari.silalahi@imigraasi.go.id',
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
                'email' => 'andrihizkia13@gmail.com',
            ],
            [
                'upt_id' => 2,
                'name' => 'Muhammad Iman Tanjung',
                'nip' => '197407132006041001',
                'golongan' => 'Penata Muda / (III/a)',
                'email' => 'atadizan@gmail.com',
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
                'email' => 'tarmizimeszy@gmail.com',
            ],
            [
                'upt_id' => 2,
                'name' => 'Almirando Ginting',
                'nip' => '199207132019011001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'randogintings@gmail.com',
            ],

            // WILAYAH 3: Kanim Belawan (upt_id = 3)
            [
                'upt_id' => 3,
                'name' => 'Dedy Depsa Ginting',
                'nip' => '197512181999031001',
                'golongan' => 'Penata / (III/c)',
                'email' => 'dedydepsa@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'Felix Andreas Sitompul',
                'nip' => '199409202017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'felixandreas31@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'Bayu Segara',
                'nip' => '198912272017121003',
                'golongan' => 'Penata Muda / (III/a)',
                'email' => 'bayusegara27@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'Andreas Virga Siahaan',
                'nip' => '200109172020121001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'siahaanandreas99@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'Anggi Ahmad Fahrezi Sitorus',
                'nip' => '200605242025061002',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'anggivivorantauprapat@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'Novry Hiskia Hamonangan Simanullang',
                'nip' => '200211112025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'noprysimanullang@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'Muhammad Fikri Hakim Nasution',
                'nip' => '199912192025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'fikribmnbelawan@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'M. Aditya Patrianur',
                'nip' => '200510062025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'patrianur2005@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'Albert Radja Sihite',
                'nip' => '200302192025061001',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'albertsht10@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'M. Raihan Rahmadi',
                'nip' => '200510272025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'rahmadi.raihan27@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'Abdillah Muhammad',
                'nip' => '199604062025061006',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'abdillahmuhammadzsb@gmail.com',
            ],
            [
                'upt_id' => 3,
                'name' => 'Garry Casvaroch',
                'nip' => '200404202025061002',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'gcasvaroch@gmail.com',
            ],

            // WILAYAH 4: Kanim Siantar (upt_id = 4)
            [
                'upt_id' => 4,
                'name' => 'Sofyan Ansori Tondang',
                'nip' => '198402022010011019',
                'golongan' => 'Penata / (III/c)',
                'email' => 'ansori.tondang@gmail.com',
            ],
            [
                'upt_id' => 4,
                'name' => 'Ruhut Trifosa Sitompul',
                'nip' => '199602082017121002',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'ruhuttrifosa1996@gmail.com',
            ],
            [
                'upt_id' => 4,
                'name' => 'Reza Fiezri Lubis',
                'nip' => '199003162017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'reza.fiezri16@gmail.com',
            ],
            [
                'upt_id' => 4,
                'name' => 'Totonafo Gulo',
                'nip' => '196901161999031001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'gtotonafo@gmail.com',
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
                'email' => 'bgyubil26tian@gmail.com',
            ],
            [
                'upt_id' => 4,
                'name' => 'Leonardo',
                'nip' => '199908082025061001',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'turislokalbaru99@gmail.com',
            ],

            // WILAYAH 5: Kanim Asahan (upt_id = 5)
            [
                'upt_id' => 5,
                'name' => 'Taufik Swardana Panjaitan',
                'nip' => '199208122017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'taufikswardanapjt@gmail.com',
            ],
            [
                'upt_id' => 5,
                'name' => 'Haykal Hafidz',
                'nip' => '200605142025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'ekalhfidzi@gmail.com',
            ],
            [
                'upt_id' => 5,
                'name' => 'Vio Aprivia Nugraha',
                'nip' => '200404242025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'vioapriviablah@gmail.com',
            ],
            [
                'upt_id' => 5,
                'name' => 'Veronika',
                'nip' => '200404062025062001',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'brveronika01@gmail.com',
            ],
            [
                'upt_id' => 5,
                'name' => 'Imam',
                'nip' => '199412192017121007',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'imam.music88@gmail.com',
            ],
            [
                'upt_id' => 5,
                'name' => 'David Fernando S',
                'nip' => '200509162025061001',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'davidfernandosilalahi@gmail.com',
            ],
            [
                'upt_id' => 5,
                'name' => 'Jojor Ivana Silaban',
                'nip' => '200305252025062002',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'jojorivanasilaban@gmail.com',
            ],
            [
                'upt_id' => 5,
                'name' => 'Cristopel Silaban',
                'nip' => '200410182025061002',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'cristopelsilaban00@gmail.com',
            ],

            // WILAYAH 6: Kanim Sibolga (upt_id = 6)
            [
                'upt_id' => 6,
                'name' => 'Anton Swanda Sinuraya',
                'nip' => '199407252017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'swandaanton@gmail.com',
            ],
            [
                'upt_id' => 6,
                'name' => 'Vici Manalu',
                'nip' => '199106042017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'Manaluuvici@gmail.com',
            ],
            [
                'upt_id' => 6,
                'name' => 'Muhammad Abduh Dalimunthe',
                'nip' => '198612132017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'abduhabduh9@gmail.com',
            ],
            [
                'upt_id' => 6,
                'name' => 'Junaidi Doloksaribu',
                'nip' => '198906132017121001',
                'golongan' => 'Penata Muda Tk. I / (III/b)',
                'email' => 'junaidi89@gmail.com',
            ],
            [
                'upt_id' => 6,
                'name' => 'Muhammad Irham Ardiansyah Samosir',
                'nip' => '200210152025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'ardiansyahirham54@gmail.com',
            ],
            [
                'upt_id' => 6,
                'name' => 'Mhd. Zacky Bangun',
                'nip' => '200006302025061009',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'mzackybangun@gmail.com',
            ],
            [
                'upt_id' => 6,
                'name' => 'Edith Favian Daniel Silalahi',
                'nip' => '200210172025061006',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'silalahiedith10@gmail.com',
            ],

            // WILAYAH 7: Kanim Mandailing Natal (upt_id = 7)
            [
                'upt_id' => 7,
                'name' => 'Vicky Harlanuari',
                'nip' => '199401142017121001',
                'golongan' => 'Penata Muda / (III/a)',
                'email' => 'harlanvicky14@gmail.com',
            ],
            [
                'upt_id' => 7,
                'name' => 'Muhammad Iqbal',
                'nip' => '199712032019011001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'mhdiqball03psp@gmailo.com',
            ],

            // WILAYAH 8: Kanim Nias (upt_id = 8)
            [
                'upt_id' => 8,
                'name' => 'Jose Mario Parlambasan Sagala',
                'nip' => '200211112026021001',
                'golongan' => 'Penata Muda / (III/a)',
                'email' => 'jmp.sagala@gmail.com',
            ],
            [
                'upt_id' => 8,
                'name' => 'Periseuein Forney Zega',
                'nip' => '199708152020121001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'forneyzeganew97@gmail.com',
            ],

            // WILAYAH 9: Kanim Tapanuli Utara (upt_id = 9)
            [
                'upt_id' => 9,
                'name' => 'Raja Huntal Sitanggang',
                'nip' => '200208262025021001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'rajasitanggang00@gmail.com',
            ],
            [
                'upt_id' => 9,
                'name' => 'Pangeran Migel Saragih',
                'nip' => '200601022025061003',
                'golongan' => 'Pengatur Muda / (II/a)',
                'email' => 'pangeranmigel5@gmail.com',
            ],

            // WILAYAH 10: Kanim Padangsidimpuan (upt_id = 10)
            [
                'upt_id' => 10,
                'name' => 'Asnan Hanafi',
                'nip' => '200005292020121001',
                'golongan' => 'Pengatur Muda Tk. I / (II/b)',
                'email' => 'asnanhanafi47@gmail.com',
            ],
        ];

        $passwordHash = Hash::make('password123');

        foreach ($pimpasaList as $p) {
            $desa = DesaBinaan::where('upt_id', $p['upt_id'])->inRandomOrder()->first();

            $user = User::updateOrCreate(
                ['nip' => $p['nip']],
                [
                    'name' => $p['name'],
                    'email' => $p['email'],
                    'password' => $passwordHash,
                    'role' => 'pimpasa',
                    'upt_id' => $p['upt_id'],
                    'desa_id' => $desa?->id,
                    'golongan' => $p['golongan'],
                    'kontak' => '0812' . rand(10000000, 99999999),
                    'is_active' => true,
                ]
            );
            $user->syncRoles(['pimpasa']);
        }
    }
}
