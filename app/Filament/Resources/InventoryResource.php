<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static ?string $navigationLabel = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('stock')
                ->numeric()
                ->minValue(0)
                ->inputMode('integer')
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->sortable()
                    ->label('Name'),
                Tables\Columns\TextColumn::make('stock')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.price')
                    ->sortable()
                    ->label('Price')
                    ->currency('PHP')
                    ->state(function (Inventory $record): float {
                        return  number_format(Product::find($record->product_id)->price, 2);
                    }),
                Tables\Columns\TextColumn::make('product.category.name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Edit')
                ->form([
                    Forms\Components\TextInput::make('stock')
                        ->label('Stock')
                        ->numeric()
                        ->minValue(0)
                        ->inputMode('integer')
                        ->required()
                        ->formatStateUsing(fn (Inventory $record) => $record->stock),
                ])
                ->action(function (array $data, Inventory $record): void {
                    $record->stock = $data['stock'];
                    $record->save();
                })
                ->icon('heroicon-o-pencil-square'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            // 'create' => Pages\CreateInventory::route('/create'),
            // 'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
