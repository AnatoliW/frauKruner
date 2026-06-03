<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Attribute extends Model
{
    protected $guarded = [];
    public function setValueAttribute($value)
    {
      $this->attributes['value'] = json_encode(explode(',', $value));
    }

    public function getValueAttribute($value)
    {
      if ($value) {
        return json_decode($value);
      }
    }
}
