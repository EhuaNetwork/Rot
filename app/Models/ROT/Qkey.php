<?php

namespace App\Models\ROT;

use Illuminate\Database\Eloquent\Model;

class Qkey extends Model
{
    protected $table = 'q_key';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $fillable = ['key','return','return_type'];
}
