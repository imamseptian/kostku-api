<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarangTambahanPenghuniTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang_tambahan_penghuni', function (Blueprint $table) {
            $table->id();
            $table->integer('id_penghuni');
            $table->integer('id_barang');
            $table->integer('qty');
            $table->integer('total');
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
        Schema::dropIfExists('barang_tambahan_penghuni');
    }
}
