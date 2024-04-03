<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;

class Cashier extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static string $view = 'filament.pages.cashier';

    public function mount(): void
    {
        $current_transaction_exists = Session::has('current_transaction_id');
        
        if (! $current_transaction_exists) {
            $transaction = Transaction::create([
                'subtotal' => 0,
                'tax'=> 0,
                'discount'=> 0,
                'total'=> 0,
                'paid'=> 0,
                'change'=> 0,
                'transaction_date'=> now(),
            ]);

            Session::put('current_transaction_id', $transaction->id);
        }
    }
}
