<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserInfo extends Component
{
    public $user;

    public $initials;

    public function __construct($user)
    {
        $this->user = $user;
        if ($user->image) {
            if (file_exists(public_path($user->image))) {
                $this->initials = imageShow($user->image);
            }
        }
        if (empty($this->initials)) {
            $this->initials = strtoupper(substr($user->name, 0, 1).substr($user->name, strpos($user->name, ' ') + 1, 1));
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.user-info', [
            'user' => $this->user,
            'initials' => $this->initials,
        ]);
    }
}
