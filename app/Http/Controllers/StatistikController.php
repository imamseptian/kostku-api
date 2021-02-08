<?php

namespace App\Http\Controllers;

use App\Mail\CobaMail;
use App\Penghuni;
use App\Transaksi;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StatistikController extends Controller
{
    function StatistikPie(Request $request)
    {


        // $data_penghuni = Penghuni::select('kelamin')->where('id_kost', $request->id_kost)->distinct()->get();

        // for ($x = 0; $x < count($data_penghuni); $x++) {
        //     $temp_data = Penghuni::where('id_kost', $request->id_kost)->where('kelamin', $data_penghuni[$x]->kelamin)->get();
        //     $data_penghuni[$x]->value = count($temp_data);
        // }

        // $data_provinsi = Penghuni::select('provinsi')->where('id_kost', $request->id_kost)->distinct()->get();
        // for ($x = 0; $x < count($data_provinsi); $x++) {
        //     $temp_data = Penghuni::where('id_kost', $request->id_kost)->where('provinsi', $data_provinsi[$x]->provinsi)->get();
        //     $data_provinsi[$x]->value = count($temp_data);
        // }

        // $data_kota = Penghuni::select('kota')->where('id_kost', $request->id_kost)->distinct()->get();
        // for ($x = 0; $x < count($data_kota); $x++) {
        //     $temp_data = Penghuni::where('id_kost', $request->id_kost)->where('kota', $data_kota[$x]->kota)->get();
        //     $data_kota[$x]->value = count($temp_data);
        // }

        // $ayaya = Penghuni::select('kelamin', DB::raw('count(kelamin) quantity'))->groupBy('kelamin')->get();
        $data_penghuni = Penghuni::select('kelamin', DB::raw('count(kelamin) quantity'))->orderByDesc('quantity')->groupBy('kelamin')->get();
        // $provcount = Penghuni::select('provinsi', DB::raw('count(provinsi) quantity'))->orderByDesc('quantity')->groupBy('provinsi')->get();
        $data_provinsi = Penghuni::select('provinsi', DB::raw('count(provinsi) quantity'))->orderByDesc('quantity')->groupBy('provinsi')->get();
        $data_kota = Penghuni::select('kota', DB::raw('count(kota) quantity'))->orderByDesc('quantity')->groupBy('kota')->get();

        // if (count($data_provinsi) > 4) {
        //     for ($x = 3; $x < count($data_provinsi) - 1; $x++) {
        //         unset($data_provinsi[$x]);
        //     }
        // }
        // array_splice($array, 1, 1);
        // unset($provcount[2]);

        return response()->json([
            "message" => "Putang Ina",
            "data" => "Pakyoo",
            // "penghuni" => $data_peghuni,
            "penghuni" => $data_penghuni,
            "provinsi" => $data_provinsi,
            "kota" => $data_kota,
            // "coba" => $ayaya,
            // "provcount" => $provcount
        ]);
    }



    function ChartPendapatan(Request $request)
    {
        $data = Transaksi::where('id_kost', 1)->whereMonth('tanggal_transaksi', 11)->get();
        $ayaya = Transaksi::all();
        $gaji = Transaksi::where('id_kost', 1)->whereMonth('tanggal_transaksi', 11)->sum('jumlah');

        // for ($x = 0; $x < count($unique_data); $x++) {
        //     $temp_data = Penghuni::where('id_kost', 1)->where('kelamin', $unique_data[$x]->kelamin)->get();
        //     $unique_data[$x]->penghuni = $temp_data;
        // }

        return response()->json([
            "message" => "Putang Ina",
            "data" => "Pakyoo",
            // "penghuni" => $data_peghuni,
            "pendapatan" => $data,
            "semua" => $ayaya,
            "jumlah" => $gaji
        ]);
    }


    function ChartKeuangan($id)
    {
        $data_terakhir = Transaksi::where('id_kost', $id)->where('jenis', 1)->orderBy('tanggal_transaksi', 'desc')->first();
        $data_pertama = Transaksi::where('id_kost', $id)->where('jenis', 1)->orderBy('tanggal_transaksi', 'asc')->first();

        $date1 = strtotime($data_pertama->tanggal_transaksi);
        $date2 = strtotime($data_terakhir->tanggal_transaksi);

        $diff = abs($date2 - $date1);

        $years = floor($diff / (365 * 60 * 60 * 24));

        $months = floor(($diff - $years * 365 * 60 * 60 * 24)
            / (30 * 60 * 60 * 24));

        $data_pendapatan = [];

        if ($months > 6) {
            for ($x = 0; $x <= 6; $x++) {
                $mytime = Carbon::parse($data_terakhir->tanggal_transaksi)->subMonth($x);

                $nominal = Transaksi::where('id_kost', $id)->where('jenis', 1)->whereMonth('tanggal_transaksi', $mytime->format('m'))->sum('jumlah');
                $array_pendapatan = array(
                    'value' => $nominal,
                    'tanggal_transaksi' => $mytime
                );
                array_unshift($data_pendapatan, $array_pendapatan);
            }
        } else {
            for ($x = 0; $x <= $months; $x++) {
                $mytime = Carbon::parse($data_terakhir->tanggal_transaksi)->subMonth($x);

                $nominal = Transaksi::where('id_kost', $id)->where('jenis', 1)->whereMonth('tanggal_transaksi', $mytime->format('m'))->sum('jumlah');
                $array_pendapatan = array(
                    'value' => $nominal,
                    'tanggal_transaksi' => $mytime
                );
                array_unshift($data_pendapatan, $array_pendapatan);
            }
        }

        $data_terakhir = Transaksi::where('id_kost', $id)->where('jenis', 2)->orderBy('tanggal_transaksi', 'desc')->first();
        $data_pertama = Transaksi::where('id_kost', $id)->where('jenis', 2)->orderBy('tanggal_transaksi', 'asc')->first();



        $date1 = strtotime($data_pertama->tanggal_transaksi);
        $date2 = strtotime($data_terakhir->tanggal_transaksi);

        $diff = abs($date2 - $date1);

        $years = floor($diff / (365 * 60 * 60 * 24));

        $months = floor(($diff - $years * 365 * 60 * 60 * 24)
            / (30 * 60 * 60 * 24));

        $data_pengeluaran = [];
        // $data_label = [];
        if ($months > 6) {
            for ($x = 0; $x <= 6; $x++) {
                $mytime = Carbon::parse($data_terakhir->tanggal_transaksi)->subMonth($x);

                $nominal = Transaksi::where('id_kost', $id)->where('jenis', 2)->whereMonth('tanggal_transaksi', $mytime->format('m'))->sum('jumlah');
                $array_pengeluaran = array(
                    'value' => $nominal,
                    'tanggal_transaksi' => $mytime
                );
                array_unshift($data_pengeluaran, $array_pengeluaran);
            }
        } else {
            for ($x = 0; $x <= $months; $x++) {
                $mytime = Carbon::parse($data_terakhir->tanggal_transaksi)->subMonth($x);

                $nominal = Transaksi::where('id_kost', $id)->where('jenis', 2)->whereMonth('tanggal_transaksi', $mytime->format('m'))->sum('jumlah');
                $array_pengeluaran = array(
                    'value' => $nominal,
                    'tanggal_transaksi' => $mytime
                );
                array_unshift($data_pengeluaran, $array_pengeluaran);
            }
        }


        return response()->json([
            "message" => "Success",
            "code" => 200,

            "selisih" => $months,
            "pendapatan" => $data_pendapatan,
            "pengeluaran" => $data_pengeluaran
        ]);
    }

    function modalKeuangan(Request $request)
    {
        if ($request->jenis == 1) {
            $data = DB::table('transaksi')
                ->leftJoin('penghuni', 'transaksi.id_penghuni', '=', 'penghuni.id')
                ->leftJoin('kamars', 'penghuni.kamar', '=', 'kamars.id')
                ->select('transaksi.*', 'penghuni.nama as nama_penghuni',  'kamars.nama as nama_kamar')
                ->where('transaksi.id_kost', $request->id_kost)
                ->whereYear('transaksi.tanggal_transaksi', $request->tahun)
                ->whereMonth('transaksi.tanggal_transaksi', $request->bulan)

                ->where('transaksi.jenis', 1)
                ->get();
        } else {
            $data = DB::table('transaksi')
                ->where('transaksi.id_kost', $request->id_kost)
                ->whereYear('transaksi.tanggal_transaksi', $request->tahun)
                ->whereMonth('transaksi.tanggal_transaksi', $request->bulan)
                ->where('transaksi.jenis', 2)
                ->get();
        }

        // $data = Transaksi::where('id_kost', 1)->whereMonth('tanggal_transaksi', $request->bulan)->whereYear('tanggal_transaksi', $request->tahun)->get();


        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "success",
            "data" => $data
        ]);
    }

    function cobaEmail()
    {
        $details = [
            'nama' => 'Selamat Anda Diterima di Kostan Ernis',
            'nama_kost' => 'Kost Ernis',
            "terima" => FALSE,
            'number' => '082138213872183',
            'urlkost' => 'https://apikostku.xyz/storage/images/kost/bvBL0jMTDW.jpeg'
        ];

        Mail::to('imam.septian11187@gmail.com')->send(new CobaMail($details));

        return response()->json([
            "code" => 200,
            "success" => TRUE,
            "message" => "email send",

        ]);
    }
}
