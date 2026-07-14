<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Postcat extends Model
{
    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
