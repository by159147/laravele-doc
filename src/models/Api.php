<?php

namespace Faed\Doc\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    protected $guarded=[];


    public function params()
    {
        return $this->hasMany(Param::class);
    }
}
