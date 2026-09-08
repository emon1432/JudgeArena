<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tags extends Component
{
    public array $tags;

    public array $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];

    public function __construct(string $tags)
    {
        $this->tags = explode(', ', $tags);
    }

    public function render(): View|Closure|string
    {
        return view('components.tags', [
            'tags' => $this->tags,
            'color' => $this->colors[array_rand($this->colors)],
        ]);
    }
}
