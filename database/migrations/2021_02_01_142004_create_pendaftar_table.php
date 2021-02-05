<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendaftarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendaftar', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kost');
            $table->string('nama');
            $table->integer('kelamin');
            $table->dateTime('tanggal_lahir', 0);
            $table->integer('provinsi');
            $table->integer('kota');
            $table->string('alamat');
            $table->string('email');
            $table->string('notelp');
            $table->string('noktp');
            $table->string('foto_ktp');
            $table->string('foto_diri');
            $table->integer('status_hubungan');
            $table->integer('status_pekerjaan');
            $table->string('tempat_kerja_pendidikan')->nullable();
            $table->string('pesan')->nullable();
            $table->integer('request_kamar');
            $table->boolean('active')->default(TRUE);
            $table->dateTime('tanggal_daftar', 0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendaftar');
    }
}
