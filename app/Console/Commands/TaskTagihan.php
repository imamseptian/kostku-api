<?php

namespace App\Console\Commands;

use App\Penghuni;
use App\Tagihan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;

class TaskTagihan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tagihan:pertama';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tagihan per 30 menit boss';

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
        // $datapenghuni = Penghuni::all();
        $kamarku = DB::table('penghuni')
            ->join('kamars', 'penghuni.kamar', '=', 'kamars.id')
            ->join('class_kamar', 'kamars.kelas', '=', 'class_kamar.id')
            ->select('penghuni.*', 'kamars.id as id_kamar', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
            ->get();

        for ($x = 0; $x < count($kamarku); $x++) {
            $mytime = Carbon::now('Asia/Jakarta');
            // $kamarku[$x]->yaya = $kamarku[$x]->harga_kamar - 100;

            // jika kurang dari tagihan kurang dari 0 tidak perlu karena berarti memiliki kelebihan bayar
            if ($kamarku[$x]->tagihan >= 0) {
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



            // $data[$x]['hari'] = Carbon::parse($data[$x]['tanggal_daftar'])->format('d');
            // $data[$x]['bulan'] = $namabulan;
            // $data[$x]['tahun'] = Carbon::parse($data[$x]['tanggal_daftar'])->format('Y');
        }

        Penghuni::where('active', TRUE)->increment('tagihan');

        // $tagih = new Tagihan();
        // $tagih->id_kost = 1;
        // $tagih->id_penghuni = 1;
        // $tagih->judul = "Tagihan kamar1";
        // $tagih->desc = "tagihan bulanan";
        // $tagih->jumlah = 15045000;
        // $tagih->status = 1;

        // $tagih->save();



        // $message = "ayaya";




        // $from = config('services.twilio.whatsapp_from');


        // $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));


        // return $twilio->messages->create('whatsapp:+6285540452061', [
        //     "from" => 'whatsapp:' . $from,
        //     "body" => "test whatsapp"
        // ]);

        // return 0;
    }
}
