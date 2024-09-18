<?php

namespace Tests\Feature;

use App\Models\Basket;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesCsvTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_download_a_csv_of_removed_items_for_last_week()
    {
        // Arrange: Create a product and a basket with a removed item
        $product = Product::factory()->create(['name' => 'Product 1']);
        $this->createBasketWithRemovedItem($product, 3);

        // Act: Download and capture CSV content
        $csvContent = $this->getCapturedCsv('/api/v1/removed-items/export-csv');

        // Assert: Check the CSV content
        $this->assertCsvContains($csvContent, ['Product 1']);
    }

    /** @test */
    public function it_can_download_a_csv_of_removed_items_within_custom_date_range()
    {
        // Arrange: Create two products and baskets with removed items on different dates
        $product1 = Product::factory()->create(['name' => 'Product 1']);
        $product2 = Product::factory()->create(['name' => 'Product 2']);

        $this->createBasketWithRemovedItem($product1, 7);  // Removed 7 days ago
        $this->createBasketWithRemovedItem($product2, 10);  // Removed 10 days ago

        // Generate query params for the date range
        $fromDate = Carbon::now()->subDays(10)->toIso8601String();
        $toDate = Carbon::now()->toIso8601String();
        $queryParams = http_build_query(['from' => $fromDate, 'to' => $toDate]);

        // Act: Download and capture CSV content within custom date range
        $csvContent = $this->getCapturedCsv("/api/v1/removed-items/export-csv?$queryParams");

        // Assert: Check that both products are in the CSV
        $this->assertCsvContains($csvContent, ['Product 1', 'Product 2']);
    }

    /** @test */
    public function it_can_download_a_csv_of_removed_items_with_user_id()
    {
        // Arrange: Create a product and a basket with a removed item
        $product = Product::factory()->create(['name' => 'Product 1']);
        $basket = $this->createBasketWithRemovedItem($product, 3);  // Removed 3 days ago

        // Act: Download and capture CSV content
        $csvContent = $this->getCapturedCsv('/api/v1/removed-items/export-csv');

        // Assert: Check the CSV structure and content
        $csvRows = array_map('str_getcsv', explode("\n", trim($csvContent)));

        // Assert that the first row contains the CSV headers
        $this->assertEquals(['User ID', 'Product ID', 'Product Name'], $csvRows[0]);

        // Assert that the second row contains the correct data
        $this->assertEquals([$basket->user_id, $product->id, $product->name], $csvRows[1]);
    }

    /**
     * Helper to create a basket with a removed item
     *
     * @param Product $product
     * @param int $daysAgo
     * @return Basket
     */
    protected function createBasketWithRemovedItem(Product $product, int $daysAgo): Basket
    {
        return Basket::create([
            'user_id' => User::factory()->create()->id,  // Each basket has its own user
            'items' => [],
            'removed_items' => [['product_id' => $product->id, 'name' => $product->name]],
            'updated_at' => Carbon::now()->subDays($daysAgo),  // Specify when the product was removed
        ]);
    }

    /**
     * Helper to assert CSV content contains specific product names and can check headers
     *
     * @param string $csvContent
     * @param array $expectedProductNames
     * @param array|null $expectedHeaders
     * @return void
     */
    protected function assertCsvContains(string $csvContent, array $expectedProductNames, array $expectedHeaders = null): void
    {
        $csvRows = array_map('str_getcsv', explode("\n", trim($csvContent)));

        if ($expectedHeaders) {
            $this->assertEquals($expectedHeaders, $csvRows[0]);  // Check CSV headers if provided
        }

        // Check that each expected product name exists in the CSV content
        foreach ($expectedProductNames as $name) {
            $this->assertStringContainsString($name, $csvContent);
        }
    }

    /**
     * Helper to capture streamed CSV content for testing
     *
     * @param string $uri
     * @return string
     */
    protected function getCapturedCsv(string $uri): string
    {
        // Start output buffering to capture the streamed response
        ob_start();

        // Act: Call the endpoint that streams the CSV
        $response = $this->get($uri);

        // Capture the streamed output
        $csvContent = ob_get_clean();

        // Assert: Check the response status
        $response->assertStatus(200);

        return $csvContent;
    }
}
