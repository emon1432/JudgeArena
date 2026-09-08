<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Actions extends Component
{
    public $actions;

    public $model;

    public $route;

    public function __construct($actions)
    {
        $this->actions = $actions;
    }

    public function render(): View|Closure|string
    {
        return view('components.actions', [
            'actions' => $this->actions,
        ]);
    }
}
