<?php

namespace App\Services;

use App\Models\Basket;
use Carbon\Carbon;

class RemovedItemsFetcher
{
    public function getRemovedItems($fromDateParam = null, $toDateParam = null): array
    {
        // Set default date range (last week) if none is provided
        $fromDate = $fromDateParam ? Carbon::parse($fromDateParam) : Carbon::now()->subWeek()->startOfDay();
        $toDate = $toDateParam ? Carbon::parse($toDateParam) : Carbon::now()->endOfDay();

        // Fetch baskets with removed items, chunked for memory efficiency
        $removedItems = [];
        Basket::whereNotNull('removed_items')
            ->whereBetween('updated_at', [$fromDate, $toDate])
            ->chunk(100, function ($baskets) use (&$removedItems) {
                foreach ($baskets as $basket) {
                    foreach ($basket->removed_items as $removedItem) {
                        $removedItems[] = [
                            'user_id' => $basket->user_id,
                            'product_id' => $removedItem['product_id'],
                            'name' => $removedItem['name']
                        ];
                    }
                }
            });

        return $removedItems;
    }
}
