<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $title = 'Cashier';

    // edit breadcrumbs

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.pos.resources.transactions.index') => TransactionResource::getNavigationLabel(),
            route('filament.pos.resources.transactions.create') => 'Cashier',
            
        ];
    }

}
