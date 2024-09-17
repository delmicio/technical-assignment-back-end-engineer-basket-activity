<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasketTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    // Set up the test environment
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);  // Authenticate the user for all tests
    }

    /** @test */
    public function it_can_add_item_to_the_basket()
    {
        $product = Product::factory()->create();

        // Act: Add a product to the basket
        $response = $this->postJson('/api/basket', ['product_id' => $product->id]);

        // Assert: Check the response and basket state
        $response->assertStatus(200)
            ->assertJson([
                'items' => [
                    ['product_id' => $product->id],
                ]
            ]);

        $this->assertBasketContains([$product->id]);
    }

    /** @test */
    public function it_can_remove_item_from_the_basket()
    {
        $product = Product::factory()->create();

        // Arrange: Add a product to the basket
        $this->postJson('/api/basket', ['product_id' => $product->id]);

        // Act: Remove the product from the basket
        $response = $this->deleteJson("/api/basket/{$product->id}");

        // Assert: Check the response and removed items state
        $response->assertStatus(200)
            ->assertJson([
                'removed_items' => [
                    ['product_id' => $product->id],
                ]
            ]);

        $this->assertBasketIsEmpty();
        $this->assertRemovedItemsContain([$product->id]);
    }

    /** @test */
    public function it_can_get_removed_items()
    {
        $product = Product::factory()->create();

        // Arrange: Add and remove a product from the basket
        $this->postJson('/api/basket', ['product_id' => $product->id]);
        $this->deleteJson("/api/basket/{$product->id}");

        // Act: Fetch the removed items
        $response = $this->getJson('/api/basket/removed-items');

        // Assert: Check the response for the removed product
        $response->assertStatus(200)
            ->assertJsonFragment(['product_id' => $product->id]);

        $this->assertRemovedItemsContain([$product->id]);
    }

    /**
     * Helper method to assert basket contains specific products
     *
     * @param array $expectedProductIds
     * @return void
     */
    private function assertBasketContains(array $expectedProductIds)
    {
        $response = $this->getJson('/api/basket');
        $response->assertStatus(200)
            ->assertJson([
                'items' => array_map(fn($id) => ['product_id' => $id], $expectedProductIds)
            ]);
    }

    /**
     * Helper method to assert basket is empty
     *
     * @return void
     */
    private function assertBasketIsEmpty()
    {
        $response = $this->getJson('/api/basket');
        $response->assertStatus(200)
            ->assertJson(['items' => []]);  // Check that items array is empty
    }

    /**
     * Helper method to assert removed items contain specific products
     *
     * @param array $expectedProductIds
     * @return void
     */
    private function assertRemovedItemsContain(array $expectedProductIds)
    {
        $response = $this->getJson('/api/basket/removed-items');
        $response->assertStatus(200)
            ->assertJson([
                'removed_items' => array_map(fn($id) => ['product_id' => $id], $expectedProductIds)
            ]);
    }
}
