<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Pages\Actions\Modal\Actions\ButtonAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Forms\Get;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;

class CreateTransaction extends Component implements HasForms
{
    use InteractsWithForms;
    
    public ?array $data = [];

    protected $listeners = ['updateTransaction' => '$refresh'];

    protected $currentId;
    
    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {   
        $this->currentId = Session::get("current_transaction_id");

        return $form
            ->schema([
                Section::make("Transaction Controls")
                ->description("Create a new transaction")
                ->schema([
                    Actions::make([
                        Actions\Action::make('Scan')
                        ->size(ActionSize::Large)
                        ->extraAttributes([
                            'style' => 'width: 100%;',
                        ]),
                        Actions\Action::make('Receipt')
                        ->size(ActionSize::Large)
                        ->extraAttributes([
                            'style' => 'width: 100%;',
                        ]),
                    ])->columnSpan(1),
                    Actions::make([
                        Actions\Action::make('Add')
                        ->size(ActionSize::Large)
                        ->extraAttributes([
                            'style' => 'width: 100%;',
                        ])
                        ->form([
                            Section::make('Add new product')
                            ->description('Add a new product to the transaction')
                            ->schema([
                                Hidden::make('transaction_id')
                                ->default($this->currentId),
                                Select::make('product_id')
                                ->label('Product')
                                ->required()
                                ->searchable()
                                ->options(Product::all()->pluck('name', 'id')),
                                TextInput::make('quantity')
                                ->numeric()
                                ->inputMode('integer')
                                ->label('Quantity')
                                ->default(1)
                                ->required(),
                            ])->columns(2),
                        ])
                        ->action(function (array $data) {
                            $itemExists = Item::where('transaction_id', $this->currentId)->where('product_id', $data['product_id'])->exists();
                            $product = Product::where('id', $data['product_id'])->first();
                            if ($itemExists) {
                                $item = Item::where('transaction_id', $this->currentId)->where('product_id', $data['product_id'])->first();
                                $item->quantity = $item->quantity + $data['quantity'];
                                $item->item_total = $data['quantity'] + $item->quantity  * $product->price;
                                $item->save();
                            } else {
                                $data['item_total'] = $data['quantity'] * $product->price;
                                Item::create($data);
                            }

                            $this->dispatch('updateTransaction');
                            Notification::make()
                            ->title('Product added to transaction')
                            ->success()
                            ->send();
                        }),
                        Actions\Action::make('Modifier')
                        ->size(ActionSize::Large)
                        ->form([
                            Section::make('Discount and Tax')
                            ->description('Modify discount and tax rates')
                            ->schema([
                                TextInput::make('tax')
                                ->label('Tax')
                                ->required()
                                ->numeric()
                                ->default(Session::get('current_transaction_tax') ?? 0)
                                ->inputMode('decimal'),
                                TextInput::make('discount')
                                ->label('Discount')
                                ->required()
                                ->numeric()
                                ->default(Session::get('current_transaction_discount') ?? 0)
                                ->inputMode('decimal'),
                            ])->columns(2),
                        ])
                        ->action(function (array $data): void {
                            Session::remove('current_transaction_tax');
                            Session::remove('current_transaction_discount');
                            Session::put('current_transaction_tax', $data['tax']);
                            Session::put('current_transaction_discount', $data['discount']);
                        })
                        ->extraAttributes([
                            'style' => 'width: 100%;',
                        ]),
                    ])->columnSpan(1),
                ])->columns(2),
                Section::make("Transaction Details")
                ->description("Summary of the transaction")
                ->schema([
                    Hidden::make('transaction_id')
                    ->default(Session::get('current_transaction_id')),
                    Placeholder::make('subtotal')
             
                    ->content(function () {
                        return '₱' . number_format(Item::where('transaction_id', $this->currentId)->sum('item_total'), 2);
                    })
                    ->default(0),
                    Placeholder::make('tax')
                    ->default(0)
                    ->content(function () {
                        $tax = Session::get('current_transaction_tax') ?? 0;
                        return $tax . ' %';
                    }),
                    Placeholder::make('discount')
                    ->default(0)
                    ->content(function () {
                        $discount = Session::get('current_transaction_discount') ?? 0;
                        return $discount . ' %';
                    }),
                    Placeholder::make('total')
                    ->columnSpanFull()
                    ->default(0)
                    ->content(function () {
                        $subtotal = Item::where('transaction_id', $this->currentId)->sum('item_total');
                        $tax = $subtotal * Session::get('current_transaction_tax') / 100;
                        $discount = $subtotal * Session::get('current_transaction_discount') / 100;
                        return '₱' . number_format($subtotal - $discount + $tax, 2);
                    }),
                    Actions::make([
                        Actions\Action::make('Checkout')
                            ->size(ActionSize::Large)
                            ->form([
                               Section::make('Payment')
                               ->description('Enter payment details')
                               ->schema([
                                    TextInput::make('paid')
                                    ->required()
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->label('Amount Paid')
                                    ->live(debounce: 500)
                                    ->columnSpanFull(),
                                    Placeholder::make('total')
                                    ->content(function () {
                                        $subtotal = Item::where('transaction_id', $this->currentId)->sum('item_total');
                                        $tax = $subtotal * Session::get('current_transaction_tax') / 100;
                                        $discount = $subtotal * Session::get('current_transaction_discount') / 100;
                                        return '₱' . number_format($subtotal - $discount + $tax, 2);
                                    }),
                                    Placeholder::make('change')
                                    ->content(function (Get $get) {
                                        $subtotal = Item::where('transaction_id', $this->currentId)->sum('item_total');
                                        $tax = $subtotal * Session::get('current_transaction_tax') / 100;
                                        $discount = $subtotal * Session::get('current_transaction_discount') / 100;
                                        $total = $subtotal - $discount + $tax;
                                        
                                        return $get('paid') > $total ? '₱' . number_format($get('paid') - $total, 2) : '₱0.00';
                                    }),
                               ])->columns(2),
                            ])
                            ->action(function (array $data): void {
                                $subtotal = Item::where('transaction_id', $this->currentId)->sum('item_total');
                                $tax = $subtotal * Session::get('current_transaction_tax') / 100;
                                $discount = $subtotal * Session::get('current_transaction_discount') / 100;
                                $data['total'] = $subtotal - $discount + $tax;
                                if($data['paid'] < $data['total']){
                                    $data['change'] = 0;
                                } else {    
                                    $data['change'] = $data['paid'] - $data['total'];
                                }
                                $data['subtotal'] = $subtotal;
                                $data['tax'] = $tax;
                                $data['discount'] = $discount;

                                $transaction = Transaction::find($this->currentId);
                                $transaction->update($data);

                                $items = Item::where('transaction_id', $this->currentId)->get();

                                foreach ($items as $item) {
                                    $productId = $item->product_id;
                                    $inventory = Inventory::where('product_id', $productId)->first();
                                
                                    if ($inventory) {
                                        $inventory->stock -= $item->quantity;
                                        $inventory->save();
                                    }
                                }

                                Notification::make()
                                ->title('Transaction Completed')
                                ->success()
                                ->send();

                                Session::remove('current_transaction_id');
                                Session::remove('current_transaction_tax');
                                Session::remove('current_transaction_discount');

                                $this->dispatch('updateTransaction');



                            })
                            ->extraAttributes([
                                'style' => 'width: 100%;',
                            ])
                            ->icon('heroicon-o-shopping-cart'),
                    ])->columnSpanFull(),

                ])->columns(3),
            ])->statePath('data');
    }
    
    public function create(): void
    {
        dd($this->form->getState());
    }

    public function render()
    {
        return view('livewire.create-transaction');
    }
}
