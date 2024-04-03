<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WeeklySalesChart extends ChartWidget
{
    protected static ?string $heading = 'Weekly Sales';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        function getDayNumber($date)
        {
            return Carbon::parse($date)->format('w'); // Use 'w' for day of week (0-6)
        }
    
        function convertDayNumberToDayString($dayNumbers)
        {
            $dayStrings = [];
            $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
            foreach ($dayNumbers as $day) {
                $dayStrings[] = $days[$day];
            }
    
            return $dayStrings;
        }
    
        function calculateDailySales()
        {
            $dailySales = [];
    
            // Adjust query to group by day of week
            $transactions = Transaction::select('total', DB::raw('strftime("%w", created_at) as day_number'))
                ->groupBy(DB::raw('strftime("%w", created_at)'))
                ->get();
    
            foreach ($transactions as $transaction) {
                $dayNumber = getDayNumber($transaction->created_at);
                $dailySales[$dayNumber] = isset($dailySales[$dayNumber]) ? $dailySales[$dayNumber] + $transaction->total : $transaction->total;
            }
    
            // Handle missing days (if no transactions on a specific day)
            for ($day = 0; $day < 7; $day++) {
                if (!isset($dailySales[$day])) {
                    $dailySales[$day] = 0;
                }
            }
    
            return $dailySales;
        }
    
        $dailySales = calculateDailySales();
    
        return [
            'labels' => convertDayNumberToDayString(array_keys($dailySales)),
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => array_values($dailySales),
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
