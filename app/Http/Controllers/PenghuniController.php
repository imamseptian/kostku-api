<?php

namespace App\Http\Controllers;

use App\Barang;
use App\Barang_Tambahan_Pendaftar;
use App\Barang_Tambahan_Penghuni;
use App\Penghuni;
use App\Pendaftar;
use App\ClassKamar;
use Illuminate\Http\Request;
use File;
use Carbon\Carbon;
use App\Kamar;
use App\Kost;
use App\Mail\CobaMail;
use App\Tagihan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class PenghuniController extends Controller
{
    public function getAll(Request $request)
    {
        // $pendaftar = Calon_Penghuni::where('id',$id)->first();
        $owner = $request->user();
        $mykeyword = $request->namakeyword;
        $data = Penghuni::where('id_kost', $request->id_kost)->where('active', TRUE)->where('nama', 'like', '%' . $mykeyword . '%')->orderBy($request->sortname, $request->orderby)->paginate(10);

        // if ($request->has('sortname')) {
        //     // $data = Penghuni::where('id_kost',$owner['id'])->where('active',TRUE)->where('nama', 'ilike','%'. $request->namakeyword.'%')->orderBy($request->sortname, $request->orderby)->paginate(10);
        //     $data = Penghuni::where('id_kost', $request->id_kost)->where('active', TRUE)->where(function ($query) use ($mykeyword) {
        //         $query->where('nama_depan', 'ilike', '%' . $mykeyword . '%')
        //             ->orWhere('nama_belakang', 'ilike', '%' . $mykeyword . '%');
        //     })->orderBy($request->sortname, $request->orderby)->paginate(10);
        // } else {
        //     $data = Penghuni::where('id_kost', $owner['id'])->where('active', TRUE)->orderBy('nama', 'asc')->paginate(10);
        // }

        for ($x = 0; $x < count($data); $x++) {
            $data[$x]['tanggal_masuk'] = Carbon::parse($data[$x]['tanggal_masuk']);
            $data[$x]['tanggal_lahir'] = Carbon::parse($data[$x]['tanggal_lahir']);
        }

        return response()->json([
            "message" => "GET Method Success",
            "data" => $data,
            'keyword' => $request->namakeyword,
            // 'ayayaa'=>$cobaaya
        ]);

        // $data = Penghuni::where('id_kost',$request->id_kost)->where('active',TRUE)->paginate(10);

        // return response()->json([
        //     "message"=>" Daftar Penghuni Method Success",
        //     "data"=>$data,
        // ]);


    }

    function addPenghuni(Request $request)
    {

        $kamar = Kamar::where('id', $request->request_kamar)->first();
        $kelas = ClassKamar::where('id', $kamar->id_kelas)->first();
        $bawaan = $request->barang_tambahan;
        if ($kamar) {
            if ($request->terima === TRUE) {
                $total = Penghuni::where('id_kamar', $request->request_kamar)->get();
                $banyak = count($total);
                if ($kelas->kapasitas > $banyak) {
                    $penghuni = new Penghuni();
                    $mytime = Carbon::now('Asia/Jakarta');
                    $penghuni->nama = $request->nama;

                    $penghuni->id_kost = $request->id_kost;
                    $penghuni->kelamin = $request->kelamin;
                    $penghuni->provinsi = $request->provinsi;
                    $penghuni->kota = $request->kota;
                    $penghuni->alamat = $request->alamat;
                    $penghuni->email = $request->email;
                    $penghuni->notelp = $request->notelp;
                    $penghuni->noktp = $request->noktp;
                    $penghuni->id_kamar = $request->request_kamar;
                    $penghuni->status_pekerjaan = $request->status_pekerjaan;
                    $penghuni->status_hubungan = $request->status_hubungan;
                    $penghuni->tempat_kerja_pendidikan = $request->tempat_kerja_pendidikan;
                    $penghuni->active = TRUE;
                    $penghuni->foto_ktp = $request->foto_ktp;
                    $penghuni->foto_diri = $request->foto_diri;
                    $penghuni->tanggal_masuk = $mytime;
                    $penghuni->tanggal_lahir = Carbon::parse($request->tanggal_lahir);


                    $penghuni->save();
                    $mytime = Carbon::now('Asia/Jakarta');
                    $biaya_barang_tambahan = 0;
                    for ($x = 0; $x < count($bawaan); $x++) {
                        $check_barang = DB::table('barang')->where('nama', $bawaan[$x]['nama'])->first();

                        if ($check_barang == null) {
                            $barang_baru = new Barang();
                            $barang_baru->nama =  $bawaan[$x]['nama'];
                            $barang_baru->save();

                            $barang_tambahan = new Barang_Tambahan_Penghuni();
                            $barang_tambahan->id_penghuni = $penghuni->id;
                            $barang_tambahan->id_barang = $barang_baru->id;
                            $barang_tambahan->qty = $bawaan[$x]['qty'];
                            $barang_tambahan->total = $bawaan[$x]['total'];
                            $barang_tambahan->tanggal_masuk = $mytime;

                            $barang_tambahan->save();
                        } else {
                            $barang_tambahan = new Barang_Tambahan_Penghuni();
                            $barang_tambahan->id_penghuni = $penghuni->id;
                            $barang_tambahan->id_barang = $check_barang->id;
                            $barang_tambahan->qty = $bawaan[$x]['qty'];
                            $barang_tambahan->total = $bawaan[$x]['total'];
                            $barang_tambahan->tanggal_masuk = $mytime;
                            $barang_tambahan->save();
                        }
                        $biaya_barang_tambahan += $bawaan[$x]['total'];
                    }


                    $mybulan = $mytime->format('m');

                    $tagih = new Tagihan();
                    $tagih->id_kamar = $request->request_kamar;
                    $tagih->id_penghuni = $penghuni->id;
                    $tagih->jumlah = $kelas['harga'] + $biaya_barang_tambahan;
                    $tagih->tanggal_tagihan = $mytime;
                    $tagih->lunas = FALSE;
                    $tagih->save();

                    $oldpendaftar = Pendaftar::where('id', $request->id)->first();

                    if ($oldpendaftar) {
                        $oldpendaftar->active = FALSE;

                        $oldpendaftar->save();
                    }

                    $this->kirimEmail($request->terima, $request->nama, $request->email, $request->id_kost, '');

                    return response()->json([
                        "code" => 200,
                        "success" => TRUE,
                        "message" => "add penghuni Method berhasil",
                        "data" => $request->terima,
                        // 'myfile'=>$files
                    ]);
                }
                return response()->json([
                    "code" => 402,
                    "success" => FALSE,
                    "message" => "add penghuni Method gagal karena kamar penuh",
                    "data" => $request->terima,
                    // 'myfile'=>$files
                ]);
            }

            $oldpendaftar = Pendaftar::where('id', $request->id)->first();

            if ($oldpendaftar) {


                // for ($x = 0; $x < count($bawaan); $x++) {
                //     $check_barang = DB::table('barang')->where('nama', $bawaan[$x]['nama'])->first();
                //     $barang_ditolak = Barang_Tambahan_Pendaftar::where('id_pendaftar', $oldpendaftar->id)->update(['active' => FALSE]);
                // }
                $oldpendaftar->active = FALSE;

                $oldpendaftar->save();
            }

            $this->kirimEmail($request->terima, $request->nama, $request->email, $request->id_kost, $request->alasan);
            $this->notifikasiWA($request->terima, $request->nama, $request->email, $request->id_kost, $request->alasan);

            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "message" => "TOLAK PENGHUNI berhasil",
                "data" => $request->terima,
                // 'myfile'=>$files
            ]);
        }
    }

    public function editPenghuni(Request $request)
    {
        $data_penghuni = Penghuni::where('id', $request->id)->first();
        if ($data_penghuni) {
            $nama_foto_diri = $data_penghuni->foto_diri;
            if ($request->new_foto_diri != null) {
                $image_64 = $request->new_foto_diri;
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $nama_foto_diri = Str::random(10) . '.' . $extension;
                $thumbnailImage = Image::make($image_64);
                $thumbnailImage->stream(); // <-- Key point
                Storage::disk('local')->put('public/images/pendaftar/' . $nama_foto_diri, $thumbnailImage);
            }
            $nama_foto_ktp = $data_penghuni->foto_ktp;
            if ($request->new_foto_ktp != null) {
                $image_64 = $request->new_foto_diri;
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $nama_foto_ktp = Str::random(10) . '.' . $extension;
                $thumbnailImage = Image::make($image_64);

                $thumbnailImage->stream(); // <-- Key point
                Storage::disk('local')->put('public/images/pendaftar/' . $nama_foto_ktp, $thumbnailImage);
            }

            $tanggal_lahir = Carbon::parse($request->tanggal_lahir);
            $data_penghuni->nama = $request->nama;
            $data_penghuni->tanggal_lahir = $tanggal_lahir;
            $data_penghuni->notelp = $request->notelp;
            $data_penghuni->email = $request->email;
            $data_penghuni->provinsi = $request->provinsi;
            $data_penghuni->kota = $request->kota;
            $data_penghuni->alamat = $request->alamat;
            $data_penghuni->noktp = $request->noktp;
            $data_penghuni->foto_diri = $nama_foto_diri;
            $data_penghuni->foto_ktp = $nama_foto_ktp;
            $data_penghuni->status_hubungan = $request->status_hubungan;
            $data_penghuni->status_pekerjaan = $request->status_pekerjaan;
            $data_penghuni->tempat_kerja_pendidikan = $request->tempat_kerja_pendidikan;
            $data_penghuni->save();
            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "message" => "Edit penghuni berhasil",
                "foto_diri" => $nama_foto_diri,
                "foto_Ktp" => $nama_foto_ktp,
            ]);
        } else {
            return response()->json([
                "code" => 404,
                "success" => FALSE,
                "message" => "Penghuni tidak ditemukan",
            ]);
        }
    }

    function ListPenghuni()
    {
        $data = Penghuni::all();
        $yy = 'aaaa';

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "Success",
            "data" => $data,
            // 'myfile'=>$files
        ]);
    }

    function FilterPenghuni(Request $request)
    {
        if ($request->kelamin) {
            $data = Penghuni::where('id_kost', $request->id_kost)->where('kelamin', $request->kelamin)->orderBy('nama', 'asc')->get();

            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "message" => "Success",
                "data" => $data,
                // 'myfile'=>$files
            ]);
        } else if ($request->provinsi) {
            if ($request->multi) {
                $data = Penghuni::where('id_kost', $request->id_kost)->whereIn('provinsi', $request->provinsi)->orderBy('nama', 'asc')->get();
            } else {
                $data = Penghuni::where('id_kost', $request->id_kost)->where('provinsi', $request->provinsi)->orderBy('nama', 'asc')->get();
            }
            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "message" => "Success",
                "data" => $data,
                "multi" => $request->multi
                // "data" => $data,
                // 'myfile'=>$files
            ]);
        } else if ($request->kota) {
            if ($request->multi) {
                $data = Penghuni::where('id_kost', $request->id_kost)->whereIn('kota', $request->kota)->orderBy('nama', 'asc')->get();
            } else {
                $data = Penghuni::where('id_kost', $request->id_kost)->where('kota', $request->kota)->orderBy('nama', 'asc')->get();
            }
            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "message" => "Success",
                "data" => $data,
                "multi" => $request->multi
                // "data" => $data,
                // 'myfile'=>$files
            ]);
        }
    }

    public function kirimEmail(Request $request)
    {
        // $terima, $nama, $email_penghuni, $id_kost, $alasan
        // $this->kirimEmail($request->terima, $request->nama, $request->email, $request->id_kost, $request->alasan);

        $kost = Kost::where('id', $request->id_kost)->first();
        $owner = Kost::where('id', $kost->owner)->first();

        $details = [
            'nama' => $request->nama,
            'nama_kost' => $kost->nama,
            "terima" => $request->terima,
            'number' => $kost->notelp,
            'urlkost' => 'https://apikostku.xyz/storage/images/kost/' . $kost->foto_kost,
            'owner' => $owner->nama,
        ];

        // Mail::to($email_penghuni)->send(new CobaMail($details));
        Mail::to($request->email_penghuni)->send(new CobaMail($details));

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "email send",

        ]);
    }

    public function notifikasiWA(Request $request)
    {
        // $terima, $nama, $notelp, $id_kost, $alasan
        // $fields = array('number' => $request->number, 'message' => $request->number);
        // $this->notifikasiWA($request->terima,$request->nama, $request->email, $request->id_kost, $request->alasan);

        $kost = Kost::where('id', $request->id_kost)->first();
        $owner = Kost::where('id', $kost->owner)->first();

        $data = array(
            'number' => $request->notelp,
            'message' => 'Hai ' . $request->nama . '\r\n\r\nAnda telah diterima menjadi penghuni ' . $kost->nama . '\r\n\r\nSilahkan persiapkan perpindahan dan segera datang ke kost sesegera mungkin\r\n\r\nHubungi pengelola kost ernis @' . $kost->notelp . ' untuk informasi lebih lanjut.\r\n\r\nTerima Kasih'
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
            "message" => 'Hai ' . $request->nama . '\n\nAnda telah diterima menjadi penghuni ' . $kost->nama . '\n\nSilahkan persiapkan perpindahan dan segera datang ke kost sesegera mungkin\n\nHubungi pengelola kost ernis @' . $kost->notelp . ' untuk informasi lebih lanjut.\n\nTerima Kasih'
        ]);
    }
}
