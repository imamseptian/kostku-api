<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ClassKamar;
use App\Fasilitas;
use App\Kamar;
use App\Kamar_Fasilitas;
use App\Penghuni;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ClassKamarController extends Controller
{
    // function get(Request $request){
    //     $dataUser = $request->user();
    //     $data = ClassKamar::where('owner',$dataUser['id'])->get();
    //     $data1=$data;


    //     for ($x = 0; $x < count($data); $x++) {
    //         $data1[$x]['fasilitas'] = json_decode($data[$x]['fasilitas']);
    //         $data1[$x]['banyak']= count(Kamar::where('kelas',$data1[$x]['id'])->get());
    //     }

    //     return response()->json([
    //         "message"=>"GET Method Success",
    //         "kamar"=>$data1,
    //         "uri"=>URL::to('/'),
    //         'user'=>$dataUser['id'],
    //     ]);
    // }

    function getAllKelas()
    {
        $data = ClassKamar::all();
        return response()->json([
            "message" => "Method Success",
            "data" => $data

        ]);
    }

    function get(Request $request)
    {
        $dataUser = $request->user();
        // $data = ClassKamar::where('owner',$dataUser['id'])->paginate(10);
        $data = ClassKamar::where('id_kost', $request->id_kost)->where('active', TRUE)->where('nama', 'like', '%' . $request->namakeyword . '%')->orderBy($request->sortname, $request->orderby)->paginate(10);



        // for ($x = 0; $x < count($data); $x++) {
        //     // $dataku =json_decode($data[$x]['fasilitas']);
        //     // $myarr1 = array(
        //     //     'item'=>'ayaya',
        //     // );
        //     // array_push($dataku,$myarr1);
        //     // $banyak_fasilitas = count($dataku);
        //     $data[$x]['fasilitas'] =  DB::table('fasilitas')
        //         ->leftJoin('kamar_fasilitas', 'fasilitas.id', '=', 'kamar_fasilitas.id_fasilitas')
        //         ->leftJoin('class_kamar', 'class_kamar.id', '=', 'kamar_fasilitas.id_kelas')
        //         ->select('kamar_fasilitas.*', 'fasilitas.nama as nama')
        //         ->where('class_kamar.id', $data[$x]['id'])
        //         ->orderBy('kamar_fasilitas.id', 'asc')
        //         ->where('kamar_fasilitas.active', TRUE)
        //         ->get();

        //     // // $data1[$x]['modif_fasilitas']= $dataku;
        //     // // $data1[$x]['banyak_fasilitas']= $banyak_fasilitas;
        //     // $data1[$x]['banyak'] = count(Kamar::where('kelas', $data1[$x]['id'])->get());
        //     // $data1[$x]['penghuni'] = count(DB::table('penghuni')
        //     //     ->leftJoin('kamars', 'penghuni.kamar', '=', 'kamars.id')
        //     //     ->leftJoin('class_kamar', 'kamars.kelas', '=', 'class_kamar.id')
        //     //     ->select('penghuni.*', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
        //     //     ->where('class_kamar.id', $data1[$x]['id'])
        //     //     ->get());
        // }

        return response()->json([
            "message" => "Method Success",
            "data" => $data,
            // "uri" => URL::to('/'),
            // 'user' => $dataUser['id'],
        ]);
    }

    function infoKamar($id)
    {
        $kamar = ClassKamar::where("id", $id)->first();

        if ($kamar) {

            $kamar->fasilitas = DB::table('fasilitas')
                ->leftJoin('kamar_fasilitas', 'fasilitas.id', '=', 'kamar_fasilitas.id_fasilitas')
                ->leftJoin('class_kamar', 'class_kamar.id', '=', 'kamar_fasilitas.id_kelas')
                ->select('kamar_fasilitas.*', 'fasilitas.nama as nama')
                ->where('class_kamar.id', $id)
                ->where('kamar_fasilitas.active', TRUE)
                ->orderBy('kamar_fasilitas.id', 'asc')
                ->get();
            $kamar_tersedia = DB::table('kamars')
                ->selectRaw("kamars.id ,COUNT(penghuni.id) as jml_penghuni")
                ->leftJoin('penghuni', 'kamars.id', '=', 'penghuni.id_kamar')
                ->groupBy("kamars.id")
                ->where("kamars.id_kelas", $id)
                ->get();

            // $myarr = $kamar_tersedia->pluck('id')->toArray();
            $myarr = $kamar_tersedia->where('jml_penghuni', '<', $kamar->kapasitas)->pluck('id')->toArray();
            $dataKamar = Kamar::whereIn('id', $myarr)->get();

            foreach ($dataKamar as &$value) {
                // $value->jml_kamar = count(Kamar::where('id_kelas', $value->id));
                $value->current_penghuni = count(Penghuni::where('id_kamar', $value->id)->get());
            }

            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "kamar" => $kamar,
                "kamar_tersedia" => $dataKamar
            ]);
        }
        return response()->json([
            "code" => 404,
            "success" => FALSE,
            "message" => "Kamar Tidak ditemukan",
        ]);
    }

    function getById($id, Request $request)
    {
        $dataUser = $request->user();
        if ($dataUser['id'] == $id) {
            $data = ClassKamar::where('id', $id)->get();
            return response()->json([
                "message" => "GET Method by Id Success",
                "kamar" => $data
            ]);
        }
        return response()->json([
            "message" => "Maaf ini bukan kost milik anda",
            "kamar" => []
        ]);
    }


    function post(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nama' => 'required|min:6',
                'harga' => 'required|numeric|min:1',
                'kapasitas' => 'required|numeric|min:1',
                'id_kost' => 'required',
                'deskripsi' => 'required',
                'foto' => 'required',
                'fasilitas.*.nama' => 'required'
            ],
            [
                'nama.required' => 'Nama wajib diisi',
                'nama.min' => 'Nama Minimal 6 Digit',
                'harga.required' => 'Harga Perlu Diisi',
                'harga.min' => 'Harga tidak bisa bernilai 0',
                'harga.numeric' => 'Harga harus beformat angka',
                'kapasitas.required' => 'Kapasitas harus diisi',
                'kapasitas.numeric' => 'Kapasitas harus berformat angka',
                'kapasitas.min' => 'Kapasitas minimal 1 orang',
                'deskripsi.required' => 'Deskripsi perlu diisi',
                'foto.required' => 'Foto perlu diupload',
                'id_kost.required' => 'id_kost perlu diisi',
                'fasilitas.*.nama.required' => 'Fasilitas tidak boleh kosong',
            ]
        );

        if ($validator->fails()) {
            return response()->json(["code" => 400, "success" => FALSE, "message" => "ada error", 'errors' => $validator->errors()->messages()]);
        }



        $data_fasilitas = $request->fasilitas;

        if (request()->has('foto')) {
            $image_64 = $request->foto; //your base64 encoded data

            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);



            $image = str_replace($replace, '', $image_64);

            $image = str_replace(' ', '+', $image);

            $imageName = Str::random(10) . '.' . $extension;



            $thumbnailImage = Image::make($image_64);

            $thumbnailImage->stream(); // <-- Key point
            Storage::disk('local')->put('public/images/kelas/' . $imageName, $thumbnailImage);


            $class_kamar = new ClassKamar();
            $class_kamar->nama = $request->nama;
            $class_kamar->harga = $request->harga;
            $class_kamar->kapasitas =  $request->kapasitas;
            $class_kamar->deskripsi =  $request->deskripsi;
            $class_kamar->id_kost =  $request->id_kost;
            $class_kamar->foto = $imageName;

            // "data"=>$class_kamar,
            $class_kamar->save();
            for ($x = 0; $x < count($data_fasilitas); $x++) {
                $check_fasilitas = Fasilitas::where('nama', $data_fasilitas[$x]['nama'])->first();
                if (!$check_fasilitas) {
                    $new_fasilitas = new Fasilitas();
                    $new_fasilitas->nama = $data_fasilitas[$x]['nama'];
                    $new_fasilitas->save();

                    $new_kamar_fasiltias = new Kamar_Fasilitas();
                    $new_kamar_fasiltias->id_kelas = $class_kamar->id;
                    $new_kamar_fasiltias->id_fasilitas = $new_fasilitas->id;
                    $new_kamar_fasiltias->save();
                } else {
                    $new_kamar_fasiltias = new Kamar_Fasilitas();
                    $new_kamar_fasiltias->id_kelas = $class_kamar->id;
                    $new_kamar_fasiltias->id_fasilitas = $check_fasilitas->id;
                    $new_kamar_fasiltias->save();
                }
            }

            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "message" => "Class Kamar dengan Foto Berhasil Ditambahkan",
                // "urlgambar"=>asset('images/aayaya.jpg'),
                // "img"=>"ODADING MANG OLEH",
                "link" => asset('image_kelas/' . $imageName),
                "data" => $class_kamar,
                // "uri"=>URL::to('/'),
                // "panjang"=>$height,
                // "lebar"=>$width

            ]);
        }

        $class_kamar = new ClassKamar();
        $class_kamar->nama = $request->nama;
        $class_kamar->harga = $request->harga;
        $class_kamar->kapasitas =  $request->kapasitas;
        $class_kamar->deskripsi =  $request->deskripsi;
        $class_kamar->id_kost =  $request->id_kost;

        // "data"=>$class_kamar,
        $class_kamar->save();

        for ($x = 0; $x < count($data_fasilitas); $x++) {
            $check_fasilitas = Fasilitas::where('nama', $data_fasilitas[$x]['nama'])->first();
            if (!$check_fasilitas) {
                $new_fasilitas = new Fasilitas();
                $new_fasilitas->nama = $data_fasilitas[$x]['nama'];
                $new_fasilitas->save();

                $new_kamar_fasiltias = new Kamar_Fasilitas();
                $new_kamar_fasiltias->id_kelas = $class_kamar->id;
                $new_kamar_fasiltias->id_fasilitas = $new_fasilitas->id;
                $new_kamar_fasiltias->save();
            } else {
                $new_kamar_fasiltias = new Kamar_Fasilitas();
                $new_kamar_fasiltias->id_kelas = $class_kamar->id;
                $new_kamar_fasiltias->id_fasilitas = $check_fasilitas->id;
                $new_kamar_fasiltias->save();
            }
        }

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "Class Kamar tanpa foto Berhasil Ditambahkan",
            // "urlgambar"=>asset('images/aayaya.jpg'),
            // "img"=>"ODADING MANG OLEH",
            // "data" => $class_kamar,
            // "uri"=>URL::to('/'),
            // "panjang"=>$height,
            // "lebar"=>$width

        ]);
    }


    function put($id, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nama' => 'required|min:6',
                'harga' => 'required|numeric|min:1',
                'kapasitas' => 'required|numeric|min:1',
                'id_kost' => 'required',
                'deskripsi' => 'required',
                // 'fasilitas.*.nama' => 'required'
            ],
            [
                'nama.required' => 'Nama wajib diisi',
                'nama.min' => 'Nama Minimal 6 Digit',
                'harga.required' => 'Harga Perlu Diisi',
                'harga.min' => 'Harga tidak bisa bernilai 0',
                'harga.numeric' => 'Harga harus beformat angka',
                'kapasitas.required' => 'Kapasitas harus diisi',
                'kapasitas.numeric' => 'Kapasitas harus berformat angka',
                'kapasitas.min' => 'Kapasitas minimal 1 orang',
                'id_kost.required' => 'id_kost perlu diisi',
                'deskripsi.required' => 'Deskripsi perlu diisi',
                // 'fasilitas.*.nama.required' => 'Fasilitas tidak boleh kosong',
            ]
        );

        if ($validator->fails()) {
            return response()->json(["code" => 400, "success" => FALSE, "message" => "ada error", 'errors' => $validator->errors()->messages()]);
        }

        $class_kamar = ClassKamar::where('id', $id)->first();

        if ($class_kamar) {
            $class_kamar->nama = $request->nama ? $request->nama : $class_kamar->nama;
            $class_kamar->harga = $request->harga ? $request->harga : $class_kamar->harga;
            $class_kamar->kapasitas = $request->kapasitas ? $request->kapasitas : $class_kamar->kapasitas;
            $class_kamar->deskripsi = $request->deskripsi ? $request->deskripsi : $class_kamar->deskripsi;
            $class_kamar->id_kost = $request->id_kost ? $request->id_kost : $class_kamar->id_kost;
            $class_kamar->active = $request->active ? $request->active : $class_kamar->active;
            $imageName = $request->foto;


            if ($request->has('newImg')) {
                $image_64 = $request->newImg; //your base64 encoded data

                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);

                //   find substring fro replace here eg: data:image/png;base64,

                $image = str_replace($replace, '', $image_64);

                $image = str_replace(' ', '+', $image);

                $imageName = Str::random(10) . '.' . $extension;
                $thumbnailImage = Image::make($image_64);

                // // $convert_img = base64_decode($image);


                // $width = Image::make($image_64)->width();
                // $height = Image::make($image_64)->height();


                // if ($width < $height) {
                //     $thumbnailImage->resize(720, null, function ($constraint) {
                //         $constraint->aspectRatio();
                //     });
                // } else {
                //     $thumbnailImage->resize(null, 480, function ($constraint) {
                //         $constraint->aspectRatio();
                //     });
                // }

                // $thumbnailImage->crop(720, 480);
                $avatarpath = public_path('/kostdata/kelas_kamar/foto/');
                $thumbnailImage->save($avatarpath . $imageName);
            }
            $class_kamar->foto = $imageName;
            $class_kamar->save();


            // FIX THIS SHIT BITHC
            // $data_fasilitas=$request->fasilitas;

            // for ($x = 0; $x < count($data_fasilitas); $x++) {
            //     $check_fasilitas = Fasilitas::where('nama', $data_fasilitas[$x])->first();
            //     if($data_fasilitas[$x]->has('id')){
            //         $old_kamar_fasilitas = Kamar_Fasilitas::where('id')
            //     }
            //     if (!$check_fasilitas) {
            //         $new_fasilitas = new Fasilitas();
            //         $new_fasilitas->nama = $data_fasilitas[$x]['nama'];
            //         $new_fasilitas->save();

            //         $new_kamar_fasiltias = new Kamar_Fasilitas();
            //         $new_kamar_fasiltias->id_kelas = $class_kamar->id;
            //         $new_kamar_fasiltias->id_fasilitas = $new_fasilitas->id;
            //         $new_kamar_fasiltias->save();
            //     } else {
            //         $check_kamar_fasilitas = Kamar_Fasilitas::where('nama', $data_fasilitas[$x])->first();

            //         $new_kamar_fasiltias = new Kamar_Fasilitas();
            //         $new_kamar_fasiltias->id_kelas = $class_kamar->id;
            //         $new_kamar_fasiltias->id_fasilitas = $check_fasilitas->id;
            //         $new_kamar_fasiltias->save();
            //     }
            // }

            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "message" => "Put Successs ",
                "data" => $class_kamar,
                "harga" => $request->harga,
            ]);
        }


        return response()->json([
            "message" => "Kelas dengan id " . $id . " Tidak Ditemukan"
        ], 400);
    }
    function delete($id)
    {

        $class_kamar = ClassKamar::where('id', $id)->first();
        if ($class_kamar) {
            $class_kamar->delete();
            return response()->json([
                "message" => "Delete Class dengan id " . $id . " Berhasil"
            ]);
        }

        return response()->json([
            "message" => "Delete Kamar dengan id " . $id . " Tidak Ditemukan"
        ], 400);
    }

    function deleteKelas(Request $request)
    {
        $class_kamar = ClassKamar::where('id', $request->id)->first();
        if ($class_kamar) {
            $data =  DB::table('class_kamar')
                ->join('kamars', 'kamars.id_kelas', '=', 'class_kamar.id')
                ->join('penghuni', 'kamars.id', '=', 'penghuni.id')
                ->whereNull('penghuni.tanggal_keluar')
                // ->where('penghuni.tanggal_keluar', '!=', null)
                // ->where('penghuni.tanggal_keluar', '!=', "")
                ->select('class_kamar.*', DB::raw("count(penghuni.id) as count"))
                ->groupBy('class_kamar.id')
                ->get();

            if ($data[0]->count > 0) {
                return response()->json([
                    "message" => "Kelas masih memiliki penghuni",
                    "success" => FALSE,
                ]);
            }

            // $kelas_hapus = ClassKamar::where('id', $request->id)->first();
            // $kelas_hapus->active = FALSE;
            // $kelas_hapus->save();

            return response()->json([
                "message" => "Hapus Kelas Berhasil",
                "success" => TRUE
            ]);
        }
    }
}
