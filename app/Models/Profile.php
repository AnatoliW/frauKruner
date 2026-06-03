<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class Profile extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected static function booted(): void
    {
        static::created(function (Profile $profile) {
            if ($profile->profile_img && Storage::exists($profile->profile_img)) {
                $profile->user->addPoint(10);
            }
        });

        static::updating(function (Profile $profile) {
            $originalImage = $profile->getOriginal('profile_img');
            $currentImage = $profile->profile_img;
            if (is_null($originalImage) && $currentImage && Storage::exists($currentImage)) {
                $profile->user->addPoint(10);
            }
        });
    }

    public function imgUrl(): string
    {
        if ($this->profile_img) {
            return media_url($this->profile_img);
        }

        return asset('assets/img/user.png');
    }
}

