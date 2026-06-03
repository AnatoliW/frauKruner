<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Dashboard extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $type;
    public $title;
    public $bread;
    public function __construct($type, $title, $bread)
    {
        $this->type = $type;
        $this->title = $title;
        $this->bread = $bread;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('layouts.dashboard');
    }
}
