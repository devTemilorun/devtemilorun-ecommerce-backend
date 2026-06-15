<?php

namespace App\Domain\Product\DataTransferObjects;

class ProductData
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly float $price,
        public readonly int $stock,
        public readonly int $categoryId,
        public readonly string $sku,
        public readonly ?array $images = null,
        public readonly string $status = 'draft'
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'],
            price: (float) $data['price'],
            stock: (int) $data['stock'],
            categoryId: (int) $data['category_id'],
            sku: $data['sku'],
            images: $data['images'] ?? null,
            status: $data['status'] ?? 'draft'
        );
    }
}