<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActionBy extends Component
{
    public $user;

    public function __construct($actionBy)
    {
        $this->user = $actionBy;
    }

    public function render(): View|Closure|string
    {
        return view('components.action-by', [
            'user' => $this->user,
        ]);
    }
}
