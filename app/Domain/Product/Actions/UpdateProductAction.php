<?php

namespace App\Domain\Product\Actions;

use App\Models\Product;
use Illuminate\Support\Str;

class UpdateProductAction
{
    public function execute(Product $product, array $data): Product
    {
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        $product->update($data);
        
        return $product->fresh();
    }
}