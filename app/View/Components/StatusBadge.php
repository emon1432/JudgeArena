<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatusBadge extends Component
{
    public string $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function render()
    {
        return view('components.status-badge', [
            'status' => $this->status,
            'color' => $this->color(),
        ]);
    }

    public function color(): string
    {
        return match ($this->status) {
            'Active' => 'success',
            'Inactive' => 'danger',
            'Maintenance' => 'warning',
            'Coming Soon' => 'info',
            'Pending' => 'warning',
            'Created' => 'success',
            'Updated' => 'info',
            'Deleted' => 'warning',
            'Restored' => 'primary',
            'Permanently Deleted' => 'danger',
            default => 'secondary',
        };
    }
}
