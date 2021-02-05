<?php

namespace App\Console\Commands;

use App\Penghuni;
use App\Tagihan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TaskTagihan2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tagihan:kedua';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'tagihan kedua yang per jam';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $kamarku = DB::table('penghuni')
            ->join('kamars', 'penghuni.kamar', '=', 'kamars.id')
            ->join('class_kamar', 'kamars.kelas', '=', 'class_kamar.id')
            ->select('penghuni.*', 'kamars.id as id_kamar', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
            ->get();

        for ($j = 2; $j > 0; $j--) {
            $mytime = Carbon::now('Asia/Jakarta')->subMonth($j);
            // $kamarku[$x]->yaya = $kamarku[$x]->harga_kamar - 100;

            // jika kurang dari tagihan kurang dari 0 tidak perlu karena berarti memiliki kelebihan bayar

            $mybulan = $mytime->format('m');
            // $mybulan = $data[$x]['tanggal_daftar']->format('m');
            $namabulan = '';
            if ($mybulan == '01') {
                $namabulan = 'Januari';
            } elseif ($mybulan == '02') {
                $namabulan = 'Februari';
            } elseif ($mybulan == '03') {
                $namabulan = 'Maret';
            } elseif ($mybulan == '04') {
                $namabulan = 'April';
            } elseif ($mybulan == '05') {
                $namabulan = 'Mei';
            } elseif ($mybulan == '06') {
                $namabulan = 'Juni';
            } elseif ($mybulan == '07') {
                $namabulan = 'Juli';
            } elseif ($mybulan == '08') {
                $namabulan = 'Agustus';
            } elseif ($mybulan == '09') {
                $namabulan = 'September';
            } elseif ($mybulan == '10') {
                $namabulan = 'Oktober';
            } elseif ($mybulan == '11') {
                $namabulan = 'November';
            } else {
                $namabulan = 'Desember';
            }
            for ($x = 0; $x < count($kamarku); $x++) {
                $tagih = new Tagihan();
                $tagih->id_kost = $kamarku[$x]->id_kostan;
                $tagih->id_penghuni = $kamarku[$x]->id;
                $tagih->judul = "Tagihan sewa " . $kamarku[$x]->nama_kamar;
                $tagih->desc = "Tagihan sewa kamar periode : " . $namabulan . '-' . $mytime->format('Y') . ". Atas Nama : " . $kamarku[$x]->nama_depan . ' ' . $kamarku[$x]->nama_belakang . '(id:' . $kamarku[$x]->id . '), yang menghuni : ' . $kamarku[$x]->nama_kamar . '(id:' . $kamarku[$x]->id_kamar . ')';
                $tagih->jumlah = $kamarku[$x]->harga_kamar;
                $tagih->tanggal_tagihan = $mytime;
                $tagih->status = TRUE;

                $tagih->save();
            }

            Penghuni::where('active', TRUE)->increment('tagihan');
        }
    }
}
