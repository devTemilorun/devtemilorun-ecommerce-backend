<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getActiveCategories(): Collection
    {
        return $this->model->where('is_active', true)
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->first();
    }
}