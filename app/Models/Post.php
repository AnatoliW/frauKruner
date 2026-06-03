<?php

namespace App\Models;

use App\Postcat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Post extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Postcat::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopeFilter($query)
    {
        $query->when(request('category') == !null, function ($q) {
            return $q->whereHas('category', function ($query) {
                $query->where('slug', request()->category);
            });
        });
    }
}
