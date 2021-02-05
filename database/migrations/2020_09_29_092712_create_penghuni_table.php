<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenghuniTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penghuni', function (Blueprint $table) {
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
            $table->integer('id_kamar');
            $table->string('tempat_kerja_pendidikan')->nullable();
            $table->boolean('active')->default(TRUE);
            $table->dateTime('tanggal_masuk', 0);
            $table->dateTime('tanggal_keluar', 0)->nullable();
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
        Schema::dropIfExists('penghuni');
    }
}
