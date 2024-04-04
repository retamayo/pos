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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;
use App\Models\Category;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Database\Eloquent\Builder;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static ?string $navigationLabel = 'Inventory';

    public function getHeader(): string
    {
        return 'Inventory';
    }

  

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
                Filter::make('stock1')
                    ->label('In Stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '>', 0)),

                Filter::make('stock2')
                    ->label('Low Stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<', 10)),

                Filter::make('stock3')
                    ->label('Out of Stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<=', 0)),
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
                //     Tables\Actions\Action::make('Generate'),
                // ]),
            ])
            ->headerActions([     
                Tables\Actions\Action::make('Generate')
                ->form([
                    Forms\Components\Placeholder::make('summary')
                    ->label('Summary')
                    ->content(function (): string {
                        return "Hello!";
                    }),
                    Forms\Components\Toggle::make('include_low_stock')
                    ->label('Include Low Stock')  
                ])
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

    public static function getBreadcrumb(): string
    {
        return 'Inventory';
    }
}
