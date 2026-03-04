<?php

namespace App\View\Components;

use App\Models\Contest;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ContestInfo extends Component
{
    public string $name;
    public ?string $code;
    public bool $isRated;
    public array $tags;
    public ?string $url;

    public function __construct(?Contest $contest = null)
    {
        if (!$contest) {
            $this->name = '-';
            $this->code = null;
            $this->url = null;
            $this->isRated = false;
            $this->tags = [];
            return;
        }

        $this->name = $contest->name;
        $this->code = $contest->platform_contest_id ? (string) $contest->platform_contest_id : null;
        $this->url = filter_var($contest->url, FILTER_VALIDATE_URL) ? $contest->url : null;
        $this->isRated = (bool) $contest->is_rated;

        $tags = $contest->tags ?? [];
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
        }

        $this->tags = array_values(array_filter(array_map(function ($tag) {
            return is_string($tag) ? trim($tag) : null;
        }, is_array($tags) ? $tags : [])));
    }

    public function render(): View|Closure|string
    {
        return view('components.contest-info', [
            'name' => $this->name,
            'code' => $this->code,
            'isRated' => $this->isRated,
            'tags' => $this->tags,
            'url' => $this->url,
        ]);
    }
}
