<?php

namespace App\View\Components;

use App\Models\Problem;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProblemInfo extends Component
{
    public string $name;
    public ?string $code;
    public bool $isPremium;
    public array $tags;
    public ?string $url;

    public function __construct(Problem $problem)
    {
        $this->name = $problem->name;
        $this->code = $problem->code;
        $this->url = filter_var($problem->url, FILTER_VALIDATE_URL) ? $problem->url : null;
        $this->isPremium = (bool) $problem->is_premium;

        $tags = $problem->tags ?? [];
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
        }

        $this->tags = array_values(array_filter(array_map(function ($tag) {
            return is_string($tag) ? trim($tag) : null;
        }, is_array($tags) ? $tags : [])));
    }

    public function render(): View|Closure|string
    {
        return view('components.problem-info', [
            'name' => $this->name,
            'code' => $this->code,
            'isPremium' => $this->isPremium,
            'tags' => $this->tags,
            'url' => $this->url,
        ]);
    }
}
