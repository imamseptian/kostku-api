<?php

namespace App\Http\Controllers;

use App\Kota;
use Illuminate\Http\Request;

class KotaController extends Controller
{
    public function getListKota($id)
    {
        $data_kota = Kota::where('province_id', $id)->get();
        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "Success get kota",
            "kota" => $data_kota,
        ]);
    }
}
