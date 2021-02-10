<?php

namespace App\Http\Controllers;

use App\Barang_Tambahan_Penghuni;
use App\Penghuni;
use App\Tagihan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{
    function getAll()
    {
        $data = Tagihan::orderBy('tanggal_tagihan', 'asc')->get();

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "tagihan berhasil",
            "data" => $data,
            // 'myfile'=>$files
        ]);
    }

    function tagihanPenghuni()
    {
        $kamarku = DB::table('penghuni')
            ->join('kamars', 'penghuni.kamar', '=', 'kamars.id')
            ->join('class_kamar', 'kamars.kelas', '=', 'class_kamar.id')
            ->select('penghuni.*', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
            ->get();

        for ($x = 0; $x < count($kamarku); $x++) {

            // $kamarku[$x]->yaya = $kamarku[$x]->harga_kamar - 100;
            $tagih = new Tagihan();
            $tagih->id_kost = $kamarku[$x]->id_kost;
            $tagih->id_penghuni = $kamarku[$x]->id;
            $tagih->judul = "Tagihan bulanan penghuni";
            $tagih->desc = "Tagihan bulanan penghuni " . $kamarku[$x]->nama  . '(id:' . $kamarku[$x]->id . ')';
            $tagih->jumlah = $kamarku[$x]->harga_kamar;
            $tagih->status = 0;

            $tagih->save();

            // $data[$x]['hari'] = Carbon::parse($data[$x]['tanggal_daftar'])->format('d');
            // $data[$x]['bulan'] = $namabulan;
            // $data[$x]['tahun'] = Carbon::parse($data[$x]['tanggal_daftar'])->format('Y');
        }

        // foreach ($kamarku as $kk) {
        //     $kk->jas = 5000;
        // }
        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "tagihan berhasil",
            // 'myfile'=>$files
        ]);
    }

    function riwayatPembayaranSewa($id, Request $request)
    {
        $data = Tagihan::where('id_penghuni', $id)->where('lunas', TRUE)->get();
        // $data = DB::table('tagihan')
        //     ->leftJoin('penghuni', 'tagihan.id_penghuni', '=', 'penghuni.id')

        //     ->select('transaksi.*', 'penghuni.nama_depan as nama_depan', 'penghuni.nama_belakang as nama_belakang')
        //     ->where('transaksi.id_kost', $request->id_kost)
        //     ->whereYear('transaksi.tanggal_transaksi', $request->tahun)
        //     ->whereMonth('transaksi.tanggal_transaksi', $request->bulan)
        //     ->where('transaksi.jenis', 1)
        //     ->orderBy('transaksi.tanggal_transaksi', 'asc')
        //     ->get();
        for ($x = 0; $x < count($data); $x++) {
            $tanggal_tagihan = $data[$x]['tanggal_tagihan'];

            $data[$x]['tanggal_tagihan'] = Carbon::parse($tanggal_tagihan);
            $data[$x]['tanggal_pelunasan'] = Carbon::parse($data[$x]['tanggal_pelunasan']);
            $data[$x]['kamar'] = DB::table('penghuni')
                ->join('kamars', 'kamars.id', '=', 'penghuni.id_kamar')
                ->join('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
                // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
                ->select('class_kamar.*')
                ->where('penghuni.id', $id)
                ->first();



            $data[$x]['barang'] = DB::table('barang_tambahan_penghuni')
                ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
                // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
                ->select('barang_tambahan_penghuni.*', 'barang.nama as nama')
                ->where('barang_tambahan_penghuni.id_penghuni', $id)
                ->where('barang_tambahan_penghuni.tanggal_masuk', '<=', Carbon::parse($tanggal_tagihan))
                ->where(function ($query) use ($tanggal_tagihan) {
                    $query->where('barang_tambahan_penghuni.tanggal_keluar', '>=', Carbon::parse($tanggal_tagihan))
                        ->orWhere('barang_tambahan_penghuni.tanggal_keluar', null);
                })

                ->get();

            // $data[$x]['barang'] = Barang_Tambahan_Penghuni::whereDate('tanggal_masuk', '<=', $data[$x]['tanggal_tagihan'])
            //     ->whereDate('tanggal_keluar', '>=', $data[$x]['tanggal_tagihan'])
            //     ->orWhere('tanggal_keluar', null)
            //     ->get();
        }

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "get riwayat berhasil",
            "data" => $data
            // 'myfile'=>$files
        ]);
    }

    function getTagihan($id)
    {
        // $data = Tagihan::where('id_penghuni', $id)->where('lunas', FALSE)->orderBy('tanggal_tagihan', 'asc')->get();
        $data = DB::table('tagihan')
            ->join('penghuni', 'tagihan.id_penghuni', '=', 'penghuni.id')
            ->join('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
            ->join('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
            ->select('tagihan.*', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
            ->where('tagihan.id_penghuni', $id)
            ->where('tagihan.lunas', FALSE)
            ->orderBy('tagihan.tanggal_tagihan', 'asc')
            ->get();
        for ($x = 0; $x < count($data); $x++) {
            $tanggal_tagihan = $data[$x]->tanggal_tagihan;
            $data[$x]->tanggal_tagihan = Carbon::parse($tanggal_tagihan);
            // $data[$x]['barang'] = Carbon::parse($data[$x]['tanggal_tagihan']);
            $data[$x]->barang = DB::table('barang_tambahan_penghuni')
                ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
                // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
                ->select('barang_tambahan_penghuni.*', 'barang.nama as nama')
                ->where('barang_tambahan_penghuni.id_penghuni', $id)
                ->where('barang_tambahan_penghuni.tanggal_masuk', '<=', Carbon::parse($tanggal_tagihan))
                ->where(function ($query) use ($tanggal_tagihan) {
                    $query->where('barang_tambahan_penghuni.tanggal_keluar', '>=', Carbon::parse($tanggal_tagihan))
                        ->orWhere('barang_tambahan_penghuni.tanggal_keluar', null);
                })

                ->get();
            // $mybulan = $data[$x]['tanggal_daftar']->format('m');
        }

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "get berhasil",
            "tagihan" => $data,
            // 'myfile'=>$files
        ]);
    }

    function createCustomTagihan(Request $request)
    {
        // $customday = Carbon::now('Asia/Jakarta')->month($request->month)->year($request->year);


        $kamarku = DB::table('penghuni')
            ->leftJoin('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
            ->leftJoin('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
            ->select('penghuni.*', 'kamars.id as id_kamar', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
            ->where('penghuni.id_kost', $request->id_kost)
            ->get();


        // $mytime = Carbon::now('Asia/Jakarta')->subMonth($j);
        // $mytime = Carbon::now('Asia/Jakarta')->month($request->month)->year($request->year);
        $mytime = Carbon::now('Asia/Jakarta');


        // $kamarku[$x]->yaya = $kamarku[$x]->harga_kamar - 100;

        // jika kurang dari tagihan kurang dari 0 tidak perlu karena berarti memiliki kelebihan bayar


        for ($x = 0; $x < count($kamarku); $x++) {

            // $biaya_barang = DB::table('barang_tambahan_penghuni')
            //     ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
            //     // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
            //     ->select('barang_tambahan_penghuni.*', 'barang.nama as nama')
            //     ->where('barang_tambahan_penghuni.id_penghuni', $kamarku[$x]->id)
            //     ->where('barang_tambahan_penghuni.active', TRUE)
            //     ->sum('barang_tambahan_penghuni.total');

            // $biaya_barang = DB::table('barang_tambahan_penghuni')
            //     ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
            //     // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
            //     ->select('barang_tambahan_penghuni.*', 'barang.nama as nama')
            //     ->where(function ($query) use ($mytime) {
            //         $query->whereDate('barang_tambahan_penghuni.tanggal_masuk', '<=', $mytime)
            //             ->whereTime('barang_tambahan_penghuni.tanggal_masuk', '<=', $mytime->format('H:i:s'));
            //     })->where(function ($query) use ($mytime) {
            //         $query->where(function ($query) use ($mytime) {
            //             $query->whereDate('barang_tambahan_penghuni.tanggal_keluar', '>=', $mytime)
            //                 ->whereTime('barang_tambahan_penghuni.tanggal_keluar', '>=', $mytime->format('H:i:s'));
            //         })->orWhere('barang_tambahan_penghuni.tanggal_keluar', null);
            //     })->sum('barang_tambahan_penghuni.total');

            $biaya_barang = DB::table('barang_tambahan_penghuni')
                ->leftJoin('penghuni', 'penghuni.id', '=', 'barang_tambahan_penghuni.id_penghuni')
                ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
                // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
                ->select('barang_tambahan_penghuni.*', 'barang.nama as nama')
                ->where('barang_tambahan_penghuni.tanggal_masuk', '<=', $mytime)
                ->where(function ($query) use ($mytime) {
                    $query->where('barang_tambahan_penghuni.tanggal_keluar', '>=', $mytime)
                        ->orWhere('barang_tambahan_penghuni.tanggal_keluar', null);
                    // $query->where(function ($query) use ($tanggal_tagihan) {
                    //     $query->where('barang_tambahan_penghuni.tanggal_masuk', '<=', Carbon::parse($tanggal_tagihan));
                    // })->orWhere('barang_tambahan_penghuni.tanggal_keluar', null);
                })
                ->where('penghuni.id', $kamarku[$x]->id)
                ->sum('barang_tambahan_penghuni.total');

            $tagih = new Tagihan();
            $tagih->id_kamar = $kamarku[$x]->id_kamar;
            $tagih->id_penghuni = $kamarku[$x]->id;
            $tagih->jumlah = $kamarku[$x]->harga_kamar + $biaya_barang;
            $tagih->tanggal_tagihan = $mytime;
            $tagih->lunas = FALSE;

            $tagih->save();
        }





        // $customjam = Carbon::now('Asia/Jakarta')->month(10)->year(2020)->format('H:i');
        // $sekarang = Carbon::now('Asia/Jakarta');
        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "mytime" => $mytime,
            "bulan" => $request->month,
            "tahun" => $request->year,

        ]);
    }

    public function cekTagihan()
    {
        $data = DB::table('penghuni')
            ->join('tagihan', 'tagihan.id_penghuni', '=', 'penghuni.id')
            ->where('tagihan.lunas', FALSE)
            ->where('penghuni.id_kost', 1)
            ->select('penghuni.id as id', 'penghuni.nama as nama', DB::raw("count(tagihan.id) as count"))
            ->groupBy('penghuni.id')
            ->orderBy('count', 'desc')
            ->get();

        if (count($data) > 0) {
            return response()->json([
                "message" => "ADA TAGIHAN",
                "code" => 200,
                "data" => $data,
                "banyak" => count($data)

            ]);
        } else {
            return response()->json([
                "message" => "LUNAS SEMUA",
                "code" => 200,
                "data" => $data,
                "banyak" => count($data)
            ]);
        }
    }

    public function notifikasiTagihan(Request $request)
    {
        // $terima, $nama, $notelp, $id_kost, $alasan
        // $fields = array('number' => $request->number, 'message' => $request->number);
        // $this->notifikasiWA($request->terima,$request->nama, $request->email, $request->id_kost, $request->alasan);

        // $kost = Kost::where('id', $id_kost)->first();
        // $owner = Kost::where('id', $kost->owner)->first();
        $mytime = Carbon::now('Asia/Jakarta');
        $mybulan = $mytime->format('m');
        $mytahun = $mytime->format('Y');

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



        // $penghuni = Penghuni::where('id', $request->id)->first();
        $penghuni = DB::table('penghuni')
            ->join('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
            ->join('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
            ->join('kosts', 'class_kamar.id_kost', '=', 'kosts.id')
            ->select('penghuni.*', 'class_kamar.harga as harga_kamar', 'class_kamar.nama as nama_kamar', 'kosts.nama as nama_kost', 'kosts.notelp as notelp_kost')
            ->where('penghuni.id', $request->id)
            ->first();

        $biaya_barang = DB::table('barang_tambahan_penghuni')
            ->leftJoin('penghuni', 'penghuni.id', '=', 'barang_tambahan_penghuni.id_penghuni')
            ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
            // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
            ->select('barang_tambahan_penghuni.*', 'barang.nama as nama')
            ->where('barang_tambahan_penghuni.tanggal_masuk', '<=', $mytime)
            ->where(function ($query) use ($mytime) {
                $query->where('barang_tambahan_penghuni.tanggal_keluar', '>=', $mytime)
                    ->orWhere('barang_tambahan_penghuni.tanggal_keluar', null);
                // $query->where(function ($query) use ($tanggal_tagihan) {
                //     $query->where('barang_tambahan_penghuni.tanggal_masuk', '<=', Carbon::parse($tanggal_tagihan));
                // })->orWhere('barang_tambahan_penghuni.tanggal_keluar', null);
            })
            ->where('penghuni.id', $request->id)
            ->sum('barang_tambahan_penghuni.total');


        // $pesan = 'Hai ' . $penghuni->nama . '\n\nAnda telah diterima menjadi penghuni ' . $kost->nama . '\nSilahkan persiapkan perpindahan dan segera datang ke kost sesegera mungkin\n\nHubungi pengelola kost ernis @' . $kost->notelp . ' untuk informasi lebih lanjut.\nTerima Kasih';
        $pesan = 'Hai ' . $penghuni->nama . '\n\nBerikut adalah tagihan bulanan sewa kamar kost anda periode ' . $namabulan . '-' . $mytahun . ' :\n\nBiaya barang bawaan = Rp ' . $biaya_barang . '\nBiaya sewa kamar = Rp ' . $penghuni->harga_kamar . '\n\nTotal tagihan bulan ini = Rp ' . ($biaya_barang + $penghuni->harga_kamar) . '\n\nSilahkan hubungi pengelola kost pada @' . $penghuni->notelp_kost . ' untuk informasi lebih lanjut.\n\nTerima Kasih\n-Pengelola ' . $penghuni->nama_kost;
        $pesan1 = str_replace(array("\\n", "\\r"), array("\n", "\r"), $pesan);



        // return response()->json([
        //     "code" => 200,
        //     "penghuni" => $penghuni,
        //     "biaya" => $biaya_barang,
        //     "pesan" => $pesan
        // ]);
        $data = array(
            'number' => $request->notelp,
            'message' => $pesan1
            // 'message' => $pesan
        );

        $payload = json_encode($data);

        // Prepare new cURL resource
        $ch = curl_init('https://kostku-whatsapp-api.herokuapp.com/send-message');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set HTTP Header for POST request
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            )
        );

        // Submit the POST request
        $result = curl_exec($ch);

        // Close cURL session handle
        curl_close($ch);

        return response()->json([
            "code" => 200,
            "res" => $result,
            "message" => $pesan,
            "message1" => $pesan1
        ]);
    }
}
