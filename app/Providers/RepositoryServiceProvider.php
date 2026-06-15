<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProductRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\InventoryRepository;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\Category;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepository::class, function ($app) {
            return new ProductRepository($app->make(Product::class));
        });

        $this->app->bind(OrderRepository::class, function ($app) {
            return new OrderRepository($app->make(Order::class));
        });

        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });

        $this->app->bind(CategoryRepository::class, function ($app) {
            return new CategoryRepository($app->make(Category::class));
        });
    }

    public function boot(): void
    {
        //
    }
}