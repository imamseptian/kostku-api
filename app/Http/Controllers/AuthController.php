<?php

namespace App\Http\Controllers;

use App\ClassKamar;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Kost;
use App\Penghuni;
use Auth;
use Facade\FlareClient\Flare;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class AuthController extends Controller
{
    public function signup(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'nama' => 'required',
                'email' => 'required|unique:users|email:rfc,dns',
                'password' => 'required|string|min:8|confirmed',
                'tanggal_lahir' => 'required|date|before:-18 years'
            ],
            [
                'nama.required' => 'Nama Perlu diisi',
                'email.required' => 'Email Perlu Diisi',
                'email.email' => 'Format email tidak sesuai',
                'email.unique' => 'Maaf alamat email sudah terdaftar, silahkan masukan alamat email lain',
                'password.required' => 'Password perlu diisi',
                'password.confirmed' => 'Konfirmasi Password tidak cocok',
                'password.min' => 'Panjang password minimal 8 digit',
                'tanggal_lahir.required' => 'Tanggal lahir perlu diisi',
                'tanggal_lahir.date' => 'Invalid Format',
                'tanggal_lahir.before' => 'Harus berumur minimal 18 tahun untuk dapat mendaftar',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'success' => FALSE, "message" => "ada error", 'errors' => $validator->errors()->messages()
            ]);
        }

        // $request->validate([
        //     'name' => 'required',
        //     'email' => 'required|string|unique:users',
        //     'password' => 'required|string|confirmed'
        // ]);
        if (request()->has('foto_profil')) {
            $image_64 = $request->foto_profil; //your base64 encoded data

            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);

            //   find substring fro replace here eg: data:image/png;base64,

            $image = str_replace($replace, '', $image_64);

            $image = str_replace(' ', '+', $image);

            $imageName = Str::random(10) . '.' . $extension;

            $thumbnailImage = Image::make($image_64);
            $thumbnailImage->stream(); // <-- Key point
            Storage::disk('local')->put('public/images/users/' . $imageName, $thumbnailImage);

            $tanggal_lahirku = Carbon::parse($request->tanggal_lahir);

            $user = new User([
                'nama' => $request->nama,
                'email' => $request->email,
                'foto_profil' => $imageName,
                'password' => bcrypt($request->password),
                'tanggal_lahir' => $tanggal_lahirku
            ]);

            $user->save();

            return response()->json([
                'code' => 200,
                'success' => TRUE,
                'message' => 'Successfully created user with photo!',
                'user' => $user,
            ], 201);
        }
        $tanggal_daftar = Carbon::parse($request->tanggal_lahir);
        $user = new User([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'tanggal_lahir' => $tanggal_daftar
        ]);

        $user->save();

        return response()->json([
            'code' => 200,
            'success' => TRUE,
            'message' => 'Successfully created user!',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email:rfc,dns',
                'password' => 'required',
            ],
            [
                'email.required' => 'Email Perlu diisi',
                'email.email' => 'Format email salah',
                'password.required' => 'Password perlu diisi'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                "code" => 401,
                "success" => FALSE,
                "message" => "Unauthorized",
                'errors' => $validator->errors()->messages()
            ]);
        }

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {

            $pemilik = User::where('email', $request->email)->first();
            if ($pemilik) {
                $error = array(
                    'password' => ["Password Salah"],
                );
            } else {
                $error = array(
                    'email' => ["Email tidak terdaftar"],
                );
            }

            return response()->json([
                'code' => 401,
                'success' => FALSE,
                'message' => 'Unauthorized',
                'errors' => $error
            ]);
        }

        // return response()->json([
        //     "code" => 200,
        //     "success" => TRUE,
        //     "message" => "Login"
        // ], 200);


        // $request->validate([
        //     'email' => 'required|string|email',
        //     'password' => 'required|string',
        //     'remember_me' => 'boolean'
        // ]);
        // $credentials = request(['email', 'password']);
        // if (!Auth::attempt($credentials))
        //     return response()->json([
        //         'code' => 401,
        //         'success' => FALSE,
        //         'message' => 'Unauthorized'
        //     ], 401);


        $user = $request->user();
        $kost = Kost::where('owner', $user['id'])->first();
        if ($kost) {
            $user['kostku'] = $kost['id'];
            $user['namakost'] = $kost['nama'];
        } else {
            $user['kostku'] = 0;
            $user['namakost'] = '';
        }
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addDays(1);
        $token->save();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'success' => TRUE,
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $dataUser = $request->user();
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out',
            'user' => $dataUser
        ]);
    }

    public function checkStatus(Request $request)
    {
        if (Auth::guard('api')->check()) {

            return response()->json([
                'status' => 'logged in'
            ]);
        }

        // alternative method
        if (($user = Auth::user()) !== null) {
            // Here you have your authenticated user model
            return response()->json([
                'message' => 'Sudah Logut'
            ]);
        }

        // return general data
        return response()->json([
            'status' => 'logged out'
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $kost = Kost::where('owner', $user['id'])->first();
        if ($kost) {
            $user['kostku'] = $kost['id'];
            $user['namakost'] = $kost['nama'];
        } else {
            $user['kostku'] = 0;
            $user['namakost'] = '';
        }
        // return response()->json($request->user());
        return response()->json($user);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $kost = Kost::where('owner', $user['id'])->first();
        $kamarku = DB::table('kosts')
            ->join('provinces', 'kosts.provinsi', '=', 'provinces.id')
            ->join('regencies', 'kosts.kota', '=', 'regencies.id')
            ->select('kosts.*', 'regencies.name as nama_kota', 'provinces.name as nama_provinsi')
            ->first();



        // $kamarku = DB::table('penghuni')
        //     ->join('kamars', 'penghuni.kamar', '=', 'kamars.id')
        //     ->join('class_kamar', 'kamars.kelas', '=', 'class_kamar.id')
        //     ->select('penghuni.*', 'kamars.id as id_kamar', 'kamars.nama as nama_kamar', 'class_kamar.harga as harga_kamar')
        //     ->get();

        if ($kost) {
            $jenisku = ClassKamar::where('id_kost', $kost['id'])->where('active', TRUE)->get();
            $kamarku = DB::table('kamars')
                ->leftJoin('class_kamar', 'kamars.id_kelas', '=', 'class_kamar.id')
                ->select('kamars.*', 'class_kamar.nama as nama_kelas')
                ->where('class_kamar.id_kost', $kost['id'])
                ->get();

            $penghuniku = Penghuni::where('id_kost', $kost['id'])->where('active', TRUE)->get();
            return response()->json([
                'code' => 200,
                'success' => TRUE,
                'message' => 'Profil ditemukan',
                'user' => $user,
                'kost' => $kost,
                'jmlkamar' => count($kamarku),
                'penghuni' => count($penghuniku),
                'kelas' => count($jenisku)
            ]);
        }
        return response()->json([
            'code' => 404,
            'success' => FALSE,
            'message' => 'Profil tidak ditemukan'
        ]);
        // return response()->json($request->user());
        return response()->json($user);
    }

    public function editProfil(Request $request)
    {
        $user = $request->user();
        $data_user = User::where('id', $user['id'])->first();
        $data_user->nama = $request->nama ? $request->nama : $data_user->nama;
        // $data_user->save();

        $imageName = $data_user->foto_profil;

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
            Storage::disk('local')->delete('public/images/users/' . $data_user->foto_profil);
            Storage::disk('local')->put('public/images/users/' . $imageName, $thumbnailImage);
        }
        $data_user->foto_profil = $imageName;
        $data_user->save();

        $kost = Kost::where('owner', $user['id'])->first();

        $data_user['kostku'] = $kost['id'];
        $data_user['namakost'] = $kost['nama'];

        return response()->json([
            'code' => 200,
            'success' => true,
            'message' => 'Berhasil',
            'foto_baru' => $imageName,
            'user' => $data_user,
            'oldpic' => $request->foto_profil

        ]);
        // return response()->json($request->user());
        // return response()->json($user);
    }
}
