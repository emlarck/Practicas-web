<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GradeBadge extends Component
{
    public function __construct(
        public string $equivalence
    ) {}

    public function badgeClass(): string
    {
        return match (strtoupper($this->equivalence)) {
            'AU' => 'bg-success',
            'DE' => 'bg-primary',
            'SA' => 'bg-info text-dark',
            default => 'bg-danger', // NA
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.grade-badge');
    }
}