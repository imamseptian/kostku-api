<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableKosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kosts', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->integer('provinsi');
            $table->integer('kota');
            $table->string('alamat');
            $table->string('notelp');
            $table->string('foto_kost')->default('default.png');
            $table->longText('deskripsi');
            $table->integer('owner');
            $table->integer('jenis');
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
        Schema::dropIfExists('kosts');
    }
}
