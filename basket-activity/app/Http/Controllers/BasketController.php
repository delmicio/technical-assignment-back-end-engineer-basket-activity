<?php

namespace App\Http\Controllers;

use App\Http\Requests\BasketRequest;
use App\Models\Basket;
use App\Models\Product;
use App\Models\User;
use App\Services\CsvExportService;
use App\Services\RemovedItemsFetcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BasketController extends Controller
{
    protected $csvExportService;
    protected $removedItemsFetcher;

    public function __construct(CsvExportService $csvExportService, RemovedItemsFetcher $removedItemsFetcher)
    {
        $this->csvExportService = $csvExportService;
        $this->removedItemsFetcher = $removedItemsFetcher;
    }

    /**
     * Get the basket items for a specific user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserBasket(Request $request): JsonResponse
    {
        $userId = $request->query('user_id');

        if (!$userId || !User::find($userId)) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $basket = Basket::where('user_id', $userId)->first();

        if (!$basket) {
            return response()->json(['items' => []], 200); // Empty basket
        }

        return response()->json(['items' => $basket->items ?? []], 200);
    }

    /**
     * Add a product to the basket.
     *
     * @param BasketRequest $request
     * @return JsonResponse
     */
    public function store(BasketRequest $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $basket = $this->getOrCreateBasket($userId);
        $product = $this->findProduct($request->input('product_id'));

        $this->addItemToBasket($basket, $product);

        return response()->json(['items' => $basket->items], 200);
    }

    /**
     * Remove a product from the basket.
     *
     * @param int $userId
     * @param int $productId
     * @return JsonResponse
     */
    public function removeProductFromBasket(int $userId, int $productId): JsonResponse
    {
        $basket = $this->getBasket($userId);
        $product = $this->findProduct($productId);

        $this->removeItemFromBasket($basket, $product);

        return response()->json(['removed_items' => $basket->removed_items], 200);
    }

    /**
     * Get removed items from the basket.
     *
     * @return JsonResponse
     */
    public function removedItems(): JsonResponse
    {
        $baskets = Basket::whereNotNull('removed_items')->get();
        $removedItemsWithUser = [];

        foreach ($baskets as $basket) {
            foreach ($basket->removed_items as $item) {
                $removedItemsWithUser[] = [
                    'user_id' => $basket->user_id,
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                ];
            }
        }

        // Return the removed items with the user info
        return response()->json(['removed_items' => $removedItemsWithUser], 200);
    }

    /**
     * Download removed items as CSV.
     *
     * @param Request $request
     * @return void
     */
    public function downloadRemovedItemsCsv(Request $request): void
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');

        $removedItems = $this->removedItemsFetcher->getRemovedItems($fromDate, $toDate);

        $this->csvExportService->export($removedItems);
    }

    /**
     * Get or create a basket for the specified user.
     *
     * @param int $userId
     * @return Basket
     */
    private function getOrCreateBasket(int $userId): Basket
    {
        $basket = Basket::firstOrNew(['user_id' => $userId]);
        $basket->save();

        return $basket;
    }

    /**
     * Get the basket for the specified user.
     *
     * @param int $userId
     * @return Basket
     */
    private function getBasket(int $userId): Basket
    {
        return Basket::where('user_id', $userId)->firstOrFail();
    }

    /**
     * Find a product by its ID.
     *
     * @param int $productId
     * @return Product
     */
    private function findProduct(int $productId): Product
    {
        return Product::findOrFail($productId);
    }

    /**
     * Add a product to the basket's items.
     *
     * @param Basket $basket
     * @param Product $product
     */
    private function addItemToBasket(Basket $basket, Product $product): void
    {
        $basket->items = $this->addProductToItems($basket->items, $product);
        $basket->save();
    }

    /**
     * Remove a product from the basket and add to removed items.
     *
     * @param Basket $basket
     * @param Product $product
     */
    private function removeItemFromBasket(Basket $basket, Product $product): void
    {
        $basket->items = $this->removeProductFromItems($basket->items, $product);
        $basket->removed_items = $this->addProductToRemovedItems($basket->removed_items, $product);
        $basket->save();
    }

    /**
     * Add a product to the basket's items array.
     *
     * @param array|null $items
     * @param Product $product
     * @return array
     */
    private function addProductToItems(?array $items, Product $product): array
    {
        $items = $items ?? [];
        $items[] = ['product_id' => $product->id];
        return $items;
    }

    /**
     * Remove a product from the basket's items array.
     *
     * @param array|null $items
     * @param Product $product
     * @return array
     */
    private function removeProductFromItems(?array $items, Product $product): array
    {
        return array_filter($items ?? [], fn($item) => $item['product_id'] != $product->id);
    }

    /**
     * Add a product to the basket's removed items array.
     *
     * @param array|null $removedItems
     * @param Product $product
     * @return array
     */
    private function addProductToRemovedItems(?array $removedItems, Product $product): array
    {
        $removedItems = $removedItems ?? [];
        $removedItems[] = ['product_id' => $product->id, 'name' => $product->name];
        return $removedItems;
    }
}
