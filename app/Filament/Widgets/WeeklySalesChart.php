<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class WeeklySalesChart extends ChartWidget
{
    protected static ?string $heading = 'Weekly Sales';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
