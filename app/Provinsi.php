<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    protected $table = 'provinces';
    protected $guarded = ['id'];
    public $timestamps = false;
}
