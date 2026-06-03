<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Prodcat extends Model
{
    public function childrens()
    {
        return $this->hasMany(Prodcat::class, 'parent_id');
    }
}
