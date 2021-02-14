<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kost;
use App\ClassKamar;
use App\Fasilitas;
use App\User;
use App\Penghuni;
use App\Kamar;
use App\Tagihan;
use App\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;

class KostController extends Controller
{
    function filterKost(Request $request)
    {
        $data = DB::table('kosts')
            ->leftJoin('provinces', 'kosts.provinsi', '=', 'provinces.id')
            ->leftJoin('regencies', 'kosts.kota', '=', 'regencies.id')
            ->where('kosts.nama', 'like', '%' . $request->keyword . '%')
            ->select('kosts.*', 'provinces.name as nama_provinsi', 'regencies.name as nama_kota');


        if ($request->provinsi != 0) {
            $data = $data->where('provinsi', $request->provinsi);
        }

        if ($request->provinsi != 0) {
            $data = $data->where('kota', $request->kota);
        }

        if ($request->provinsi != 0) {
            $data = $data->where('jenis', $request->jenis);
        }

        $data = $data->get();
        // $data = $data->where('provinsi', 33);

        // $data = $data->where('jenis', 1)->get();



        return response()->json([
            "message" => "GET Method Success",
            "data" => $data,
            "provinsi" => $request->provinsi,
            "kota" => $request->kota,
            "jenis" => $request->jenis,
        ]);
    }

    function getKelasKost($id)
    {

        $kost = Kost::where('id', $id)->first();
        if ($kost != null) {
            $data_kelas = ClassKamar::where('owner', $id)->where('active', TRUE)->get();
            return response()->json([
                "status" => "success",
                "message" => "Kost ditemukan",
                "kost" => $kost,
                "kelas" => $data_kelas
            ]);
        }
        return response()->json([
            "status" => "failed",
            "message" => "Kost tidak ditemukan",
        ]);
    }

    function getKamarKost($id)
    {
        $data_kamar = Kamar::where('kelas', $id)->where('active', TRUE)->get();
        return response()->json([
            "message" => "GET Method Success",
            "kamar" => $data_kamar
        ]);
    }

    function cobaKamar($id)
    {
        // $ayaya = DB::table('kamars')
        // ->selectRaw("kamars.id ,kamars.nama,penghuni.nama_depan,kamars.kapasitas,COUNT(penghuni.id) as jml_penghuni")
        // ->leftJoin('penghuni', 'kamars.id', '=', 'penghuni.kamar')
        // ->groupBy("kamars.id")
        // ->where("kamars.kelas",$id)
        // ->get();
        $data_kelas = ClassKamar::where('id', $id)->first();


        $ayaya = DB::table('kamars')
            ->selectRaw("kamars.id ,COUNT(penghuni.id) as jml_penghuni")
            ->leftJoin('penghuni', 'kamars.id', '=', 'penghuni.kamar')
            ->groupBy("kamars.id")
            ->where("kamars.kelas", $id)
            ->get();

        // $myarr = $ayaya->pluck('id')->toArray();
        $myarr = $ayaya->where('jml_penghuni', '<', $data_kelas->kapasitas)->pluck('id')->toArray();
        $dataKamar = Kamar::whereIn('id', $myarr)->get();
        // ->pluck('id')->toArray();
        // $covert_json = json_decode($ayaya);

        // $myarr = [];
        // for ($x = 0; $x < count($covert_json); $x++){
        //     array_push($myarr,$covert_json[$x]['id']);
        // }

        // $ayaya = DB::select('SELECT kamars.id,kamars.nama,jml_penghuni FROM (SELECT kamars.id ,kamars.nama,penghuni.nama_depan,kamars.kapasitas,COUNT(penghuni.id) as jml_penghuni FROM kamars LEFT JOIN penghuni ON kamars.id = penghuni.kamar GROUP BY kamars.id  ) ')


        return response()->json([
            "message" => "GET Method Success",
            "kamar_tersedia" => $dataKamar,
            "dataku" => $ayaya,
            "myarray" => $myarr
        ]);
    }



    function getKost($id)
    {
        $kost = Kost::where('id', $id)->first();

        if ($kost) {
            $owner = User::where('id', $kost->owner)->first();
            $kelas = ClassKamar::where('id_kost', $id)->get();
            // $coba =  ClassKamar::select('class_kamar.*', DB::raw('count(kelamin) quantity'))->orderByDesc('quantity')->groupBy('kelamin')->get();
            // foreach ($kelas as &$value) {
            //     // $value->jml_kamar = count(Kamar::where('id_kelas', $value->id));
            // $value->jml_kamar = count(Kamar::where('id_kelas', $value->id)->get());
            // $value->fasilitas =  DB::table('class_kamar')
            //     ->rightJoin('kamar_fasilitas', 'class_kamar.id', '=', 'kamar_fasilitas.id_kelas')
            //     ->rightJoin('fasilitas', 'kamar_fasilitas.id', '=', 'fasilitas.id')
            //     ->select('kamar_fasilitas.*', 'fasilitas.nama as nama')
            //     ->where('class_kamar.id', $value->id)
            //     ->where('kamar_fasilitas.active', TRUE)
            //     ->orderBy('kamar_fasilitas.id', 'asc')
            //     ->get();
            // }
            for ($x = 0; $x < count($kelas); $x++) {
                $kelas[$x]->jml_kamar = count(Kamar::where('id_kelas', $kelas[$x]->id)->get());
                $kelas[$x]->fasilitas =  DB::table('class_kamar')
                    ->rightJoin('kamar_fasilitas', 'class_kamar.id', '=', 'kamar_fasilitas.id_kelas')
                    ->rightJoin('fasilitas', 'kamar_fasilitas.id_fasilitas', '=', 'fasilitas.id')
                    ->select('kamar_fasilitas.*', 'fasilitas.nama as nama')
                    ->where('class_kamar.id', $kelas[$x]->id)
                    ->where('kamar_fasilitas.active', TRUE)
                    ->orderBy('kamar_fasilitas.id', 'asc')
                    ->get();
            }


            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "kost" => $kost,
                "owner" => $owner,
                "kelas" => $kelas,
            ]);
        }
        return response()->json([
            "code" => 404,
            "success" => FALSE,
            "message" => "Kost Tidak ditemukan",
        ]);
    }

    function allKamarkost()
    {
        $data = Kamar::all();
        return response()->json([
            "message" => "GET Method Success",
            "dataku" => $data
        ]);
    }

    function getById($id)
    {
        $data = Kost::where('id', $id)->get();

        return response()->json([
            "message" => "GET Method Success",
            "data" => $data
        ]);
    }

    function checkFirstTime(Request $request)
    {
        $user = $request->user();
        $data = Kost::where('owner', $user['id'])->get();
        return response()->json([
            "message" => "First Method Success",
            "data" => count($data),
            "usmer" => $request->user()
        ]);
    }

    function homeScreen($id, Request $request)
    // function homeScreen($id)
    {
        $nowtime = Carbon::now('Asia/Jakarta');


        $uang = DB::table('transaksi')
            ->where('id_kost', $id)
            ->whereMonth('tanggal_transaksi', $nowtime->format('m'))
            ->whereYear('tanggal_transaksi', $nowtime->format('Y'))
            ->where('jenis', 1)
            ->sum('jumlah');
        // $data_penghuni = Penghuni::where('id_kost', $id)->orderBy('tagihan', 'desc')->limit(10)->get();
        $data_penghuni = Penghuni::where('id_kost', $id)->orderBy('nama', 'asc')->limit(10)->get();
        for ($x = 0; $x < count($data_penghuni); $x++) {
            $data_penghuni[$x]['tanggal_masuk'] = Carbon::parse($data_penghuni[$x]['tanggal_masuk']);
            $data_penghuni[$x]['tanggal_lahir'] = Carbon::parse($data_penghuni[$x]['tanggal_lahir']);
        }


        $data_transaksi = DB::table('transaksi')
            ->leftJoin('tagihan', 'transaksi.id_tagihan', '=', 'tagihan.id')
            ->leftJoin('penghuni', 'tagihan.id_penghuni', '=', 'penghuni.id')
            ->leftJoin('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
            ->select('transaksi.*', 'penghuni.nama as nama_penghuni', 'kamars.nama as nama_kamar')
            ->where('transaksi.id_kost', $id)
            // ->where('transaksi.jenis', 2)
            ->orderBy('transaksi.tanggal_transaksi', 'desc')
            ->limit(10)
            ->get();

        for ($x = 0; $x < count($data_transaksi); $x++) {

            $data_transaksi[$x]->tanggal_transaksi = Carbon::parse($data_transaksi[$x]->tanggal_transaksi);
        }
        // for ($x = 0; $x < count($data_penghuni); $x++) {
        //     $mybulan = Carbon::parse($data_penghuni[$x]['tanggal_masuk'])->format('m');
        //     // $mybulan = $data_penghuni[$x]['tanggal_daftar']->format('m');
        //     $namabulan = '';
        //     if ($mybulan == '01') {
        //         $namabulan = 'Januari';
        //     } elseif ($mybulan == '02') {
        //         $namabulan = 'Februari';
        //     } elseif ($mybulan == '03') {
        //         $namabulan = 'Maret';
        //     } elseif ($mybulan == '04') {
        //         $namabulan = 'April';
        //     } elseif ($mybulan == '05') {
        //         $namabulan = 'Mei';
        //     } elseif ($mybulan == '06') {
        //         $namabulan = 'Juni';
        //     } elseif ($mybulan == '07') {
        //         $namabulan = 'Juli';
        //     } elseif ($mybulan == '08') {
        //         $namabulan = 'Agustus';
        //     } elseif ($mybulan == '09') {
        //         $namabulan = 'September';
        //     } elseif ($mybulan == '10') {
        //         $namabulan = 'Oktober';
        //     } elseif ($mybulan == '11') {
        //         $namabulan = 'November';
        //     } else {
        //         $namabulan = 'Desember';
        //     }
        //     // $data_penghuni[$x]['tagihan'] = Tagihan::where('id_penghuni', $data_penghuni[$x]['id'])->get();
        //     $data_penghuni[$x]['hari'] = Carbon::parse($data_penghuni[$x]['tanggal_masuk'])->format('d');
        //     $data_penghuni[$x]['bulan'] = $namabulan;
        //     $data_penghuni[$x]['tahun'] = Carbon::parse($data_penghuni[$x]['tanggal_masuk'])->format('Y');
        // }


        // $data_kamar = ClassKamar::where('id_kost', $id)->orderBy('nama', 'asc')->limit(5)->get();

        $data_kamar = ClassKamar::where('id_kost', $id)->where('active', TRUE)->orderBy('nama', 'asc')->limit(5)->get();
        // $data1 = $data_kamar;
        // for ($x = 0; $x < count($data_kamar); $x++) {
        //     $data1[$x]['fasilitas'] = json_decode($data_kamar[$x]['fasilitas']);
        //     $data1[$x]['banyak'] = count(Kamar::where('kelas', $data1[$x]['id'])->get());
        // }


        // $data = DB::table('transaksi')
        //     ->leftJoin('penghuni', 'transaksi.id_penghuni', '=', 'penghuni.id')
        //     ->leftJoin('kamars', 'penghuni.kamar', '=', 'kamars.id')
        //     ->select('transaksi.*', 'penghuni.nama_depan as nama_depan', 'penghuni.nama_belakang as nama_belakang', 'kamars.nama as nama_kamar')
        //     ->where('transaksi.id_kost', $id)
        //     ->orderBy('transaksi.tanggal_transaksi', 'desc')
        //     ->limit(10)
        //     ->get();

        // for ($x = 0; $x < count($data); $x++) {
        //     $mybulan = Carbon::parse($data[$x]->tanggal_transaksi)->format('m');
        //     // $mybulan = $data[$x]['tanggal_daftar']->format('m');
        //     $namabulan = '';
        //     if ($mybulan == '01') {
        //         $namabulan = 'Januari';
        //     } elseif ($mybulan == '02') {
        //         $namabulan = 'Februari';
        //     } elseif ($mybulan == '03') {
        //         $namabulan = 'Maret';
        //     } elseif ($mybulan == '04') {
        //         $namabulan = 'April';
        //     } elseif ($mybulan == '05') {
        //         $namabulan = 'Mei';
        //     } elseif ($mybulan == '06') {
        //         $namabulan = 'Juni';
        //     } elseif ($mybulan == '07') {
        //         $namabulan = 'Juli';
        //     } elseif ($mybulan == '08') {
        //         $namabulan = 'Agustus';
        //     } elseif ($mybulan == '09') {
        //         $namabulan = 'September';
        //     } elseif ($mybulan == '10') {
        //         $namabulan = 'Oktober';
        //     } elseif ($mybulan == '11') {
        //         $namabulan = 'November';
        //     } else {
        //         $namabulan = 'Desember';
        //     }

        //     $data[$x]->hari = Carbon::parse($data[$x]->tanggal_transaksi)->format('d');
        //     $data[$x]->bulan = $namabulan;
        //     $data[$x]->tahun = Carbon::parse($data[$x]->tanggal_transaksi)->format('Y');
        // }

        return response()->json([
            "message" => "First Method Success",
            "data_penghuni" => $data_penghuni,
            "data_kamar" => $data_kamar,
            // "data_kamar" => $data1,
            "uang" => $uang,
            "transaksi" => $data_transaksi,
            // "usmer" => $request->user()
        ]);
    }

    function checkExist($id, Request $request)
    {
        $kost = Kost::where('id', $id)->first();
        if ($kost != null) {
            return response()->json([
                "status" => "exist",
                "kost" => $kost
            ]);
        }
        return response()->json([
            "status" => "not exist",
        ]);
    }

    // DAFTAR DATA KOST SAAT AWAL
    function post(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'nama' => 'required',
                'provinsi' => 'required',
                'kota' => 'required',
                'alamat' => 'required',
                'notelp' => 'required',
                'deskripsi' => 'required',
                'owner' => 'required',
                'jenis' => 'required',
            ],
            [
                'nama.required' => 'Nama Kost perlu diisi',
                'provinsi.required' => 'Provinsi perlu diisi',
                'kota.required' => 'Kota perlu diisi',
                'alamat.required' => 'Alamat tempat Kost perlu diisi',
                'notelp.required' => 'No telepon kost perlu diisi',
                'deskripsi.required' => 'Deskripsi kost perlu diisi',
                'owner.required' => 'Pemilik harus diisi',
                'jenis.required' => 'Jenis kost perlu diisi',

            ]
        );


        if ($validator->fails()) {
            return response()->json(["code" => 400, "success" => FALSE, "message" => "ada error", 'errors' => $validator->errors()->messages()]);
        }

        $user = $request->user();

        if (request()->has('foto_kost')) {
            $image_64 = $request->foto_kost; //your base64 encoded data

            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);

            $image = str_replace($replace, '', $image_64);

            $image = str_replace(' ', '+', $image);

            $imageName = Str::random(10) . '.' . $extension;

            $thumbnailImage = Image::make($image_64);
            $thumbnailImage->stream(); // <-- Key point
            Storage::disk('local')->put('public/images/kost/' . $imageName, $thumbnailImage);


            $kost = new Kost();
            $kost->nama = $request->nama;
            $kost->provinsi = $request->provinsi;
            $kost->kota = $request->kota;
            $kost->alamat = $request->alamat;
            $kost->notelp = "+62" . $request->notelp;
            $kost->foto_kost = $imageName;
            $kost->deskripsi = $request->deskripsi;
            $kost->owner = $request->owner;
            $kost->jenis = $request->jenis;

            $kost->save();
            $user['kostku'] = $kost['id'];
            $user['namakost'] = $kost['nama'];
            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "message" => "Success create foto",
                "data" => $kost,
                "user" => $user,
            ]);
        }

        $kost = new Kost();
        $kost->nama = $request->nama;
        $kost->provinsi = $request->provinsi;
        $kost->kota = $request->kota;
        $kost->alamat = $request->alamat;
        $kost->notelp = "+62" . $request->notelp;
        $kost->deskripsi = $request->deskripsi;
        $kost->owner = $request->owner;
        $kost->jenis = $request->jenis;

        $kost->save();
        $user['kostku'] = $kost['id'];
        $user['namakost'] = $kost['nama'];
        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "Success create tanpa foto",
            "data" => $kost,
            "user" => $user
        ]);
    }
    function put($id, Request $request)
    {

        $kost = Kost::where('id', $id)->first();

        if ($kost) {
            $kost->nama = $request->nama ? $request->nama : $kost->nama;
            $kost->provinsi = $request->provinsi ? $request->provinsi : $kost->provinsi;
            $kost->kota = $request->kota ? $request->kota : $kost->kota;
            $kost->alamat = $request->alamat ? $request->alamat : $kost->alamat;
            $kost->notelp = $request->notelp ? $request->notelp : $kost->notelp;
            $kost->jmlkamar = $request->jmlkamar ? $request->jmlkamar : $kost->jmlkamar;
            $kost->deskripsi = $request->deskripsi ? $request->deskripsi : $kost->deskripsi;
            $kost->owner = $request->owner ? $request->owner : $kost->owner;
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

        $kost = Kost::where('id', $id)->first();
        if ($kost) {
            $kost->delete();
            return response()->json([
                "message" => "Delete kost dengan id " . $id . " Berhasil"
            ]);
        }

        return response()->json([
            "message" => "Delete Kost dengan id " . $id . " Tidak Ditemukan"
        ], 400);
    }

    function editKost(Request $request)
    {
        $user = $request->user();
        $kost = Kost::where('owner', $user['id'])->first();
        if ($kost) {
            $kost->nama = $request->nama ? $request->nama : $kost->nama;
            $kost->provinsi = $request->provinsi ? $request->provinsi : $kost->provinsi;
            $kost->kota = $request->kota ? $request->kota : $kost->kota;
            $kost->alamat = $request->alamat ? $request->alamat : $kost->alamat;
            $kost->notelp = $request->notelp ? $request->notelp : $kost->notelp;
            $kost->deskripsi = $request->deskripsi ? $request->deskripsi : $kost->deskripsi;
            $imageName = $request->foto_kost;
            if ($request->has('newImg')) {
                $image_64 = $request->newImg; //your base64 encoded data

                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);

                //   find substring fro replace here eg: data:image/png;base64,

                $image = str_replace($replace, '', $image_64);

                $image = str_replace(' ', '+', $image);

                $imageName = Str::random(10) . '.' . $extension;

                $thumbnailImage = Image::make($image_64);
                $thumbnailImage->stream(); // <-- Key point
                Storage::disk('local')->delete('public/images/kost/' . $kost->foto_kost);
                Storage::disk('local')->put('public/images/kost/' . $imageName, $thumbnailImage);
            }
            $kost->foto_kost = $imageName;
            $kost->save();

            $user['kostku'] = $kost->id;
            $user['namakost'] = $kost->nama;
            // $data_user->foto_profil = $imageName;
            // $data_user->save();

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Berhasil',
                'foto_baru' => $imageName,
                'user' => $user,
                'kost' => $kost,

            ]);
        }

        // $data_user->save();

        return response()->json([
            'code' => 404,
            'success' => FALSE,
            'message' => 'Kost tidak ada',
        ]);
        // return response()->json($request->user());
        // return response()->json($user);
    }
}
