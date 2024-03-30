<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Inventory;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $title = 'Cashier';

    // edit breadcrumbs
    protected function handleRecordCreation(array $data): Transaction
    {
        $record =  static::getModel()::create($data);

        foreach ($data['products'] as $key => $value) {
            $product = Product::find($data['products'][$key]['product']);
            $inventory = Inventory::where('product_id', $product->id)->first();

            $product->sale_count = $data['products'][$key]['quantity'];

            if ($inventory->stock > $data['products'][$key]['quantity']) {
                $inventory->stock = $inventory->stock - $data['products'][$key]['quantity'];
            } else {
                $inventory->stock = 0;
            }

            $product->save();
            $inventory->save();
        }

        return $record;
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.pos.resources.transactions.index') => 'Sales',
            route('filament.pos.resources.transactions.create') => 'Cashier',
        ];
    }

}
