<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Item;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $best_product = Product::where('sale_count', '>', 0)->orderBy('sale_count', 'desc')->first();
        $best_product_name = $best_product ? $best_product->name : 'No product sold yet';
        $best_product_sale_count = $best_product ? "Sold $best_product->sale_count times" : "Sold 0 times";

        $total_products_sold_today = Item::whereDate('created_at', today())->sum('quantity');
        $total_products_sold_yesterday = Item::whereDate('created_at', today()->subDay())->sum('quantity');
        $total_products_sold_comparison = number_format($total_products_sold_today - $total_products_sold_yesterday);
        $total_products_description = $total_products_sold_comparison > 0 ? "$total_products_sold_comparison increase since yesterday" : "$total_products_sold_comparison decrease since yesterday";
        $total_products_icon = $total_products_sold_today > $total_products_sold_yesterday ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        $sales_today = Transaction::whereDate('created_at', today())->sum('total');
        $sales_yesterday = Transaction::whereDate('created_at', today()->subDay())->sum('total');
        $sales_comparison = number_format($sales_today - $sales_yesterday, 2);
        $sales_description = $sales_comparison > 0 ? "$sales_comparison increase since yesterday" : "$sales_comparison decrease since yesterday";
        $sales_icon = $sales_today > $sales_yesterday ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        
        return [
            Stat::make('Daily Sales', '₱' . number_format($sales_today, 2))
            ->description("₱" . $sales_description)
            ->descriptionIcon($sales_icon),
            Stat::make('Products Sold', $total_products_sold_today . ' products')
                ->description($total_products_description)
                ->descriptionIcon($total_products_icon),
            Stat::make('Best Product', $best_product_name)
                ->description($best_product_sale_count)
                ->descriptionIcon('heroicon-m-chart-pie'),
        ];
    }
}
