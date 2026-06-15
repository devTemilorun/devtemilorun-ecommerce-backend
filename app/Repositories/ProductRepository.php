<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getFiltered(array $filters, int $perPage = 20)
    {
        $query = $this->model->query()->with('category');
        
        //  filters
        if (!empty($filters['category'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }
        
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        //  sorting
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('sales_count', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Only show published products 
        if (!request()->user() || !request()->user()->isAdmin()) {
            $query->where('status', 'published');
        }
        
        return $query->paginate($perPage);
    }

    public function getFeatured(int $limit = 8): Collection
    {
        return $this->model->where('is_featured', true)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function updateStock(int $productId, int $quantity): bool
    {
        return $this->model->where('id', $productId)
            ->decrement('stock', $quantity);
    }

    public function getLowStockProducts(int $threshold = 5): Collection
    {
        return $this->model->where('stock', '<=', $threshold)
            ->where('status', 'published')
            ->get();
    }
}