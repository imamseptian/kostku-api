<?php

namespace App\Http\Controllers;

use App\ClassKamar;
use App\Kamar;
use App\Kost;
use Illuminate\Http\Request;
use App\Penghuni;
use App\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KamarController extends Controller
{
    function get()
    {
        $data = Kamar::all();

        return response()->json([
            "message" => "GET Method Success",
            "data" => $data
        ]);
    }

    function getById($id)
    {
        $data = Kamar::where('id', $id)->get();

        return response()->json([
            "message" => "GET Method by ID Success",
            "data" => $data
        ]);
    }

    function daftarKamar(Request $request)
    {
        $data = Kamar::where('id_kelas', $request->id)->where('active', TRUE)->where('nama', 'like', '%' . $request->namakeyword . '%')->orderBy($request->sortname, $request->orderby)->paginate(10);
        // $data = Kamar::where('id',$request->id)->get();
        for ($x = 0; $x < count($data); $x++) {
            $penghuni = Penghuni::where('id_kamar', $data[$x]['id'])->get();
            for ($y = 0; $y < count($penghuni); $y++) {
                $penghuni[$y]['tanggal_masuk'] = Carbon::parse($penghuni[$y]['tanggal_masuk']);
                $penghuni[$y]['tanggal_lahir'] = Carbon::parse($penghuni[$y]['tanggal_lahir']);
            }
            // for ($y = 0; $y < count($penghuni); $y++) {
            //     $tagihan = Tagihan::where('id_penghuni', $penghuni[$y]['id'])->where('status', TRUE)->get();
            //     $penghuni[$y]['mytagihan'] = $tagihan;
            // }
            $data_kelas = ClassKamar::where('id', $data[$x]['id_kelas'])->first();
            $data[$x]['kapasitas'] = $data_kelas->kapasitas;
            // $banyak_penghuni = count($penghuni);
            // $potong_penghuni = Penghuni::where('kamar',$data[$x]['id'])->limit(2);
            $data[$x]['penghuni'] = $penghuni;
            // $data[$x]['banyak_penghuni']=$banyak_penghuni;
        }


        return response()->json([
            "message" => "GET Method by ID Success",
            "data" => $data
        ]);
    }

    // function getByKelas($id)
    // {
    //     $data = Kamar::where('kelas', $id)->orderBy('nama')->get();

    //     return response()->json([
    //         "message" => "GET Method by kelas Success",
    //         "data" => $data
    //     ]);
    // }

    function searchKamar($id, $search)
    {
        $data = Kamar::where('kelas', $id)->where('nama', 'ilike', '%' . $search . '%')->orderBy('nama')->get();

        return response()->json([
            "message" => "GET Method by kelas Success",
            "search" => $search,
            "jumlah" => count($data),
            "data" => $data
        ]);
    }

    function ayaya(Request $request)
    {
        $custom_time = Carbon::parse('1998-09-09T00:00:00.000000Z');

        return response()->json([
            "message" => "GET Method by kelas Success",
            "taanggal" => $custom_time,

        ]);
    }


    function post(Request $request)
    {

        // $arrKamar=[];
        for ($x = 0; $x < $request->qty; $x++) {
            $kamar = new Kamar();
            $kamar->nama = $request->nama;
            $kamar->id_kelas = $request->id_kelas;
            $kamar->save();
        }

        return response()->json([
            "message" => "Post Kamar penghuni Berhasil",
            "data" => $request->qty
        ]);
    }
    function put($id, Request $request)
    {

        $kost = Kost::where('id', $id)->first();


        if ($kost) {
            $kost->kamar = $request->kamar ? $request->kamar : $kost->kamar;
            $kost->harga = $request->harga ? $request->harga : $kost->harga;
            $kost->fasilitas = $request->fasilitas ? $request->fasilitas : $kost->fasilitas;
            $kost->active = $request->active ? $request->active : $kost->active;

            $kost->save();
            return response()->json([
                "message" => "Put Successs ",
                "data" => $kost,
                "harga" => $request->harga
            ]);
        }
        return response()->json([
            "message" => "Kost dengan id " . $id . " Tidak Ditemukan"
        ], 400);
    }
    function delete($id)
    {

        $kamar = Kamar::where('id', $id)->first();
        if ($kamar) {
            $kamar->delete();
            return response()->json([
                "message" => "Delete Kamar dengan id " . $id . " Berhasil"
            ]);
        }

        return response()->json([
            "message" => "Delete Kamar dengan id " . $id . " Tidak Ditemukan"
        ], 400);
    }

    public function allKamars()
    {
        // $kamars= Kamar::paginate(10,['*'],'page');
        // $kamars= Kamar::paginate(10);
        // $kamars = Kamar::where('penghuni',9)->paginate(10);

        // return response()->json([
        //     "data"=>$kamars,
        //     "message"=>"ayaya",
        // ]);
    }
}
