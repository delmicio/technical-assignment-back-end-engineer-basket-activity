<?php

namespace App\Http\Controllers;

use App\Http\Requests\BasketRequest;
use App\Models\Basket;
use App\Models\Product;
use App\Services\CsvExportService;
use App\Services\RemovedItemsFetcher;
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

    public function downloadRemovedItemsCsv(Request $request)
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');

        $removedItems = $this->removedItemsFetcher->getRemovedItems($fromDate, $toDate);

        $this->csvExportService->export($removedItems);
    }

    public function index()
    {
        $basket = $this->getOrCreateBasket();
        return response()->json(['items' => $basket->items ?? []], 200);
    }

    public function store(BasketRequest $request)
    {
        $basket = $this->getOrCreateBasket();
        $product = $this->findProduct($request->input('product_id'));

        $this->addItemToBasket($basket, $product);
        return response()->json(['items' => $basket->items], 200);
    }

    public function destroy($id)
    {
        $basket = $this->getBasket();
        $product = $this->findProduct($id);

        $this->removeItemFromBasket($basket, $product);
        return response()->json(['removed_items' => $basket->removed_items], 200);
    }

    public function removedItems()
    {
        $basket = $this->getOrCreateBasket();
        return response()->json(['removed_items' => $basket->removed_items ?? []], 200);
    }

    private function getBasket()
    {
        return Basket::where('session_id', session()->getId())->firstOrFail();
    }

    private function getOrCreateBasket()
    {
        $basket = Basket::firstOrNew(['session_id' => session()->getId()]);
        $basket->user_id = $basket->user_id ?? auth()->id();
        $basket->save();

        return $basket;
    }

    private function findProduct($productId)
    {
        return Product::findOrFail($productId);
    }

    private function addItemToBasket(Basket $basket, Product $product)
    {
        $basket->items = $this->addProductToItems($basket->items, $product);
        $basket->save();
    }

    private function removeItemFromBasket(Basket $basket, Product $product)
    {
        $basket->items = $this->removeProductFromItems($basket->items, $product);
        $basket->removed_items = $this->addProductToRemovedItems($basket->removed_items, $product);
        $basket->save();
    }

    private function addProductToItems(?array $items, Product $product): array
    {
        $items = $items ?? [];
        $items[] = ['product_id' => $product->id];
        return $items;
    }

    private function removeProductFromItems(?array $items, Product $product): array
    {
        return array_filter($items ?? [], fn($item) => $item['product_id'] != $product->id);
    }

    private function addProductToRemovedItems(?array $removedItems, Product $product): array
    {
        $removedItems = $removedItems ?? [];
        $removedItems[] = ['product_id' => $product->id];
        return $removedItems;
    }
}
