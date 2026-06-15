<?php

namespace App\Domain\Product\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        DB::beginTransaction();
        
        try {
            // Delete associated images
            foreach ($product->images as $image) {
                // Delete from storage if needed
                $image->delete();
            }
            
            $product->delete();
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete product', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}