<?php

namespace Faed\Doc\models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    protected $guarded=[];


    public function params()
    {
        return $this->hasMany(Param::class);
    }

    public function scopeGroupId(Builder $builder,$value)
    {
        return $builder->when($value,function (Builder $builder,$value){
            $builder->where('group_id',$value);
        });
    }
}
