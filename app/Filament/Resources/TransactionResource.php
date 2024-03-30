<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Route;
use App\Models\Product;
use Filament\Forms\Set;
use Illuminate\Support\Facades\DB;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                ->description('Create a new transaction')
                ->schema([
                    Forms\Components\Repeater::make('products')
                        ->schema([
                            Forms\Components\Select::make('product')
                            ->options(function () {
                                $options = DB::select("SELECT p.id, p.name FROM products AS p INNER JOIN inventories AS i ON p.id = i.product_id WHERE i.stock > 0");
                                $opt = [];
                                foreach ($options as $option) {
                                    $opt[$option->id] = $option->name;
                                }
                                return $opt;
                            })
                            ->searchable()
                            ->live(debounce: 500)
                            ->required(),
                            Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->minValue(1)
                            ->inputMode('integer')
                            ->live(debounce: 500)
                            ->required(),
                        ])
                        ->afterStateUpdated(function ($state, Set $set) {
                            
                            $total = 0;
                            $items = '';
                            $products_sold = 0;

                            foreach ($state as $key => $value) {
                                if (isset($state[$key]['product']) && isset($state[$key]['quantity'])) {
                                    $product = Product::find($state[$key]['product']);
                                    $quantity = $state[$key]['quantity'];
                                    $products_sold = $products_sold + $quantity;
                                    if ($state[$key]['quantity'] === '') {
                                        $quantity = 0;
                                    } 
                                    $total += $product->price * $quantity;
                                    $items .= 'Product: ' . $product->name . PHP_EOL;
                                    $items .= 'Quantity: ' . $quantity . PHP_EOL;
                                    $items .= 'Price: ' . $product->price * $quantity . PHP_EOL;
                                    $items .= '__________________________________________________________'. PHP_EOL;
                                }
                            }

                            $set('breakdown', $items);
                            $set('total', $total);
                            $set('products_sold', $products_sold);
                            
                        })
                        ->columns(2)->columnSpanFull(),
                ])->columns(2)->columnSpan(7),
                Forms\Components\Section::make('Transaction Breakdown')
                ->description('Detailed breakdown of the transaction')
                ->schema([
                    Forms\Components\TextArea::make('breakdown')
                    ->rows(8)
                    ->autosize()
                    ->readonly()
                    ->columnSpanFull(),
                    Forms\Components\TextInput::make('total')
                    ->numeric()
                    ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 2)
                    ->columnSpanFull(),
                    Forms\Components\Hidden::make('transaction_date')
                    ->default(now()->format('Y-m-d H:i:s')),
                    Forms\Components\Hidden::make('products_sold'),
                ])->columns(2)->columnSpan(5),
            ])->columns(12);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('products_sold')
                ->label('Products Sold')
                ->suffix(' Products'),
                Tables\Columns\TextColumn::make('total')
                ->currency('PHP'),
                Tables\Columns\TextColumn::make('transaction_date'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/cashier'),
            // 'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function canUpdate(): bool
    {
        return false;
    }
}
