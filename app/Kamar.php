<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    protected $hidden = ['created_at','updated_at'];
    protected $fillable = ['*'];
}
