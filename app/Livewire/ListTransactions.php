<?php

namespace App\Livewire;

use App\Models\Transaction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class ListTransactions extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected $listeners = ['updateTransaction' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::query()->where('subtotal', '>', 0))
            ->columns([
                TextColumn::make('transaction_date')
                    ->formatStateUsing(fn ($state) => date('F j, Y | g:i A', strtotime($state)))
                    ->searchable()
                    ->sortable()
                    ->label('Transaction Date'),
                TextColumn::make('subtotal')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '₱' . number_format($state, 2)),
                TextColumn::make('tax')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' %')
                    ->badge(),
                TextColumn::make('discount')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' %')
                    ->badge(),
                TextColumn::make('paid')
                    ->label('Amount Paid')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '₱' . number_format($state, 2)),
                TextColumn::make('total')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '₱' . number_format($state, 2)),
                TextColumn::make('change')
                    ->sortable()->formatStateUsing(fn ($state) => '₱' . number_format($state, 2)),
            ])
            ->filters([
                DateRangeFilter::make('transaction_date')
                ->label('Transaction Date'),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                // ...
            ])
            // ->poll('.1s')
            ->striped();
    }

    public function render()
    {
        return view('livewire.list-transactions');
    }
}
