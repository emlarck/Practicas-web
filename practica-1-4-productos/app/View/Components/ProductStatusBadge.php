<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductStatusBadge extends Component
{
    public function __construct(
        public string $status
    ) {}

    public function badgeClass(): string
    {
        return match ($this->status) {
            'Disponible' => 'bg-success',
            'Agotado'    => 'bg-danger',
            default      => 'bg-secondary',
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.product-status-badge');
    }
}