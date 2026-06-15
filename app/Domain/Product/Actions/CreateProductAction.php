<?php

namespace App\Domain\Product\Actions;

use App\Models\Product;
use Illuminate\Support\Str;

class CreateProductAction
{
    public function execute(array $data): Product
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['sku'] = $data['sku'] ?? $this->generateSku($data['name']);
        
        return Product::create($data);
    }
    
    private function generateSku(string $name): string
    {
        $prefix = strtoupper(substr($name, 0, 3));
        $uniqueId = uniqid();
        return "{$prefix}-{$uniqueId}";
    }
}