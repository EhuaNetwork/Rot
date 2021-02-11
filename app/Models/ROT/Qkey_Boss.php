<?php

namespace App\Models\ROT;

use Illuminate\Database\Eloquent\Model;

class Qkey_Boss extends Model
{
    protected $table = 'q_key_boss';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $fillable = ['key','return','return_type'];
}
