<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlySalesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        function getMonthNumber($date)
        {
            return Carbon::parse($date)->format('m');
        }

        function convertMonthNumberToMonthName($monthNumbers)
        {
            $monthNames = [];

            foreach ($monthNumbers as $month) {
                if ($month == '01') {
                    $monthNames[] = 'Jan';
                } elseif ($month == '02') {
                    $monthNames[] = 'Feb';
                } elseif ($month == '03') {
                    $monthNames[] = 'Mar';
                } elseif ($month == '04') {
                    $monthNames[] = 'Apr';
                } elseif ($month == '05') {
                    $monthNames[] = 'May';
                } elseif ($month == '06') {
                    $monthNames[] = 'Jun';
                } elseif ($month == '07') {
                    $monthNames[] = 'Jul';
                } elseif ($month == '08') {
                    $monthNames[] = 'Aug';
                } elseif ($month == '09') {
                    $monthNames[] = 'Sep';
                } elseif ($month == '10') {
                    $monthNames[] = 'Oct';
                } elseif ($month == '11') {
                    $monthNames[] = 'Nov';
                } elseif ($month == '12') {
                    $monthNames[] = 'Dec';
                } else {
                    $monthNames[] = 'Unknown';
                }
            }

            return $monthNames;
        }

        function calculateMonthlySales()
        {
            $monthlySales = [
                '01' => 0,
                '02' => 0,
                '03' => 0,
                '04' => 0,
                '05' => 0,
                '06' => 0,
                '07' => 0,
                '08' => 0,
                '09' => 0,
                '10' => 0,
                '11' => 0,
                '12' => 0
            ];

            foreach ($monthlySales as $month => &$sales) {
                $currentMonth = $month;
                $transactions = Transaction::select('total')
                ->whereMonth('created_at', $currentMonth)
                ->get();
                $sales = $transactions->sum('total');
            }

            return $monthlySales;
        }

        $monthlySales = calculateMonthlySales();
        
        return [
            'labels' => convertMonthNumberToMonthName(array_keys($monthlySales)),
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => array_values($monthlySales),
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
