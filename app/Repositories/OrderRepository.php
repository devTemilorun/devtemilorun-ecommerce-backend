<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getUserOrders(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with(['items', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $id): ?Order
    {
        return $this->model->with(['items', 'payment', 'user'])
            ->find($id);
    }

    public function getOrdersByStatus(string $status, int $perPage = 20)
    {
        return $this->model->where('status', $status)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getRecentOrders(int $limit = 10): Collection
    {
        return $this->model->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getSalesStats(string $startDate, string $endDate): array
    {
        $stats = $this->model->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total) as total_revenue,
                AVG(total) as avg_order_value
            ')
            ->first();
        
        return [
            'total_orders' => $stats->total_orders ?? 0,
            'total_revenue' => $stats->total_revenue ?? 0,
            'avg_order_value' => $stats->avg_order_value ?? 0,
        ];
    }
}