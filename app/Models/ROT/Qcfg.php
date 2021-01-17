<?php

namespace App\Models\ROT;

use Illuminate\Database\Eloquent\Model;

class Qcfg extends Model
{
    protected $table = 'q_cfg';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $fillable = ['content','state','type','t_id'];
}
