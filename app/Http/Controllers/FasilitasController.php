<?php

namespace App\Http\Controllers;

use App\Fasilitas;
use App\Kamar_Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FasilitasController extends Controller
{
    function getFasilitas($id)
    {
        $data = DB::table('fasilitas')
            ->leftJoin('kamar_fasilitas', 'fasilitas.id', '=', 'kamar_fasilitas.id_fasilitas')
            ->leftJoin('class_kamar', 'class_kamar.id', '=', 'kamar_fasilitas.id_kelas')
            ->select('kamar_fasilitas.*', 'fasilitas.nama as nama')
            ->where('class_kamar.id', $id)
            ->where('kamar_fasilitas.active', TRUE)
            ->orderBy('kamar_fasilitas.id', 'asc')
            ->get();

        return response()->json([
            "message" => "GET Method by kelas Success",
            "code" => 200,
            "success" => TRUE,
            "data" => $data
        ]);
    }

    function addKamarFasilitas(Request $request)
    {
        $check_fasilitas = Fasilitas::where('nama', $request->nama)->first();

        if ($check_fasilitas) {
            $new_kamar_fasilitas = new Kamar_Fasilitas();
            $new_kamar_fasilitas->id_fasilitas = $check_fasilitas->id;
            $new_kamar_fasilitas->id_kelas = $request->id_kelas;

            $new_kamar_fasilitas->save();
        } else {
            $new_fasilitas = new Fasilitas();
            $new_fasilitas->nama = $request->nama;
            $new_fasilitas->save();

            $new_kamar_fasilitas = new Kamar_Fasilitas();
            $new_kamar_fasilitas->id_fasilitas = $new_fasilitas->id;
            $new_kamar_fasilitas->id_kelas = $request->id_kelas;

            $new_kamar_fasilitas->save();
        }

        return response()->json([
            "message" => "Add Fasilitas Sukses",
            "code" => 200,
            "success" => TRUE
        ]);
    }


    function editFasilitas($id, Request $request)
    {
        $check_fasilitas = Fasilitas::where('nama', $request->nama)->first();
        if ($check_fasilitas) {
            $fasilitas_kamar = Kamar_Fasilitas::where('id', $id)->first();
            $fasilitas_kamar->id_fasilitas = $check_fasilitas->id;
            $fasilitas_kamar->save();
        } else {
            $new_fasilitas = new Fasilitas();
            $new_fasilitas->nama = $request->nama;
            $new_fasilitas->save();

            $fasilitas_kamar = Kamar_Fasilitas::where('id', $id)->first();
            $fasilitas_kamar->id_fasilitas = $new_fasilitas->id;
            $fasilitas_kamar->save();
        }

        return response()->json([
            "message" => "GET Method by kelas Success",
            "code" => 200,
            "success" => TRUE
        ]);
    }

    function hapusKamarFasilitas($id)
    {
        // ADD STATUS TO DB KAMAR FASILITAS
        $check_fasilitas = Kamar_Fasilitas::where('id', $id)->first();
        if ($check_fasilitas) {
            $check_fasilitas->active = FALSE;
            $check_fasilitas->save();

            return response()->json([
                "message" => "Fasilitas berhasil dihapus",
                "code" => 200,
                "success" => TRUE
            ]);
        }

        return response()->json([
            "message" => "Fasilitas tidak ditemukan",
            "code" => 404,
            "success" => FALSE
        ]);
    }
}
