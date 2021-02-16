<?php

namespace App\Http\Controllers;

use App\Pendaftar;
use App\Kamar;
use App\Penghuni;
use App\Tagihan;
use App\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    function bayartagihan(Request $request)
    {
        if ($request->bayar == 0) {
            return response()->json([
                "code" => 400,
                "success" => FALSE,
                "message" => "Nominal Bayar tidak boleh bernilai 0"
            ]);
        }

        $bayar = $request->bayar;

        $lebih = FALSE;
        $data_tagihan = Tagihan::where('id_penghuni', $request->id_penghuni)->where('status', TRUE)->orderBy('tanggal_tagihan', 'asc')->get();
        $data_penghuni = Penghuni::where('id', $request->id_penghuni)->first();
        $data_kamar = Kamar::where('id', $data_penghuni['kamar'])->first();
        if (count($data_tagihan) > 0) {
            for ($x = 0; $x < count($data_tagihan); $x++) {
                $mytime = Carbon::parse($data_tagihan[$x]['tanggal_tagihan']);
                $mybulan = $mytime->format('m');
                // $mybulan = $data[$x]['tanggal_daftar']->format('m');
                $namabulan = '';
                if ($mybulan == '01') {
                    $namabulan = 'Januari';
                } elseif ($mybulan == '02') {
                    $namabulan = 'Februari';
                } elseif ($mybulan == '03') {
                    $namabulan = 'Maret';
                } elseif ($mybulan == '04') {
                    $namabulan = 'April';
                } elseif ($mybulan == '05') {
                    $namabulan = 'Mei';
                } elseif ($mybulan == '06') {
                    $namabulan = 'Juni';
                } elseif ($mybulan == '07') {
                    $namabulan = 'Juli';
                } elseif ($mybulan == '08') {
                    $namabulan = 'Agustus';
                } elseif ($mybulan == '09') {
                    $namabulan = 'September';
                } elseif ($mybulan == '10') {
                    $namabulan = 'Oktober';
                } elseif ($mybulan == '11') {
                    $namabulan = 'November';
                } else {
                    $namabulan = 'Desember';
                }

                if ($data_tagihan[$x]['jumlah'] <= $bayar) {

                    // Create transaksi

                    $data_transaksi = new Transaksi();
                    $data_transaksi->id_kost = $request->id_kost;
                    $data_transaksi->id_penghuni = $request->id_penghuni;



                    $data_transaksi->judul = "Pembayaran tagihan sewa " . $data_kamar['nama'];
                    $data_transaksi->desc = "Pembayaran tagihan sewa kamar periode : " . $namabulan . '-' . $mytime->format('Y') . ". Atas Nama : " . $data_penghuni['nama'] . ' ' . $data_penghuni['nama_belakang'] . '(id:' . $data_penghuni['id'] . '), yang menghuni : ' . $data_kamar['nama'] . '(id:' . $data_kamar['id'] . ')';
                    $data_transaksi->jenis = 1;
                    $nowtime = Carbon::now('Asia/Jakarta');
                    $data_transaksi->tanggal_transaksi = $nowtime;
                    $data_transaksi->jumlah = $data_tagihan[$x]['jumlah'];
                    $data_transaksi->status = TRUE;
                    $data_transaksi->save();

                    $bayar = $bayar - $data_tagihan[$x]['jumlah'];
                    $data_tagihan[$x]['jumlah'] = 0;
                    $data_tagihan[$x]['status'] = FALSE;
                    $data_tagihan[$x]['tanggal_pelunasan'] = $nowtime;
                    $data_tagihan[$x]->save();



                    // $data_transaksi->id_kost = $request->id_kost;
                    // $data_transaksi->id_penghuni = $request->id_penghuni;

                    // $data_transaksi->tanggal_transaksi = $mytime;
                    // $data_transaksi->status = TRUE;
                    // $data_transaksi->save();

                    Penghuni::where('id', $request->id_penghuni)->decrement('tagihan');
                    if ($x == count($data_tagihan) - 1 && $bayar > 0) {
                        $lebih = TRUE;
                    }
                } else {
                    if ($bayar == 0) {
                        break;
                    }
                    $data_transaksi = new Transaksi();
                    $data_transaksi->id_kost = $request->id_kost;
                    $data_transaksi->id_penghuni = $request->id_penghuni;

                    $data_transaksi->judul = "Menyicil tagihan sewa " . $data_kamar['nama'];
                    $data_transaksi->desc = "Menyicil tagihan sewa kamar periode : " . $namabulan . '-' . $mytime->format('Y') . ". Atas Nama : " . $data_penghuni['nama'] .  '(id:' . $data_penghuni['id'] . '), yang menghuni : ' . $data_kamar['nama'] . '(id:' . $data_kamar['id'] . ')';
                    $data_transaksi->jenis = 1;
                    $nowtime = Carbon::now('Asia/Jakarta');
                    $data_transaksi->tanggal_transaksi = $nowtime;
                    $data_transaksi->jumlah = $bayar;
                    $data_transaksi->status = TRUE;
                    $data_transaksi->save();

                    $sisahutang = $data_tagihan[$x]['jumlah'] - $bayar;
                    $data_tagihan[$x]['jumlah'] = $sisahutang;
                    $data_tagihan[$x]->save();
                    break;
                }
            }
        } else {
            $lebih = TRUE;
        }


        if ($lebih == TRUE) {
            $last_tagihan = Tagihan::where('id_penghuni', $request->id_penghuni)->orderBy('tanggal_tagihan', 'desc')->first();
            $bulanke = 1;
            if ($last_tagihan) {
                while ($bayar >= $request->perbulan) {
                    $tanggal_bonus = Carbon::parse($last_tagihan['tanggal_tagihan'])->addMonth($bulanke);
                    //transaksi
                    // $bulan = Carbon::now('Asia/Jakarta')->addMonth(1);
                    $mybulan = $tanggal_bonus->format('m');
                    $namabulan = '';
                    if ($mybulan == '01') {
                        $namabulan = 'Januari';
                    } elseif ($mybulan == '02') {
                        $namabulan = 'Februari';
                    } elseif ($mybulan == '03') {
                        $namabulan = 'Maret';
                    } elseif ($mybulan == '04') {
                        $namabulan = 'April';
                    } elseif ($mybulan == '05') {
                        $namabulan = 'Mei';
                    } elseif ($mybulan == '06') {
                        $namabulan = 'Juni';
                    } elseif ($mybulan == '07') {
                        $namabulan = 'Juli';
                    } elseif ($mybulan == '08') {
                        $namabulan = 'Agustus';
                    } elseif ($mybulan == '09') {
                        $namabulan = 'September';
                    } elseif ($mybulan == '10') {
                        $namabulan = 'Oktober';
                    } elseif ($mybulan == '11') {
                        $namabulan = 'November';
                    } else {
                        $namabulan = 'Desember';
                    }
                    $nowtime = Carbon::now('Asia/Jakarta');
                    // CREATE TAGIHAN
                    $tagih = new Tagihan();
                    $tagih->id_kost = $request->id_kost;
                    $tagih->id_penghuni = $request->id_penghuni;
                    $tagih->judul = "Tagihan sewa " . $data_kamar['nama'];
                    $tagih->desc = "Tagihan sewa kamar periode : " . $namabulan . '-' . $tanggal_bonus->format('Y') . ". Atas Nama : " . $data_penghuni['nama'] .  '(id:' . $data_penghuni['id'] . '), yang menghuni : ' . $data_kamar['nama'] . '(id:' . $data_kamar['id'] . ')';
                    $tagih->jumlah = $request->perbulan;
                    $tagih->tanggal_tagihan = $tanggal_bonus;
                    $tagih->tanggal_pelunasan = $nowtime;
                    $tagih->status = FALSE;

                    $tagih->save();

                    // CREATE RIWAYAT TRANSAKSI

                    $data_transaksi = new Transaksi();
                    $data_transaksi->id_kost = $request->id_kost;
                    $data_transaksi->id_penghuni = $request->id_penghuni;
                    $data_transaksi->judul = "Pembayaran sewa kamar " . $data_kamar['nama'];
                    $data_transaksi->desc = "Pembayaran sewa kamar periode : " . $namabulan . '-' . $tanggal_bonus->format('Y') . ". Atas Nama : " . $data_penghuni['nama'] .  '(id:' . $data_penghuni['id'] . '), yang menghuni : ' . $data_kamar['nama'] . '(id:' . $data_kamar['id'] . ')';
                    $data_transaksi->tanggal_transaksi = $nowtime;
                    $data_transaksi->jenis = 1;
                    $data_transaksi->jumlah = $request->perbulan;
                    $data_transaksi->status = TRUE;
                    $data_transaksi->save();


                    // Create tagihan and transaksi
                    $bayar = $bayar - $request->perbulan;
                    $bulanke = $bulanke + 1;
                    Penghuni::where('id', $request->id_penghuni)->decrement('tagihan');
                }
            } else {
                // return response()->json([
                //     "message" => "joe biden njeng Bayar",
                //     "sisa" => $bayar,
                //     // 'ayayaa'=>$cobaaya
                // ]);
                while ($bayar >= $request->perbulan) {
                    $tanggal_bonus = Carbon::now('Asia/Jakarta')->addMonth($bulanke);
                    //transaksi
                    // $bulan = Carbon::now('Asia/Jakarta')->addMonth(1);
                    $mybulan = $tanggal_bonus->format('m');
                    $namabulan = '';
                    if ($mybulan == '01') {
                        $namabulan = 'Januari';
                    } elseif ($mybulan == '02') {
                        $namabulan = 'Februari';
                    } elseif ($mybulan == '03') {
                        $namabulan = 'Maret';
                    } elseif ($mybulan == '04') {
                        $namabulan = 'April';
                    } elseif ($mybulan == '05') {
                        $namabulan = 'Mei';
                    } elseif ($mybulan == '06') {
                        $namabulan = 'Juni';
                    } elseif ($mybulan == '07') {
                        $namabulan = 'Juli';
                    } elseif ($mybulan == '08') {
                        $namabulan = 'Agustus';
                    } elseif ($mybulan == '09') {
                        $namabulan = 'September';
                    } elseif ($mybulan == '10') {
                        $namabulan = 'Oktober';
                    } elseif ($mybulan == '11') {
                        $namabulan = 'November';
                    } else {
                        $namabulan = 'Desember';
                    }

                    // CREATE TAGIHAN
                    $nowtime = Carbon::now('Asia/Jakarta');
                    $tagih = new Tagihan();
                    $tagih->id_kost = $request->id_kost;
                    $tagih->id_penghuni = $request->id_penghuni;
                    $tagih->judul = "Tagihan sewa " . $data_kamar['nama'];
                    $tagih->desc = "Tagihan sewa kamar periode : " . $namabulan . '-' . $tanggal_bonus->format('Y') . ". Atas Nama : " . $data_penghuni['nama'] .  '(id:' . $data_penghuni['id'] . '), yang menghuni : ' . $data_kamar['nama'] . '(id:' . $data_kamar['id'] . ')';

                    $tagih->jumlah = $request->perbulan;
                    $tagih->tanggal_tagihan = $tanggal_bonus;
                    $tagih->tanggal_pelunasan = $nowtime;
                    $tagih->status = FALSE;

                    $tagih->save();


                    // CREATE RIWAYAT TRANSAKSI
                    $data_transaksi = new Transaksi();
                    $data_transaksi->id_kost = $request->id_kost;
                    $data_transaksi->id_penghuni = $request->id_penghuni;
                    $data_transaksi->judul = "Pembayaran sewa kamar " . $data_kamar['nama'];
                    $data_transaksi->desc = "Pembayaran sewa kamar periode : " . $namabulan . '-' . $tanggal_bonus->format('Y') . ". Atas Nama : " . $data_penghuni['nama'] .  '(id:' . $data_penghuni['id'] . '), yang menghuni : ' . $data_kamar['nama'] . '(id:' . $data_kamar['id'] . ')';

                    $data_transaksi->tanggal_transaksi = $nowtime;
                    $data_transaksi->jenis = 1;
                    $data_transaksi->jumlah = $request->perbulan;
                    $data_transaksi->status = TRUE;
                    $data_transaksi->save();


                    // Create tagihan and transaksi
                    $bayar = $bayar - $request->perbulan;
                    $bulanke = $bulanke + 1;
                    Penghuni::where('id', $request->id_penghuni)->decrement('tagihan');
                }
            }
        }

        return response()->json([
            "message" => "Sukses Bayar",
            "sisa" => $bayar,
            'lebih' => $lebih,
            "banyak" => count($data_tagihan)
            // 'ayayaa'=>$cobaaya
        ]);
    }

    function catatTransaksiBayar(Request $request)
    {
        $data_bayar = $request->data_bayar;

        for ($x = 0; $x < count($data_bayar); $x++) {

            $nowtime = Carbon::now('Asia/Jakarta');
            $data_transaksi = new Transaksi();
            $data_transaksi->id_kost = $request->id_kost;
            $data_transaksi->id_tagihan = $data_bayar[$x]['id'];
            $data_transaksi->judul = 'Bayar Sewa Kamar';
            $data_transaksi->jenis = 1;
            $data_transaksi->jumlah = $data_bayar[$x]['jumlah'];
            $data_transaksi->tanggal_transaksi = $nowtime;

            $data_transaksi->save();

            $old_tagihan = Tagihan::where('id', $data_bayar[$x]['id'])->first();
            $old_tagihan->lunas = TRUE;
            $old_tagihan->tanggal_pelunasan = $nowtime;
            $old_tagihan->save();
        }

        // $data_penghuni = DB::table('penghuni')
        //     ->join('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
        //     ->join('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
        //     ->select('penghuni.*', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
        //     ->where('penghuni.id', $request->id_penghuni)
        //     ->first();

        $data_penghuni = DB::table('penghuni')
            ->leftJoin('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
            ->leftJoin('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
            ->select('penghuni.*', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
            ->where('penghuni.id', $request->id_penghuni)
            ->first();

        $data_penghuni->tagihan = count(Tagihan::where('id_penghuni', $data_penghuni->id)->where('lunas', FALSE)->get());

        return response()->json([
            "message" => "Sukses Bayar",
            "code" => 200,
            "penghuni" => $data_penghuni
            // 'ayayaa'=>$cobaaya
        ]);
    }

    function getTagihanById($id)
    {
        $data = DB::table('penghuni')
            ->join('kamars', 'penghuni.kamar', '=', 'kamars.id')
            ->join('class_kamar', 'kamars.kelas', '=', 'class_kamar.id')
            ->select('penghuni.*', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
            ->where('penghuni.id', $id)
            ->first();

        if ($data) {
            return response()->json([
                "code" => 200,
                "success" => TRUE,
                "message" => "Penghuni Ditemukan",
                "data" => $data
                // 'ayayaa'=>$cobaaya
            ]);
        }
        return response()->json([
            "code" => 200,
            "message" => "Penghuni Tidak ada",
            "success" => FALSE,
            // 'ayayaa'=>$cobaaya
        ]);
    }

    function getTagihanPenghuni(Request $request)
    {
        $keyword = $request->keyword;
        $data = DB::table('tagihan')
            ->leftJoin('penghuni', 'tagihan.id_penghuni', '=', 'penghuni.id')
            ->leftJoin('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
            ->leftJoin('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
            ->select('penghuni.*', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar', DB::raw("count(tagihan.id) as count"))
            ->where('class_kamar.id_kost', $request->id_kost)
            ->where('tagihan.lunas', FALSE)
            ->where('penghuni.nama', 'like', '%' . $keyword . '%')
            ->groupBy('penghuni.id')
            ->orderBy('count', 'desc')
            ->get();



        // for ($x = 0; $x < count($data); $x++) {

        //     $biaya_barang = DB::table('barang_tambahan_penghuni')
        //         ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
        //         // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')

        //         ->where('barang_tambahan_penghuni.id_penghuni', $data[$x]->id)
        //         // ->where('barang_tambahan_penghuni.active', TRUE)
        //         ->sum('total');

        //     // $data[$x]->tagihan = count(Tagihan::where('id_penghuni', $data[$x]->id)->where('lunas', FALSE)->get());
        // }
        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "Sukses get",
            "data" => $data,
            // 'ayayaa'=>$cobaaya
        ]);
    }

    function mycarbon()
    {
        $mytime = Carbon::now('Asia/Jakarta');
        $satubulan = Carbon::now('Asia/Jakarta')->addMonth(1);
        $duabulan = Carbon::now('Asia/Jakarta')->addMonth(2);

        return response()->json([
            "message" => "Sukses carbon",
            "now" => $mytime,
            "bulan_depa" => $satubulan,
            "bulandua" => $duabulan,
            // 'ayayaa'=>$cobaaya
        ]);
    }

    function getBarang(Request $request)
    {
        $keyword = $request->search;

        $data = DB::table('penghuni')
            ->leftJoin('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
            ->select('penghuni.*', 'kamars.nama as nama_kamar')
            ->where('class_kamar.id_kost', $request->id_kost)

            ->where('penghuni.nama', 'ilike', '%' . $keyword . '%')
            ->get();

        for ($x = 0; $x < count($data); $x++) {
            $data[$x]->barang = DB::table('barang_tambahan_penghuni')
                ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
                ->where('barang_tambahan_penghuni.id_penghuni', $data[$x]->id)
                ->get();
        }
    }

    function createTransaksi()
    {
        $data_transaksi = new Transaksi();

        $mytime = Carbon::now('Asia/Jakarta');

        // $kamarku[$x]->yaya = $kamarku[$x]->harga_kamar - 100;

        // jika kurang dari tagihan kurang dari 0 tidak perlu karena berarti memiliki kelebihan bayar

        $mybulan = $mytime->format('m');
        // $mybulan = $data[$x]['tanggal_daftar']->format('m');
        $namabulan = '';
        if ($mybulan == '01') {
            $namabulan = 'Januari';
        } elseif ($mybulan == '02') {
            $namabulan = 'Februari';
        } elseif ($mybulan == '03') {
            $namabulan = 'Maret';
        } elseif ($mybulan == '04') {
            $namabulan = 'April';
        } elseif ($mybulan == '05') {
            $namabulan = 'Mei';
        } elseif ($mybulan == '06') {
            $namabulan = 'Juni';
        } elseif ($mybulan == '07') {
            $namabulan = 'Juli';
        } elseif ($mybulan == '08') {
            $namabulan = 'Agustus';
        } elseif ($mybulan == '09') {
            $namabulan = 'September';
        } elseif ($mybulan == '10') {
            $namabulan = 'Oktober';
        } elseif ($mybulan == '11') {
            $namabulan = 'November';
        } else {
            $namabulan = 'Desember';
        }
        $data_transaksi->id_kost = 1;
        $data_transaksi->id_penghuni = 1;

        $data_transaksi->judul = "Pembayaran tagihan " . $namabulan;
        $data_transaksi->desc = "Pembayaran tagihan " . $namabulan;
        $data_transaksi->jenis = 1;
        $data_transaksi->tanggal_transaksi = $mytime;
        $data_transaksi->jumlah = 500000;
        $data_transaksi->status = TRUE;
        $data_transaksi->save();

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "data" => $data_transaksi
            // 'ayayaa'=>$cobaaya
        ]);
    }

    function allTransaksi()
    {
        $data = Transaksi::where('id_kost', 1)->where('jenis', 1)->get();

        // $data = array(
        //     'stack' => 'aa',
        //     'screen' => 'ss',
        // );

        $banyakhari = Carbon::now('Asia/Jakarta')->month(10)->year(2020)->daysInMonth;
        $arrBulan = array();
        for ($x = 1; $x <= $banyakhari; $x++) {
            $data = DB::table('transaksi')
                ->where('id_kost', 1)
                ->whereMonth('tanggal_transaksi', 10)
                ->whereDay('tanggal_transaksi', $x)
                ->get();
            $datahari = array(
                "hari" => $x,
                "data" => $data
            );
            if (count($data) > 0) {
                array_push($arrBulan, $datahari);
            }
        }

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "data" => $data,
            "banyak" => $banyakhari,
            "everyday" => $arrBulan,
            // 'ayayaa'=>$cobaaya
        ]);
    }

    function lastTagihan($id)
    {
        $last_tagihan = Tagihan::where('id_penghuni', $id)->orderBy('tanggal_tagihan', 'desc')->first();
        $mypenghuni = Penghuni::all();
        $data_pendaftar = Pendaftar::all();
        $tagihan = Tagihan::all();
        $transaksi = Transaksi::all();

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "data" => $last_tagihan,
            "penghuni" => $mypenghuni,
            "pendaftar" => $data_pendaftar,
            "tagihan" => $tagihan,
            "transaksi" => $transaksi,
            // 'ayayaa'=>$cobaaya
        ]);
    }


    function filterPengeluaran(Request $request)
    {
        // $data = Transaksi::where('id_kost', $id)->whereMonth('tanggal_transaksi', '10')->get();
        // $data = DB::table('transaksi')
        //     ->where('id_kost', $request->id_kost)
        //     ->whereMonth('tanggal_transaksi', $request->bulan)
        //     ->whereYear('tanggal_transaksi', $request->tahun)
        //     ->get();

        // $uang = DB::table('transaksi')
        //     ->where('id_kost', $request->id_kost)
        //     ->whereMonth('tanggal_transaksi', $request->bulan)
        //     ->whereYear('tanggal_transaksi', $request->tahun)
        //     ->sum('jumlah');



        $data = DB::table('transaksi')
            ->where('id_kost', $request->id_kost)
            ->whereMonth('tanggal_transaksi', $request->bulan)
            ->whereYear('tanggal_transaksi', $request->tahun)
            ->where('jenis', $request->jenis)
            ->get();

        for ($x = 0; $x < count($data); $x++) {
            $mybulan = Carbon::parse($data[$x]->tanggal_transaksi)->format('m');
            // $mybulan = $data[$x]['tanggal_daftar']->format('m');
            $namabulan = '';
            if ($mybulan == '01') {
                $namabulan = 'Januari';
            } elseif ($mybulan == '02') {
                $namabulan = 'Februari';
            } elseif ($mybulan == '03') {
                $namabulan = 'Maret';
            } elseif ($mybulan == '04') {
                $namabulan = 'April';
            } elseif ($mybulan == '05') {
                $namabulan = 'Mei';
            } elseif ($mybulan == '06') {
                $namabulan = 'Juni';
            } elseif ($mybulan == '07') {
                $namabulan = 'Juli';
            } elseif ($mybulan == '08') {
                $namabulan = 'Agustus';
            } elseif ($mybulan == '09') {
                $namabulan = 'September';
            } elseif ($mybulan == '10') {
                $namabulan = 'Oktober';
            } elseif ($mybulan == '11') {
                $namabulan = 'November';
            } else {
                $namabulan = 'Desember';
            }

            $data[$x]->hari = Carbon::parse($data[$x]->tanggal_transaksi)->format('d');
            $data[$x]->bulan = $namabulan;
            $data[$x]->tahun = Carbon::parse($data[$x]->tanggal_transaksi)->format('Y');
        }


        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "data" => $data
        ]);
    }

    function filterPemasukan(Request $request)
    {
        $data = DB::table('transaksi')
            ->leftJoin('tagihan', 'transaksi.id_tagihan', '=', 'tagihan.id')
            ->leftJoin('penghuni', 'penghuni.id', '=', 'tagihan.id_penghuni')
            ->leftJoin('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
            ->leftJoin('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
            ->select('transaksi.*', 'penghuni.nama as nama_penghuni',  'penghuni.id as id_penghuni', 'penghuni.foto_diri as foto_diri', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
            ->where('transaksi.id_kost', $request->id_kost)
            ->whereYear('transaksi.tanggal_transaksi', $request->tahun)
            ->whereMonth('transaksi.tanggal_transaksi', $request->bulan)
            ->where('transaksi.jenis', 1)
            ->orderBy('transaksi.tanggal_transaksi', 'asc')
            ->get();


        for ($x = 0; $x < count($data); $x++) {
            $tanggal_transaksi = Carbon::parse($data[$x]->tanggal_transaksi);
            $data[$x]->tanggal_transaksi = Carbon::parse($data[$x]->tanggal_transaksi);
            $data[$x]->tanggal_transaksi = Carbon::parse($data[$x]->tanggal_transaksi);
            $data[$x]->barang = DB::table('barang_tambahan_penghuni')
                ->leftJoin('barang', 'barang_tambahan_penghuni.id_barang', '=', 'barang.id')
                // ->select('barang_tambahan_penghuni.id as id', 'barang.nama as nama', 'barang_tambahan_penghuni.qty as qty', 'barang_tambahan_penghuni.total as total')
                ->select('barang_tambahan_penghuni.*', 'barang.nama as nama')
                ->where('barang_tambahan_penghuni.id_penghuni', $data[$x]->id_penghuni)
                ->where('barang_tambahan_penghuni.tanggal_masuk', '<=', $tanggal_transaksi)
                ->where(function ($query) use ($tanggal_transaksi) {
                    $query->where('barang_tambahan_penghuni.tanggal_keluar', '>=', $tanggal_transaksi)
                        ->orWhere('barang_tambahan_penghuni.tanggal_keluar', null);
                    // $query->where(function ($query) use ($tanggal_tagihan) {
                    //     $query->where('barang_tambahan_penghuni.tanggal_masuk', '<=', Carbon::parse($tanggal_tagihan));
                    // })->orWhere('barang_tambahan_penghuni.tanggal_keluar', null);
                })->get();
        }


        // $banyakhari = Carbon::now('Asia/Jakarta')->month($request->bulan)->year($request->tahun)->daysInMonth;
        // $arrBulan = array();
        // for ($x = $banyakhari; $x >= 1; $x--) {


        //     $data = DB::table('transaksi')
        //         ->leftJoin('tagihan', 'transaksi.id_tagihan', '=', 'tagihan.id')
        //         ->leftJoin('penghuni', 'penghuni.id', '=', 'tagihan.id_penghuni')
        //         ->select('transaksi.*', 'penghuni.nama_depan as nama_depan', 'penghuni.nama_belakang as nama_belakang')
        //         ->where('transaksi.id_kost', $request->id_kost)
        //         ->whereYear('transaksi.tanggal_transaksi', $request->tahun)
        //         ->whereMonth('transaksi.tanggal_transaksi', $request->bulan)
        //         ->whereDay('transaksi.tanggal_transaksi', $x)
        //         ->where('transaksi.jenis', 1)
        //         ->get();

        //     for ($y = 0; $y < count($data); $y++) {
        //         $mywaktu = Carbon::parse($data[$y]->tanggal_transaksi)->format('H:i');
        //         $data[$y]->waktu = $mywaktu;
        //     }
        //     $datahari = array(
        //         "hari" => $x,
        //         "data" => $data
        //     );
        //     if (count($data) > 0) {
        //         array_push($arrBulan, $datahari);
        //     }
        // }

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            // "data" => $arrBulan,
            "data" => $data,
            // 'ayayaa'=>$cobaaya
        ]);
    }

    function addPengeluaran(Request $request)
    {
        $nowtime = Carbon::now('Asia/Jakarta');
        //transaksi

        $data_transaksi = new Transaksi();
        $data_transaksi->id_kost = $request->id_kost;
        $data_transaksi->judul = $request->judul;
        $data_transaksi->tanggal_transaksi = $nowtime;
        $data_transaksi->jenis = 2;
        $data_transaksi->jumlah = $request->jumlah;
        $data_transaksi->save();

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "data" => $data_transaksi
        ]);
    }

    function allPengeluaran($id, Request $request)
    {
        $data = Transaksi::where('id_kost', $id)->where('jenis', 2)->get();
        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "data" => $data
        ]);
    }

    function customTransaksi(Request $request)
    {
        $nowtime = Carbon::now('Asia/Jakarta')->subDays($request->kurang);
        //transaksi

        for ($x = 0; $x <= $request->banyak; $x++) {
            $data_transaksi = new Transaksi();
            $data_transaksi->id_kost = $request->id_kost;
            $data_transaksi->id_penghuni = $request->id_penghuni;
            $data_transaksi->judul = $request->judul . "____" . $x;
            $data_transaksi->desc = $request->desc . "____" . $x;
            $data_transaksi->tanggal_transaksi = $nowtime;
            $data_transaksi->jenis = 1;
            $data_transaksi->jumlah = $request->jumlah;
            $data_transaksi->status = TRUE;
            $data_transaksi->save();
        }

        // $data_transaksi = new Transaksi();
        // $data_transaksi->id_kost = $request->id_kost;
        // $data_transaksi->judul = $request->judul;
        // $data_transaksi->desc = $request->desc;
        // $data_transaksi->tanggal_transaksi = $nowtime;
        // $data_transaksi->jenis = 2;
        // $data_transaksi->jumlah = $request->jumlah;
        // $data_transaksi->status = TRUE;
        // $data_transaksi->save();

        return response()->json([
            "code" => 200,
            "success" => TRUE,

        ]);
    }

    function createCustomTransaksi(Request $request)
    {
        $customday = Carbon::now('Asia/Jakarta')->day($request->day)->month($request->month)->year($request->year);
        if ($request->jenis == 1) {
            for ($x = 0; $x < $request->banyak; $x++) {
                $data_transaksi = new Transaksi();
                $data_transaksi->id_kost = $request->id_kost;
                $data_transaksi->id_tagihan = $request->id_tagihan;
                $data_transaksi->judul = $request->judul . "____" . $x;
                $data_transaksi->tanggal_transaksi = $customday;
                $data_transaksi->jenis = 1;
                $data_transaksi->jumlah = $request->jumlah;

                $data_transaksi->save();
            }
        } else {
            for ($x = 0; $x < $request->banyak; $x++) {
                $data_transaksi = new Transaksi();
                $data_transaksi->id_kost = $request->id_kost;
                $data_transaksi->judul = $request->judul . "____" . $x;

                $data_transaksi->tanggal_transaksi = $customday;
                $data_transaksi->jenis = 2;
                $data_transaksi->jumlah = $request->jumlah;

                $data_transaksi->save();
            }
        }

        // $customjam = Carbon::now('Asia/Jakarta')->month(10)->year(2020)->format('H:i');
        // $sekarang = Carbon::now('Asia/Jakarta');
        return response()->json([
            "code" => 200,
            "success" => TRUE,
        ]);
    }

    function getTransaksi($id, $jenis, $month, $year)
    {

        // $data = DB::table('transaksi')
        // ->leftJoin('tagihan', 'penghuni.id_kamar', '=', 'kamars.id')
        // ->leftJoin('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
        // ->select('penghuni.*', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
        // ->where('penghuni.id', $request->id_penghuni)
        // ->first();
        if ($jenis == 1) {
            $data = DB::table('transaksi')
                ->join('tagihan', 'transaksi.id_tagihan', '=', 'tagihan.id')
                ->join('penghuni', 'tagihan.id_penghuni', '=', 'penghuni.id')
                ->join('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
                ->join('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
                ->select('transaksi.*', 'penghuni.nama as nama_penghuni',  'kamars.nama as nama_kamar', 'penghuni.foto_diri as foto')
                ->where('transaksi.id_kost', $id)
                ->where('transaksi.jenis', $jenis)
                ->whereMonth('tanggal_transaksi', $month)
                ->whereYear('tanggal_transaksi', $year)
                ->get();
            for ($x = 0; $x < count($data); $x++) {
                $data[$x]->tanggal_transaksi = Carbon::parse($data[$x]->tanggal_transaksi);
            }
        } else {
            $data = Transaksi::where('id_kost', $id)->where('jenis', $jenis)
                ->whereMonth('tanggal_transaksi', $month)
                ->whereYear('tanggal_transaksi', $year)
                ->get();

            for ($x = 0; $x < count($data); $x++) {
                $data[$x]['tanggal_transaksi'] = Carbon::parse($data[$x]['tanggal_transaksi']);
            }
        }

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "data" => $data
        ]);
    }

    function hapusNanti()
    {
        $data = Transaksi::all();
        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "data" => $data
        ]);
    }

    function cobaWa(Request $request)
    {
        $data1 = [
            'number' => $request->number,
            'message' => $request->message,
            'my_wa_token' => '2igsDETMf7RMeQ73cvVKGfGep9cQD0BGhPZtzwvWLGqNSAwwTpRdHkZWmYgdMNfSKAB1Emaea3TcIy4YBMF9ChlI9qsb4Qd40GTwiWbg2GTOUG2bU3Bb3raMkBLgUodAfJcGeq3HkjSKeOJ7LR1KcjgNHYxEnuRVExMS8x9wLgWvuWaLGrGAXIQV4WVcHGWQdqCmzrgr'
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://boiling-sea-40553.herokuapp.com/send-message",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data1),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            print_r(json_decode($response));
        }
    }
}
