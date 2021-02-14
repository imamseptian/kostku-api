<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $faker = Faker::create();
        // foreach(range(1,500) as $index)
        // {
        //     DB::table('kamars')->insert([
        //         'nama'=>$faker->name,
        //         'kelas'=>1,
        //         'penghuni'=>9,
        //         'active'=>TRUE,
        //     ]);
        // }

        $faker = Faker::create();
        $mytime = Carbon::now('Asia/Jakarta');
        foreach (range(1, 30) as $index) {
            DB::table('kosts')->insert([
                // 'id_kost' => 1,
                // 'nama_depan' => $faker->name,
                // 'nama_belakang' => $faker->name,
                // 'kelamin' => rand(1, 2),
                // 'tanggal_lahir' => $faker->dateTimeBetween($startDate = '-30 years', $endDate = '-18 years', $timezone = 'Asia/Jakarta'),
                // 'provinsi' => rand(11, 19),
                // 'kota' => rand(3207, 3277),
                // 'email' => $faker->email,
                // 'alamat' => $faker->address,
                // 'notelp' => $faker->phoneNumber,
                // 'noktp' => $faker->numerify('#############################'),
                // 'foto_diri' => 'ayaya.png',
                // 'foto_ktp' => 'ayaya.png',
                // 'status' => rand(1, 2),
                // 'tempat_kerja_pendidikan' => $faker->address,
                // 'id_kamar' => rand(1, 3),
                // 'active' => TRUE,
                // 'tanggal_masuk' => $mytime

                'nama' => $faker->name,
                'provinsi' => rand(11, 19),
                'kota' => rand(3207, 3277),
                'alamat' => $faker->address,
                'notelp' => $faker->phoneNumber,
                'foto_kost' => 'ayaya.png',
                'deskripsi' => $faker->address,
                'owner' => rand(1, 2),
                'jenis' => rand(1, 3),
            ]);
        }
        // $this->call(UserSeeder::class);
    }
}
