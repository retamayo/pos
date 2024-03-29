<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Category;
use App\Models\Inventory;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Details')
                ->description('Create a new product')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->inputMode('decimal'),
                    Forms\Components\TextInput::make('stock')
                        ->required()
                        ->numeric()
                        ->inputMode('integer'),
                    Forms\Components\Select::make('category_id')
                        ->required()
                        ->label('Category')
                        ->options(Category::all()->pluck('name', 'id'))
                        ->searchable(),
                    Forms\Components\TextInput::make('description')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->sortable()
                    ->label('Price (PHP)')
                    ->state(function (Product $record): float {
                        return  number_format($record->price, 2);
                    }),
                Tables\Columns\TextColumn::make('stock')
                    ->state(function (Product $record): string {
                        return Inventory::where('product_id', $record->id)->first()->sum('stock');
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    // decrementing category products_count when bulk deleting
                        ->before(function ($records) {
                            $category = Category::find($records->first()->category_id);
                            if ($category->products_count > 0) {
                                $category->products_count = $category->products_count - $records->count();
                                $category->save();
                            }
                        })
                ]),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
