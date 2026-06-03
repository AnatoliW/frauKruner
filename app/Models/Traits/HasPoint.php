<?php

namespace App\Models\Traits;

use App\Models\Point;

trait HasPoint
{

    public function point()
    {
        return $this->morphOne(Point::class, 'pointable');
    }

    public function addPoint($point)
    {
        if (!$this->point) {
            $this->point()->create(['points' => $point]);
        } else {
            $this->point->increment('points', $point);
        }
    }
    

    
    
}
