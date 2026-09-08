<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormActionButton extends Component
{
    public string $resource;

    public string $action;

    public string $type;

    public function __construct($resource, $action, $type)
    {
        $this->resource = $resource;
        $this->action = $action;
        $this->type = $type;
    }

    public function render(): View|Closure|string
    {
        return view('components.form-action-button', [
            'resource' => $this->resource,
            'action' => $this->action,
            'type' => $this->type,
        ]);
    }
}
