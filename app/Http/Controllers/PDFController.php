<?php

namespace App\Http\Controllers;

// use Barryvdh\DomPDF\PDF;

use App\Kost;
use App\User;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PDFController extends Controller
{
    function get()
    {

        return response()->json([
            "message" => "Putang Ina",
            "data" => "Pakyoo"
        ]);
    }

    function getNamaPDF(Request $request, $bulan, $tahun)
    {
        $user = $request->user();
        $kost = Kost::where('owner', $user->id)->first();
        $mybulan = Carbon::now('Asia/Jakarta')->month($bulan)->year($tahun)->format('m');
        $mytahun = Carbon::now('Asia/Jakarta')->month($bulan)->year($tahun)->format('Y');

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
        $nama = "Laporan Bulanan " . $kost->nama . " - " . $namabulan . "_" . $mytahun;
        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "nama" => $nama
        ]);
    }

    function pdfku(Request $request, $bulan, $tahun)
    {
        $user = $request->user();
        // $user = User::where('id', 1)->first();
        // $kostku = Kost::where('owner',$user->id)->first;

        // $kost = Kost::where('owner', $user->id)->first();
        $kost = Kost::where('owner', 1)->first();
        // $kost = Kost::where('id', 1)->first();
        // $data_pemasukan = DB::table('transaksi')
        //     ->leftJoin('penghuni', 'transaksi.id_penghuni', '=', 'penghuni.id')
        //     ->leftJoin('kamars', 'penghuni.kamar', '=', 'kamars.id')
        //     ->select('transaksi.*', 'penghuni.nama_depan as nama_depan', 'penghuni.nama_belakang as nama_belakang', 'kamars.nama as nama_kamar')
        //     ->where('transaksi.id_kost', $kost['id'])
        //     ->whereYear('transaksi.tanggal_transaksi', $tahun)
        //     ->whereMonth('transaksi.tanggal_transaksi', $bulan)
        //     ->where('transaksi.jenis', 1)
        //     ->get();

        // $data_pemasukan = DB::table('transaksi')
        //     ->leftJoin('penghuni', 'transaksi.id_penghuni', '=', 'penghuni.id')
        //     ->leftJoin('kamars', 'penghuni.kamar', '=', 'kamars.id')
        //     ->select('transaksi.*', 'penghuni.nama_depan as nama_depan', 'penghuni.nama_belakang as nama_belakang', 'kamars.nama as nama_kamar')
        //     ->where('transaksi.id_kost', $kost['id'])
        //     ->whereYear('transaksi.tanggal_transaksi', $tahun)
        //     ->whereMonth('transaksi.tanggal_transaksi', $bulan)
        //     ->where('transaksi.jenis', 1)
        //     ->get();

        $data_pemasukan = DB::table('transaksi')
            ->leftJoin('tagihan', 'transaksi.id_tagihan', '=', 'tagihan.id')
            ->leftJoin('penghuni', 'penghuni.id', '=', 'tagihan.id_penghuni')
            ->leftJoin('kamars', 'penghuni.id_kamar', '=', 'kamars.id')
            ->select('transaksi.*', 'penghuni.nama as nama_penghuni', 'kamars.nama as nama_kamar')
            ->where('transaksi.id_kost', $kost['id'])
            ->whereYear('transaksi.tanggal_transaksi', $tahun)
            ->whereMonth('transaksi.tanggal_transaksi', $bulan)
            ->where('transaksi.jenis', 1)
            ->get();

        $total_pemasukan = DB::table('transaksi')
            ->where('transaksi.id_kost', 1)
            ->whereYear('transaksi.tanggal_transaksi', $tahun)
            ->whereMonth('transaksi.tanggal_transaksi', $bulan)
            ->where('transaksi.jenis', 1)
            ->sum('transaksi.jumlah');

        for ($x = 0; $x < count($data_pemasukan); $x++) {
            $mybulan = Carbon::parse($data_pemasukan[$x]->tanggal_transaksi)->format('m');
            // $mybulan = $data_pemasukan[$x]['tanggal_daftar']->format('m');
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

            $data_pemasukan[$x]->hari = Carbon::parse($data_pemasukan[$x]->tanggal_transaksi)->format('d');
            $data_pemasukan[$x]->bulan = $namabulan;
            $data_pemasukan[$x]->tahun = Carbon::parse($data_pemasukan[$x]->tanggal_transaksi)->format('Y');
        }

        $periodebulan = Carbon::now('Asia/Jakarta')->month($bulan)->year($tahun)->format('m');
        $periodetahun = Carbon::now('Asia/Jakarta')->month($bulan)->year($tahun)->format('Y');
        $bulanperiode = '';
        if ($periodebulan == '01') {
            $bulanperiode = 'Januari';
        } elseif ($periodebulan == '02') {
            $bulanperiode = 'Februari';
        } elseif ($periodebulan == '03') {
            $bulanperiode = 'Maret';
        } elseif ($periodebulan == '04') {
            $bulanperiode = 'April';
        } elseif ($periodebulan == '05') {
            $bulanperiode = 'Mei';
        } elseif ($periodebulan == '06') {
            $bulanperiode = 'Juni';
        } elseif ($periodebulan == '07') {
            $bulanperiode = 'Juli';
        } elseif ($periodebulan == '08') {
            $bulanperiode = 'Agustus';
        } elseif ($periodebulan == '09') {
            $bulanperiode = 'September';
        } elseif ($periodebulan == '10') {
            $bulanperiode = 'Oktober';
        } elseif ($periodebulan == '11') {
            $bulanperiode = 'November';
        } else {
            $bulanperiode = 'Desember';
        }



        $data_pengeluaran = DB::table('transaksi')
            ->where('transaksi.id_kost', 1)
            ->whereYear('transaksi.tanggal_transaksi', $tahun)
            ->whereMonth('transaksi.tanggal_transaksi', $bulan)
            ->where('transaksi.jenis', 2)
            ->get();

        $total_pengeluaran = DB::table('transaksi')
            ->where('transaksi.id_kost', $kost['id'])
            ->whereYear('transaksi.tanggal_transaksi', $tahun)
            ->whereMonth('transaksi.tanggal_transaksi', $bulan)
            ->where('transaksi.jenis', 2)
            ->sum('transaksi.jumlah');

        for ($x = 0; $x < count($data_pengeluaran); $x++) {
            $mybulan = Carbon::parse($data_pengeluaran[$x]->tanggal_transaksi)->format('m');
            // $mybulan = $data_pengeluaran[$x]['tanggal_daftar']->format('m');
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

            $data_pengeluaran[$x]->hari = Carbon::parse($data_pengeluaran[$x]->tanggal_transaksi)->format('d');
            $data_pengeluaran[$x]->bulan = $namabulan;
            $data_pengeluaran[$x]->tahun = Carbon::parse($data_pengeluaran[$x]->tanggal_transaksi)->format('Y');
        }




        // $data['judul'] = Carbon::now('Asia/Jakarta');
        // $data->judul = Carbon::now('Asia/Jakarta');

        // dd($data_pemasukan);
        $pdf = PDF::loadView('pdf',  [
            "judul" => "Laporan Bulanan " . $kost->nama,
            "periode" => "Periode " . $bulanperiode . " - " . $periodetahun,
            "data_pemasukan" => $data_pemasukan,
            "total_pemasukan" => $total_pemasukan,
            // "data_pengeluaran" => $data_pengeluaran,
            // "total_pengeluaran" => $total_pengeluaran,
        ]);
        return $pdf->download('Laporan.pdf');

        // return response()->json([
        //     "code" => 200,
        //     "success" => TRUE,
        //     "pemasukan" => $data_pemasukan,
        //     "total_pemasukan" => $total_pemasukan,
        //     "pengeluaran" => $data_pengeluaran,
        //     "total_pengeluaran" => $total_pengeluaran,
        // ]);

        // return view('pdf', [
        //     "judul" => "Laporan Bulanan " . $kost->nama,
        //     "periode" => "Periode " . $bulanperiode . " - 2020",
        //     "data_pemasukan" => $data_pemasukan,
        //     "total_pemasukan" => $total_pemasukan,
        //     // "data_pengeluaran" => $data_pengeluaran,
        //     // "total_pengeluaran" => $total_pengeluaran,
        // ]);
    }

    function bruh()
    {
        return view('welcome');
    }
}
