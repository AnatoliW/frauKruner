<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Notification extends Model
{
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    public function scopeForMe($query)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $userRoleId = $user->role_id;
            $query->where('user_id', $user->id)
                ->orWhere(function ($q) use ($user, $userRoleId) {
                    $q->where('role', $userRoleId);
                    // if ($user->verified == 0) {
                    //     $q->where('verified', true);
                    // } 
                });
        }

        return $query;
    }
}
