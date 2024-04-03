<?php

namespace App\Livewire;

use App\Models\Item;
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

class ListItems extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected $listeners = ['updateTransaction' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->query(Item::query()->where("transaction_id", Session::get("current_transaction_id")))
            ->columns([
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->searchable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                DeleteAction::make('delete')
                    ->action(function (Item $record) {
                        $record->delete();
                        Notification::make()
                        ->title('Item deleted successfully')
                        ->success()
                        ->send();
                        $this->dispatch('updateTransaction');
                    }),
            ])
            ->bulkActions([
                // ...
            ])
            // ->poll('.1s')
            ->striped();
    }

    public function render()
    {
        return view('livewire.list-items');
    }
}
