<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarangTambahanPendaftarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang_tambahan_pendaftar', function (Blueprint $table) {
            $table->id();
            $table->integer('id_pendaftar');
            $table->integer('id_barang');
            $table->integer('qty');
            $table->boolean('active')->default(TRUE);
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
        Schema::dropIfExists('barang_tambahan_pendaftar');
    }
}
