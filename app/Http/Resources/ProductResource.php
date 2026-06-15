<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => (float) $this->price,
            'compare_price' => (float) $this->compare_price,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'images' => $this->images ?? [],
            'attributes' => $this->attributes,
            'weight' => $this->weight,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'rating' => (float) $this->rating,
            'sales_count' => $this->sales_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}