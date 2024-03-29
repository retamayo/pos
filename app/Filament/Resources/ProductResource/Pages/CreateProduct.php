<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Product
    {
        $record =  static::getModel()::create($data);

        $category = Category::find($data['category_id']);
        $category->products_count = $category->products_count + 1;

        $inventory = new Inventory();
        $inventory->product_id = $record->id;
        $inventory->stock = $data['stock'];

        $category->save();
        $inventory->save();

        return $record;
    }
}
