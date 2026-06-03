<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getImageAttribute ($value){
        if($this->nsfw){
            return $value;
        }
        return $value;
    }
}
