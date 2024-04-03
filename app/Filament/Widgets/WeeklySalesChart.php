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
    
        function convertDayNumberToDayName($dayNumbers)
        {
            $dayNames = [];

            foreach ($dayNumbers as $day) {
                switch ($day) {
                case '01':
                    $dayNames[] = 'Mon';
                    break;
                case '02':
                    $dayNames[] = 'Tue';
                    break;
                case '03':
                    $dayNames[] = 'Wed';
                    break;
                case '04':
                    $dayNames[] = 'Thu';
                    break;
                case '05':
                    $dayNames[] = 'Fri';
                    break;
                case '06':
                    $dayNames[] = 'Sat';
                    break;
                case '07':
                    $dayNames[] = 'Sun';
                    break;
                default:
                    $dayNames[] = 'Unknown';
                }
            }

            return $dayNames;
        }
    
        function calculateDailySales()
        {
            $dailySales = [
                '01' => 0,
                '02' => 0,
                '03' => 0,
                '04' => 0,
                '05' => 0,
                '06' => 0,
                '07' => 0,
            ];
            
            foreach ($dailySales as $day => &$sales) {
                $currentDay = $day;
                $transactions = Transaction::select('total')
                  ->whereDay('created_at', $day) // Filter by day number
                  ->whereMonth('created_at', Carbon::now()->format('m')) // Filter for current month
                  ->get();
                $dailySales[$currentDay] = $transactions->sum('total'); // Store sales for the day name
            }
            
            return $dailySales;
        }
    
        $dailySales = calculateDailySales();
    
        return [
            'labels' => convertDayNumberToDayName(array_keys($dailySales)),
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
