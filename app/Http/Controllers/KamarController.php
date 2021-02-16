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

    function listKamar(Request $request)
    {
        $user = auth('api')->user();
        // $kost


        // $data = ClassKamar::where('id_kost', $request->id_kost)->where('active', TRUE)->where('nama', 'like', '%' . $request->namakeyword . '%')->get();

        $data = DB::table('kamars')
            ->leftJoin('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
            ->leftJoin('kosts', 'kosts.id', '=', 'class_kamar.id_kost')
            ->leftJoin('users', 'kosts.owner', '=', 'users.id')
            ->where('users.id', $user->id)
            ->where('class_kamar.id', $request->id_kelas)
            ->where('kamars.nama', 'like', '%' . $request->keyword . '%')
            ->where('kamars.active', TRUE)
            ->select('kamars.*',)
            ->get();

        for ($x = 0; $x < count($data); $x++) {
            $penghuni = Penghuni::where('id_kamar', $data[$x]->id)->get();

            // $data[$x]['kapasitas'] = $data_kelas->kapasitas;
            // $banyak_penghuni = count($penghuni);
            // $potong_penghuni = Penghuni::where('kamar',$data[$x]['id'])->limit(2);
            $data[$x]->penghuni = $penghuni;
            // $data[$x]['banyak_penghuni']=$banyak_penghuni;
        }

        return response()->json([
            "message" => "Method Success",
            "data" => $data
        ]);
        // return response()->json($user);
    }

    public function pindahKamar(Request $request)
    {

        $penghuni = Penghuni::where('id', $request->id)->first();
        if ($penghuni) {
            $penghuni->id_kamar = $request->id_kamar;
            $penghuni->save();
            $mytime = Carbon::now('Asia/Jakarta');
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

            $class_kamar = ClassKamar::where('id', $request->id_kelas)->first();

            $tagih = new Tagihan();
            $tagih->id_kamar = $request->id_kamar;
            $tagih->id_penghuni = $request->id;
            $tagih->jumlah = $class_kamar->harga + $biaya_barang;
            $tagih->tanggal_tagihan = $mytime;
            $tagih->lunas = FALSE;

            $tagih->save();

            return response()->json([
                "message" => "Method Success",
                "success" => TRUE
            ]);
        }

        return response()->json([
            "message" => "Method Success",
            "success" => FALSE
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

        $kamar = Kamar::where('id', $id)->first();


        if ($kamar) {
            $kamar->nama = $request->nama ? $request->nama : $kamar->nama;
            $kamar->save();
            return response()->json([
                "message" => "Put Successs ",
                "data" => $kamar,

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

    function hapusKamar(Request $request)
    {
        $class_kamar = Kamar::where('id', $request->id)->first();
        if ($class_kamar) {
            $data =  DB::table('kamars')
                ->leftJoin('penghuni', 'kamars.id', '=', 'penghuni.id')
                ->whereNull('penghuni.tanggal_keluar')
                ->where('kamars.id', $request->id)
                // ->where('penghuni.tanggal_keluar', '!=', null)
                // ->where('penghuni.tanggal_keluar', '!=', "")
                ->select('kamars.*', DB::raw("count(penghuni.id) as count"))
                ->groupBy('kamars.id')
                ->first();

            if ($data->count > 0) {
                return response()->json([
                    "message" => "Kamar masih memiliki Penghuni",
                    "success" => FALSE,
                ]);
            }

            $kelas_hapus = Kamar::where('id', $request->id)->first();
            $kelas_hapus->active = FALSE;
            $kelas_hapus->save();

            $kirim = Kamar::all();

            return response()->json([
                "message" => "Hapus Kelas Berhasil",
                "success" => TRUE,
                "data" => $kirim
            ]);
        }

        return response()->json([
            "message" => "Kamar tidak ditemukan",
            "success" => FALSE
        ]);
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
