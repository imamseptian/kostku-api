<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class CreateClassKamarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_kamar', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kost');
            $table->string('nama');
            $table->integer('harga');
            $table->integer('kapasitas');
            $table->longText('deskripsi');
            $table->boolean('active')->default(TRUE);
            $table->string('foto')->nullable();
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
        Schema::dropIfExists('class_kamar');
    }
}
