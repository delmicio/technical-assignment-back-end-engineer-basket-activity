<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasketTest extends TestCase
{
    use RefreshDatabase;

    // Set up the test environment
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_add_item_to_the_basket()
    {
        $product = Product::factory()->create();

        // Act: Add a product to the basket
        $response = $this->postJson('/api/v1/baskets', [
            'user_id' => $this->user->id,
            'product_id' => $product->id
        ]);

        // Assert: Check the response and basket state
        $response->assertStatus(200)
            ->assertJson([
                'items' => [
                    ['product_id' => $product->id],
                ]
            ]);

        $this->assertBasketContains([$product->id], $this->user->id);
    }

    /** @test */
    public function it_can_remove_item_from_the_basket()
    {
        $product = Product::factory()->create();

        // Arrange: Add a product to the basket
        $this->postJson('/api/v1/baskets', [
            'user_id' => $this->user->id,
            'product_id' => $product->id
        ]);

        // Act: Remove the product from the basket
        $response = $this->patchJson("/api/v1/baskets/{$this->user->id}/products/{$product->id}");

        // Assert: Check the response and removed items state
        $response->assertStatus(200)
            ->assertJson([
                'removed_items' => [
                    ['product_id' => $product->id],
                ]
            ]);

        $this->assertBasketIsEmpty($this->user->id);
        $this->assertRemovedItemsContain([$product->id], $this->user->id);
    }

    /** @test */
    public function it_can_get_removed_items()
    {
        $product = Product::factory()->create();

        // Arrange: Add and remove a product from the basket
        $this->postJson('/api/v1/baskets', [
            'user_id' => $this->user->id,
            'product_id' => $product->id
        ]);
        $this->patchJson("/api/v1/baskets/{$this->user->id}/products/{$product->id}");

        $this->assertRemovedItemsContain([$product->id], $this->user->id);
    }

    /**
     * Helper method to assert basket contains specific products
     *
     * @param array $expectedProductIds
     * @param int $userId
     * @return void
     */
    private function assertBasketContains(array $expectedProductIds, int $userId): void
    {
        $response = $this->getJson("/api/v1/baskets?user_id={$userId}");
        $response->assertStatus(200)
            ->assertJson([
                'items' => array_map(fn($id) => ['product_id' => $id], $expectedProductIds)
            ]);
    }

    /**
     * Helper method to assert basket is empty
     *
     * @param int $userId
     * @return void
     */
    private function assertBasketIsEmpty(int $userId): void
    {
        $response = $this->getJson("/api/v1/baskets?user_id={$userId}");
        $response->assertStatus(200)
            ->assertJson(['items' => []]);  // Check that items array is empty
    }

    /**
     * Helper method to assert removed items contain specific products for a user
     *
     * @param array $expectedProductIds
     * @param int $userId
     * @return void
     */
    private function assertRemovedItemsContain(array $expectedProductIds, int $userId): void
    {
        $response = $this->getJson('/api/v1/removed-items');

        // Assert the request was successful
        $response->assertStatus(200);

        // Get the JSON data from the response
        $removedItems = collect($response->json('removed_items'));

        // Filter the removed items by user ID and map only the product IDs
        $filteredItems = $removedItems->filter(function ($item) use ($userId) {
            return $item['user_id'] === $userId;
        })->pluck('product_id')->toArray();

        // Assert that all expected product IDs are in the filtered removed items
        foreach ($expectedProductIds as $expectedProductId) {
            $this->assertTrue(
                in_array($expectedProductId, $filteredItems),
                "Failed asserting that the removed items contain product ID: {$expectedProductId} for user ID: {$userId}"
            );
        }
    }
}
