<?php

namespace App\Http\Controllers;

use App\Barang;
use App\Barang_Tambahan_Penghuni;
use App\Tagihan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function storeGambar(Request $request)
    {
        $image_64 = $request->foto_profil; //your base64 encoded data

        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);

        $imageName = Str::random(10) . '.' . $extension;
        $thumbnailImage = Image::make($image_64);
        $thumbnailImage->stream(); // <-- Key point
        Storage::disk('local')->put('public/images/' . $imageName, $thumbnailImage);
        return response()->json([
            "code" => 200

        ]);
    }

    function barangPendaftar(Request $request)
    {
        // $bawaan = $request->barang_tambahan;
        // $data = DB::table('barang_tambahan_pendaftar')
        //     ->leftJoin('barang', 'barang_tambahan_pendaftar.id_barang', '=', 'barang.id')
        //     ->select('barang_tambahan_pendaftar.id as id', 'barang.nama as nama', 'barang_tambahan_pendaftar.qty as qty')
        //     ->where('barang_tambahan_pendaftar.id_pendaftar', $request->id_pendaftar)

        //     ->get();

        $data = DB::table('barang_tambahan_pendaftar')
            ->leftJoin('barang', 'barang_tambahan_pendaftar.id_barang', '=', 'barang.id')
            ->select('barang_tambahan_pendaftar.id as id', 'barang.nama as nama', 'barang_tambahan_pendaftar.qty as qty')
            ->where('barang_tambahan_pendaftar.id_pendaftar', $request->id_pendaftar)

            ->get();


        return response()->json([

            "barang" => $data,

        ]);
    }

    function barangPenghuni($id)
    {


        $data = DB::table('barang_tambahan_penghuni')
            ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
            // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
            ->select('barang_tambahan_penghuni.*', 'barang.nama as nama')
            ->where('barang_tambahan_penghuni.id_penghuni', $id)
            ->where('barang_tambahan_penghuni.tanggal_keluar', null)
            ->get();

        return response()->json([

            "barang" => $data,

        ]);
    }

    function addBarangPenghuni(Request $request)
    {

        $check_barang = DB::table('barang')->where('nama', $request->nama)->first();
        $nowtime = Carbon::now('Asia/Jakarta');
        if ($check_barang == null) {
            $new_item = new Barang();
            $new_item->nama =  $request->nama;
            $new_item->save();

            $barang_baru = new Barang_Tambahan_Penghuni();
            $barang_baru->id_penghuni = $request->id_penghuni;
            $barang_baru->id_barang = $new_item->id;
            $barang_baru->qty = $request->qty;
            $barang_baru->total = $request->total;
            $barang_baru->tanggal_masuk = $nowtime;
            // $barang_baru->active = TRUE;

            $barang_baru->save();
        } else {
            $barang_baru = new Barang_Tambahan_Penghuni();
            $barang_baru->id_penghuni = $request->id_penghuni;
            $barang_baru->id_barang = $check_barang->id;
            $barang_baru->qty = $request->qty;
            $barang_baru->total = $request->total;
            $barang_baru->tanggal_masuk = $nowtime;
            // $barang_baru->active = TRUE;

            $barang_baru->save();
        }


        return response()->json([
            "code" => 200,
            "barang" => 'Successs',

        ]);
    }

    function editBarangPenghuni(Request $request)
    {

        $check_barang = DB::table('barang')->where('nama', $request->nama)->first();
        $barang_edit = Barang_Tambahan_Penghuni::where('id', $request->id)->first();
        if ($check_barang && $barang_edit) {


            $barang_edit->id_penghuni = $request->id_penghuni;
            $barang_edit->id_barang = $request->id_barang;
            $barang_edit->qty = $request->qty;
            $barang_edit->total = $request->total;

            $barang_edit->save();
            return response()->json([
                "code" => 200,
                "barang" => 'Successs',

            ]);
        }


        return response()->json([
            "code" => 404,
            "barang" => 'Barang Tidak Ditemukan',

        ]);
    }

    function deleteBarangPenghuni(Request $request)
    {

        $check_barang = DB::table('barang')->where('nama', $request->nama)->first();
        $barang_edit = Barang_Tambahan_Penghuni::where('id', $request->id)->first();
        if ($check_barang && $barang_edit) {


            // $barang_edit->active = FALSE;
            $barang_edit->tanggal_keluar = Carbon::now('Asia/Jakarta');


            $barang_edit->save();
            return response()->json([
                "code" => 200,
                "barang" => 'Hapus Success',

            ]);
        }


        return response()->json([
            "code" => 404,
            "barang" => 'Barang Tidak Ditemukan',

        ]);
    }

    function customBarangPenghuni(Request $request)
    {
        $check_barang = DB::table('barang')->where('nama', $request->nama)->first();
        $customday = Carbon::now('Asia/Jakarta')->day($request->day)->month($request->month)->year($request->year);
        if ($check_barang == null) {
            $new_item = new Barang();
            $new_item->nama =  $request->nama;
            $new_item->save();

            $barang_baru = new Barang_Tambahan_Penghuni();
            $barang_baru->id_penghuni = $request->id_penghuni;
            $barang_baru->id_barang = $new_item->id;
            $barang_baru->qty = $request->qty;
            $barang_baru->total = $request->total;
            $barang_baru->tanggal_masuk = $customday;
            // $barang_baru->active = TRUE;

            $barang_baru->save();
        } else {
            $barang_baru = new Barang_Tambahan_Penghuni();
            $barang_baru->id_penghuni = $request->id_penghuni;
            $barang_baru->id_barang = $check_barang->id;
            $barang_baru->qty = $request->qty;
            $barang_baru->total = $request->total;
            $barang_baru->tanggal_masuk = $customday;
            // $barang_baru->active = TRUE;

            $barang_baru->save();
        }


        return response()->json([
            "code" => 200,
            "barang" => 'Successs',

        ]);
    }

    function cariBarang(Request $request)
    {
        $tglMasuk = Carbon::now('Asia/Jakarta')->day($request->day)->month($request->month)->year($request->year);
        // $tglKeluar = Carbon::now('Asia/Jakarta')->day($request->day2)->month($request->month2)->year($request->year2);
        // $data = Barang_Tambahan_Penghuni::whereDate('tanggal_masuk', '>', $tglMasuk)->whereDate('tanggal_keluar', '<', $tglMasuk)->orWhere('tanggal_keluar', null)->get();
        $data = Barang_Tambahan_Penghuni::whereDate('tanggal_masuk', '<', $tglMasuk)->whereDate('tanggal_keluar', '>', $tglMasuk)->orWhere('tanggal_keluar', null)->get();

        return response()->json([
            "code" => 200,
            "barang" => 'Successs',
            "data" => $data
        ]);
    }

    function allBarang()
    {
        $data = DB::table('barang_tambahan_penghuni')
            ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
            // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
            ->select('barang_tambahan_penghuni.*', 'barang.nama as nama')

            ->get();

        $search = 'puki';

        $tagihan = Tagihan::where('id', 1)->first();
        $converted_date = Carbon::parse($tagihan->tanggal_tagihan);
        $kurang_hari = Carbon::now('Asia/Jakarta')->addDays(3);
        $coba = DB::table('barang_tambahan_penghuni')
            ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
            // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
            ->select('barang_tambahan_penghuni.*', 'barang.nama as nama')
            ->where(function ($query) use ($converted_date) {
                $query->whereDate('barang_tambahan_penghuni.tanggal_masuk', '<=', $converted_date)
                    ->whereTime('barang_tambahan_penghuni.tanggal_masuk', '<=', $converted_date->format('H:i:s'));
            })->where(function ($query) use ($converted_date) {
                $query->where(function ($query) use ($converted_date) {
                    $query->whereDate('barang_tambahan_penghuni.tanggal_keluar', '>=', $converted_date)
                        ->whereTime('barang_tambahan_penghuni.tanggal_keluar', '>=', $converted_date->format('H:i:s'));
                })->orWhere('barang_tambahan_penghuni.tanggal_keluar', null);
            })->get();


        // ->whereDate('barang_tambahan_penghuni.tanggal_masuk', '<=', Carbon::parse($tagihan->tanggal_tagihan))
        // ->whereTime('barang_tambahan_penghuni.tanggal_masuk', '<=', Carbon::parse($tagihan->tanggal_tagihan)->format('H:i:s'))
        // ->whereDate('barang_tambahan_penghuni.tanggal_keluar', '>=', Carbon::parse($tagihan->tanggal_tagihan))
        // ->orWhere('barang_tambahan_penghuni.tanggal_keluar', null)
        // // ->whereTime('barang_tambahan_penghuni.tanggal_keluar', '>=', Carbon::parse($tagihan->tanggal_tagihan)->format('H:i:s'))
        // // ->whereTime('barang_tambahan_penghuni.tanggal_keluar', '>', Carbon::parse($tagihan->tanggal_tagihan))
        // // ->whereTime('barang_tambahan_penghuni.tanggal_masuk', '<=', Carbon::parse($tagihan->tanggal_tagihan))
        // // ->whereDate('barang_tambahan_penghuni.tanggal_keluar', '>=', $data[$x]['tanggal_tagihan'])
        // // ->where('barang_tambahan_penghuni.active', TRUE)
        // ->get();



        return response()->json([
            "code" => 200,
            "barang" => 'Successs',
            "data" => $data,
            "tagihan" => $tagihan,
            "coba" => $coba,
            "carbon" => Carbon::parse($tagihan->tanggal_tagihan)
        ]);
    }
}
