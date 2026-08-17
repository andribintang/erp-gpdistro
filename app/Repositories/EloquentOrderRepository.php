<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->with(['customer', 'items.product'])
            ->withSum(['payments as verified_payments_sum' => fn ($q) => $q->where('status', 'verified')], 'amount')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                ->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['channel'] ?? null, fn ($query, string $channel) => $query->where('channel', $channel))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Order
    {
        return Order::query()->create($data);
    }
}
