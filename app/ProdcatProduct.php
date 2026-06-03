<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class ProdcatProduct extends Model
{
	protected $table = 'prodcat_product';
    protected $fillable = ['product_id','prodcat_id'];
}
