<?php

namespace App\Models\ROT;

use Illuminate\Database\Eloquent\Model;

class Vcfg extends Model
{
    protected $table = 'v_cfg';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $fillable = ['content','state','type','t_id'];
}
