<?php

namespace App\Http\Controllers;

use App\Provinsi;
use Illuminate\Http\Request;

class ProvinsiController extends Controller
{
    public function getListProvinsi()
    {
        $data_provinsi = Provinsi::all();
        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "Success get provinsi",
            "provinsi" => $data_provinsi,
        ]);
    }
}
