<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kota extends Model
{
    protected $table = 'regencies';
    protected $guarded = ['id'];
    public $timestamps = false;
}
