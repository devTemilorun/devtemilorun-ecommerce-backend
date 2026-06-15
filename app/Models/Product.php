<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    
    protected $fillable = [
        'name', 'slug', 'description', 'short_description', 'price',
        'compare_price', 'stock', 'sku', 'category_id', 'images',
        'status', 'is_featured', 'rating', 'sales_count', 'views'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'rating' => 'decimal:2',
        'images' => 'array'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}