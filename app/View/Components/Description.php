<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Description extends Component
{
    public string $description;

    public function __construct(string $description = '')
    {
        $this->description = $description;
    }

    public function render(): View|Closure|string
    {
        return view('components.description', [
            'description' => $this->description,
        ]);
    }
}
