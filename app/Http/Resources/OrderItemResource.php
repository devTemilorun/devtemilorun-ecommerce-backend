<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'product_sku' => $this->product_sku,
            'unit_price' => (float) $this->unit_price,
            'quantity' => $this->quantity,
            'total' => (float) $this->total,
            'attributes' => $this->attributes,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}