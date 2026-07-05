<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SetupLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public int $step = 1,
        public int $totalSteps = 6,
    ) {}

    public function render(): View|Closure|string
    {
        return view('layouts.setup');
    }
}
