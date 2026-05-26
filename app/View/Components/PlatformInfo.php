<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PlatformInfo extends Component
{
    public $name;
    public $image;
    public $displayName;
    public $initials;

    public function __construct($platform)
    {
        $this->displayName = $platform->name ?? $platform->title ?? ucwords(str_replace('_', ' ', $platform->key ?? '')) ?? 'N/A';
        $this->image = $platform->image ?? null;
        $this->name = $platform->name ?? null;

        if ($this->image) {
            if (file_exists(public_path($this->image))) {
                $this->initials = imageShow($this->image);
            }
        }
        if (empty($this->initials)) {
            $words = preg_split('/\s+/', trim($this->displayName));
            $initials = '';

            foreach ($words as $word) {
                $initials .= strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $word), 0, 1));
            }

            $this->initials = substr($initials, 0, 2);
            $this->image = null;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.platform-info', [
            'name' => $this->name,
            'image' => $this->image,
            'displayName' => $this->displayName,
            'initials' => $this->initials,
        ]);
    }
}
