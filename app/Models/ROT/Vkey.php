<?php

namespace App\Models\ROT;

use Illuminate\Database\Eloquent\Model;

class Vkey extends Model
{
    protected $table = 'v_key';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $fillable = ['key','return','return_type'];
}
