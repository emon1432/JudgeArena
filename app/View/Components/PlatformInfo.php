<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PlatformInfo extends Component
{
    public $name;
    public $slug;
    public $shortName;
    public $icon;
    public $initials;

    public function __construct($platform)
    {
        $this->name = $platform->name ?? 'N/A';
        $this->slug = $platform->slug ?? null;
        $this->shortName = $platform->short_name ?? $this->name;
        $this->icon = $platform->icon ?? null;
        $this->initials = $platform->initials ?? null;

        if ($this->icon) {
            if (file_exists(public_path($this->icon))) {
                $this->initials = imageShow($this->icon);
            }
        }
        if (empty($this->initials)) {
            if ($this->shortName && strlen($this->shortName) <= 2) {
                $this->initials = strtoupper($this->shortName);
            } else {
                $words = preg_split('/\s+/', trim($this->name));
                $initials = '';

                foreach ($words as $word) {
                    $initials .= strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $word), 0, 1));
                }
                $this->initials = substr($initials, 0, 2);
            }
            $this->icon = null;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.platform-info', [
            'name' => $this->name,
            'slug' => $this->slug,
            'shortName' => $this->shortName,
            'icon' => $this->icon,
            'initials' => $this->initials,
        ]);
    }
}
