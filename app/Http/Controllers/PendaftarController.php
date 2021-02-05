<?php

namespace App\Http\Controllers;

use App\Barang;
use App\Barang_Tambahan_Pendaftar;
use App\Barang_Tambahan_Penghuni;
use App\Pendaftar;
use App\Penghuni;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Http\Request;

class PendaftarController extends Controller
{

    function getPendaftar(Request $request)
    {
        $owner = $request->user();
        $mykeyword = $request->namakeyword;
        if ($request->has('sortname')) {
            $data = Pendaftar::where('id_kost', $request->id_kost)->where('active', TRUE)->where('nama', 'like', '%' . $mykeyword . '%')->orderBy($request->sortname, $request->orderby)->paginate(10);
        } else {
            $data = Pendaftar::where('id_kost', $request->id_kost)->where('active', TRUE)->orderBy('isread', 'desc')->paginate(10);
        }

        for ($x = 0; $x < count($data); $x++) {
            $data[$x]['tanggal_daftar'] = Carbon::parse($data[$x]['tanggal_daftar']);
            $data[$x]['tanggal_lahir'] = Carbon::parse($data[$x]['tanggal_lahir']);
            // $mybulan = Carbon::parse($data[$x]['tanggal_daftar'])->format('m');
            // $mybulan = $data[$x]['tanggal_daftar']->format('m');
            // $namabulan = '';
            // if ($mybulan == '01') {
            //     $namabulan = 'Januari';
            // } elseif ($mybulan == '02') {
            //     $namabulan = 'Februari';
            // } elseif ($mybulan == '03') {
            //     $namabulan = 'Maret';
            // } elseif ($mybulan == '04') {
            //     $namabulan = 'April';
            // } elseif ($mybulan == '05') {
            //     $namabulan = 'Mei';
            // } elseif ($mybulan == '06') {
            //     $namabulan = 'Juni';
            // } elseif ($mybulan == '07') {
            //     $namabulan = 'Juli';
            // } elseif ($mybulan == '08') {
            //     $namabulan = 'Agustus';
            // } elseif ($mybulan == '09') {
            //     $namabulan = 'September';
            // } elseif ($mybulan == '10') {
            //     $namabulan = 'Oktober';
            // } elseif ($mybulan == '11') {
            //     $namabulan = 'November';
            // } else {
            //     $namabulan = 'Desember';
            // }
            // // $temp_decode = json_decode($data[$x]['barang_tambahan']);

            // $data[$x]['hari'] = Carbon::parse($data[$x]['tanggal_daftar'])->format('d');
            // $data[$x]['bulan'] = $namabulan;
            // $data[$x]['tahun'] = Carbon::parse($data[$x]['tanggal_daftar'])->format('Y');
            // $data[$x]['barang_tambahan'] = json_decode($data[$x]['barang_tambahan']);
        }

        return response()->json([
            "message" => "GET Method Success",
            "data" => $data,
            'keyword' => $request->namakeyword,
            // 'ayayaa'=>$cobaaya
        ]);
    }

    // public function paginate($items, $perPage = 5, $page = null, $options = [])
    // {
    //     $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
    //     $items = $items instanceof Collection ? $items : Collection::make($items);
    //     return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    // }

    function daftar(Request $request)
    {


        // return response()->json([
        //     "message" => "GET Method Success",
        //     "data" => $kost_data->id,

        //     // 'ayayaa'=>$cobaaya
        // ]);

        $validator = Validator::make(
            $request->all(),
            [
                'nama' => 'required',
                'email' => 'required',
                'kelamin' => 'required',
                'provinsi' => 'required',
                'kota' => 'required',
                'alamat' => 'required',
                'notelp' => 'required',
                'noktp' => 'required',
                'foto_ktp' => 'required',
                'foto_diri' => 'required',
                'status_pekerjaan' => 'required',
                'status_hubungan' => 'required',
                'tempat_kerja_pendidikan' => 'required',
                'request_kamar' => 'required',
                'tanggal_lahir' => 'required|date|before:-17 years',
                'barang_tambahan.*.nama' => 'required'
            ],
            [
                'nama.required' => 'Nama perlu diisi',
                'email.required' => 'Email Perlu Diisi',
                'kelamin.required' => 'Jenis kelamin perlu diisi',
                'provinsi.required' => 'Pronvisi asal perlu diisi',
                'kota.required' => 'Kota asal perlu diisi',
                'alamat.required' => 'Alamat asal perlu diisi',
                'noktp.required' => 'Nomor KTP perlu diisi',
                'notelp.required' => 'Nomor Telepon perlu diisi',
                'foto_ktp.required' => 'Foto KTP perlu diunggah',
                'foto_diri.required' => 'Foto Diri perlu diunggah',
                'status_pekerjaan.required' => 'Status pekerjaan perlu diisi',
                'status_hubungan.required' => 'Status hubungan perlu diisi',
                'tempat_kerja_pendidikan.required' => 'Tempat kerja atau pendidikan perlu diisi',
                'request_kamar.required' => 'Silahkan pilih kamar yang ingin dihuni',
                'tanggal_lahir.required' => 'Tanggal lahir perlu diisi',
                'tanggal_lahir.before' => 'Harus berumur minimal 18 tahun untuk dapat mendaftar',
                'barang_tambahan.*.nama.required' => 'Nama barang tidak boleh kosong',
            ]
        );

        if ($validator->fails()) {
            return response()->json(["message" => "ada error", 'errors' => $validator->errors()->messages()]);
        }

        $penghuni = new Pendaftar();
        $foto_diri = '';
        $foto_ktp = '';

        if ($request->has('foto_ktp')) {
            $image_64 = $request->foto_ktp;

            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);


            $image = str_replace($replace, '', $image_64);

            $image = str_replace(' ', '+', $image);

            $imageName = Str::random(10) . '.' . $extension;

            $width = Image::make($image_64)->width();
            $height = Image::make($image_64)->height();

            $thumbnailImage = Image::make($image_64);



            if ($width < $height) {
                $thumbnailImage->resize(720, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else {
                $thumbnailImage->resize(null, 480, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $thumbnailImage->crop(720, 480);
            $thumbnailImage->stream(); // <-- Key point
            Storage::disk('local')->put('public/images/pendaftar/' . $imageName, $thumbnailImage);

            $foto_ktp = $imageName;
        }
        if ($request->has('foto_diri')) {
            $image_64 = $request->foto_diri;

            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);


            $image = str_replace($replace, '', $image_64);

            $image = str_replace(' ', '+', $image);

            $imageName = Str::random(10) . '.' . $extension;

            $width = Image::make($image_64)->width();
            $height = Image::make($image_64)->height();

            $thumbnailImage = Image::make($image_64);
            if ($width < $height) {
                $thumbnailImage->resize(512, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else {
                $thumbnailImage->resize(null, 512, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $thumbnailImage->crop(512, 512);
            $thumbnailImage->stream(); // <-- Key point
            Storage::disk('local')->put('public/images/pendaftar/' . $imageName, $thumbnailImage);
            $foto_diri = $imageName;
        }

        $kost_data = DB::table('kamars')
            ->join('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
            ->join('kosts', 'class_kamar.id_kost', '=', 'kosts.id')
            ->select('kosts.*')
            ->where('kamars.id', $request->request_kamar)

            ->first();
        $mytime = Carbon::now('Asia/Jakarta');
        // $mytime = Carbon::now('Asia/Jakarta')->subDays(9);
        $date_lahir = Carbon::parse($request->tanggal_lahir);
        $penghuni->nama = $request->nama;
        $penghuni->id_kost = $kost_data->id;
        $penghuni->kelamin = $request->kelamin;

        $penghuni->provinsi = $request->provinsi;
        $penghuni->kota = $request->kota;
        $penghuni->alamat = $request->alamat;
        $penghuni->email = $request->email;
        $penghuni->notelp = $request->notelp;
        $penghuni->noktp = $request->noktp;
        $penghuni->status_pekerjaan = $request->status_pekerjaan;
        $penghuni->status_hubungan = $request->status_hubungan;
        $penghuni->tempat_kerja_pendidikan = $request->tempat_kerja_pendidikan;
        if ($request->pesan) {

            // $penghuni->pesan = $request->pesan ? $request->isread : $pendaftar->isread;
            $penghuni->pesan = $request->pesan;
        }
        $penghuni->request_kamar = $request->request_kamar;
        $penghuni->foto_ktp = $foto_ktp;
        $penghuni->foto_diri = $foto_diri;
        $penghuni->tanggal_daftar = $mytime;
        $penghuni->tanggal_lahir = $date_lahir;



        $penghuni->save();
        $bawaan = $request->barang_tambahan;

        for ($x = 0; $x < count($bawaan); $x++) {
            $check_barang = DB::table('barang')->where('nama', $bawaan[$x]['nama'])->first();

            if ($check_barang == null) {
                $barang_baru = new Barang();
                $barang_baru->nama =  $bawaan[$x]['nama'];
                $barang_baru->save();

                $barang_tambahan = new Barang_Tambahan_Pendaftar();
                $barang_tambahan->id_pendaftar = $penghuni->id;
                $barang_tambahan->id_barang = $barang_baru->id;
                $barang_tambahan->qty = $bawaan[$x]['qty'];

                $barang_tambahan->save();
            } else {
                $barang_tambahan = new Barang_Tambahan_Pendaftar();
                $barang_tambahan->id_pendaftar = $penghuni->id;
                $barang_tambahan->id_barang = $check_barang->id;
                $barang_tambahan->qty = $bawaan[$x]['qty'];

                $barang_tambahan->save();
            }
        }

        $mytopic = "kostku-" . $kost_data->id;;
        $this->cobasend($mytopic, 'test1', 'test2', 'Seseorang Mendaftar Menjadi Penghuni Kost Anda', 'Klik Untuk Meninjau');
        return response()->json([
            "message" => "Pendaftar Berhasil Ditambahkan",
            "code" => 200,
            "success" => TRUE,
            "data" => $penghuni,
        ]);
    }

    // function bawaBarang(Request $request)
    // {
    //     $bawaan = $request->barang_tambahan;
    //     // $check_barang = DB::table('barang')->where('nama', $request->nama)->exists();
    //     // if (!$check_barang) {
    //     //     $barang_bawaan = new Barang();
    //     //     $barang_bawaan->nama = $request->nama;
    //     //     $barang_bawaan->save();
    //     // }
    //     for ($x = 0; $x < count($bawaan); $x++) {
    //         $check_barang = DB::table('barang')->where('nama', $bawaan[$x]['nama'])->first();
    //         if ($check_barang == null) {
    //             $barang_baru = new Barang();
    //             $barang_baru->nama =  $bawaan[$x]['nama'];
    //             $barang_baru->save();

    //             $barang_tambahan = new Barang_Tambahan_Pendaftar();
    //             $barang_tambahan->id_pemilik = $request->id_pemilik;
    //             $barang_tambahan->id_barang = $barang_baru->id;
    //             $barang_tambahan->qty = $bawaan[$x]['qty'];
    //             $barang_tambahan->total = $bawaan[$x]['total'];
    //             $barang_tambahan->active = FALSE;
    //             $barang_tambahan->save();
    //         } else {
    //             $barang_tambahan = new Barang_Tambahan_Pendaftar();
    //             $barang_tambahan->id_pemilik = $request->id_pemilik;
    //             $barang_tambahan->id_barang = $check_barang->id;
    //             $barang_tambahan->qty = $bawaan[$x]['qty'];
    //             $barang_tambahan->total = $bawaan[$x]['total'];
    //             $barang_tambahan->active = FALSE;
    //             $barang_tambahan->save();
    //         }
    //     }


    //     return response()->json([
    //         "message" => "calon penghuni Berhasil Ditambahkan",

    //     ]);
    // }

    function allBarang(Request $request)
    {
        // $bawaan = $request->barang_tambahan;
        $data = Barang::all();
        $barang_pendaftar = Barang_Tambahan_Pendaftar::all();
        $barang_penghuni = Barang_Tambahan_Penghuni::all();
        $data_pendaftar = Pendaftar::all();
        $data_penghuni = Penghuni::all();


        return response()->json([

            "barang" => $data,
            "barang_pendaftar" => $barang_pendaftar,
            "barang_penghuni" => $barang_penghuni,
            "calon" => $data_pendaftar,
            "penghuni" => $data_penghuni
        ]);
    }

    public function editPendaftar(Request $request, $id)
    {
        $pendaftar = Pendaftar::where('id', $id)->first();

        if ($pendaftar) {
            $pendaftar->isread = $request->isread ? $request->isread : $pendaftar->isread;
            $pendaftar->active = $request->active ? $request->active : $pendaftar->active;

            $pendaftar->save();

            return response()->json([
                "message" => "Put Successs ",
                "data" => $pendaftar,
            ]);
        }


        return response()->json([
            "message" => "Pendaftar dengan id " . $id . " Tidak Ditemukan"
        ], 400);
    }

    function cobapost(Request $request)
    {

        return response()->json([
            "respond" => 'method berhasil'
        ]);
    }

    function cobasend($topic, $stack, $screen, $title, $body)
    {

        // $response = Http::withToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYjIwYTBhZmNlMGI5ZmMxNjljNWZkNmE4OTVlMzFlMDFmMzlhMGRkZGZkZmQ4OTFhMzYwM2FhNTYwZWQ5Y2E4OWIzOWUxMTMwMGFiZjJmZmEiLCJpYXQiOjE2MDA0MTg1MjYsIm5iZiI6MTYwMDQxODUyNiwiZXhwIjoxNjMxOTU0NTI2LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.iitRPFuBuWwM-Vm6f12u4zxtnjn6Cus0zC8YCoo-f58-ZpWWevK7vhaUvn1xg5bWEK4YjL3UbE-Qt-llxyicXTq3kkfkrXE14mxX3U8b9RIFB6yVyFu_T4x9JST1UVTvzMadzScdk_iF30TSFrmiI_FzkdopEzus6CmuUg2K_INwWNNZYj6OcODWr3dk5L2sxW7tvkzaodrPZPhD1aaWvq9PI2LcMnousM9grIeF64AdbwnC69eSu60izpG9STLq5F0FSV5Q9nQUjiMzAf8ieKSxJsAL2Mq9FpY50Ft9UNLpa2qBXgTELOOERdG_mWXkHvqm4GSxgnWRXO_h8xKs7tY_kVgk6SHYbhz2RTswSyJqxg41WmbM-Sv4j4EDRDvrEMV6N7hnwyvVqF2a8RBOzuI-0IaA89etrLtL6oM3oCo-KFuqz5DsEHOSJKt--RAkxmIMnzEga6xULyF70GD7lLPt1KZTvQQD4iFj6U1zsY0eOz-4zWDCDW19Ed3SNeiOpSJ-ZEahJEJZOQc6YLA3uh7VVpsn6uXeAcPV4vm6Qajw97D6QbcrGQDDnQ-CaTswLwwwAZ7pO1r2AcdhFaFi69nRulp0Ooug6udsz7nR4u6V-i6xSXGh5CC87nqukA-5QP1J08TW6jierk-B1K74u-ZLvYsFuISmmzGZh_RL_88')->post('https://dry-forest-53707.herokuapp.com/api/cobapost', [
        //     'name' => 'Steve',
        // ]);
        // $to='eV92VGkBRkmgbSey6out2A:APA91bHCcYeotyeSKZ-UZpr6KSGneT6d51SELx-c7u9UtQOk_VebtxJfhVb-AnqTvA6XKIxNTeiL_xUKVpNEEP62J9Slo145lwdNWpKJBCUkcqByENk2UcbjSAO5gwmd_u_vXOp19zt2';
        // $to='/topics/'.$request->topic;
        $to = '/topics/' . $topic;


        $notif = array(
            'title' => $title,
            'body' => $body,
        );

        // $data = array(
        //     'stack'=>$request->stack,
        //     'screen'=>$request->screen,
        // );

        $data = array(
            'stack' => $stack,
            'screen' => $screen,
        );

        // $kondisi = array(
        //     'condition'=>"'kostku-11' in topics ",
        //     'data'=>$data,
        // );

        $sendNotif = $this->sendNotification($to, $notif, $data);
        // $sendNotif = $this->sendNotification($notif,$kondisi);

        // return response()->json([
        //     "respond"=>'berhasil',
        //     'hasil'=>$sendNotif,
        //     "topic"=>"'".$request->topic."' in topics"

        // ]);
    }

    public function sendNotification($to = '', $notif = array(), $data = array())
    {
        $fields = array('to' => $to, 'notification' => $notif, 'data' => $data);
        // $data = json_encode($json_data);
        //FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key = 'AAAA8xTtYLY:APA91bGBMemsYq9dHBuJDHSxajLLeljIoC2hFLEIggcozlNFk5w9Cdc25K0hFMgbVfAyUGLr6P6Zjtfye1VGavnUQc81ivvEjSI2MYtPeRt6suIccraFBHd2f-35dIeFBw94_grF6-oT';
        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $server_key
        );
        //CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    // public function sendNotification($notif = array(),$data = array()){
    //     $fields = array('notification'=>$notif,'data'=>$data);
    //     // $data = json_encode($json_data);
    //     //FCM API end-point
    //     $url = 'https://fcm.googleapis.com/fcm/send';
    //     //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
    //     $server_key = 'AAAA8xTtYLY:APA91bGBMemsYq9dHBuJDHSxajLLeljIoC2hFLEIggcozlNFk5w9Cdc25K0hFMgbVfAyUGLr6P6Zjtfye1VGavnUQc81ivvEjSI2MYtPeRt6suIccraFBHd2f-35dIeFBw94_grF6-oT';
    //     //header with content_type api key
    //     $headers = array(
    //         'Content-Type:application/json',
    //         'Authorization:key='.$server_key
    //     );
    //     //CURL request to route notification to FCM connection server (provided by Google)
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    //     $result = curl_exec($ch);
    //     curl_close($ch);
    //     return json_decode($result,true);
    // }



}
