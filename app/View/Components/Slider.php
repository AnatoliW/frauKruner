<?php

namespace App\View\Components;

use App\Slider as SliderModel;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class Slider extends Component
{
    public Collection $sliders;

    public function __construct()
    {
        $this->sliders = SliderModel::query()->get();
    }

    public function render(): View
    {
        return view('components.slider', [
            'sliders' => $this->sliders,
        ]);
    }
}
