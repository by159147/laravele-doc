<?php

namespace Faed\Doc\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function apis()
    {
        return $this->hasMany(Api::class);
    }
}
