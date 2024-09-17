<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_add_item_to_the_basket()
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/basket', ['product_id' => $product->id]);

        $response->assertStatus(200)
            ->assertJson([
                'items' => [
                    ['product_id' => $product->id],
                ]
            ]);

        // Fetch basket state and ensure the product is in the basket
        $this->assertBasketContains([$product->id]);
    }

    /** @test */
    public function it_can_remove_item_from_the_basket()
    {
        $product = Product::factory()->create();

        $this->postJson('/api/basket', ['product_id' => $product->id]);

        $response = $this->deleteJson("/api/basket/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'removed_items' => [
                    ['product_id' => $product->id],
                ]
            ]);

        // Fetch basket state and ensure the product has been removed
        $this->assertBasketContains([]);
        $this->assertRemovedItemsContain([$product->id]);
    }

    /** @test */
    public function it_can_get_removed_items()
    {
        $product = Product::factory()->create();

        $this->postJson('/api/basket', ['product_id' => $product->id]);
        $this->deleteJson("/api/basket/{$product->id}");

        $response = $this->getJson('/api/basket/removed-items');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'product_id' => $product->id
            ]);

        // Ensure removed items are correctly populated
        $this->assertRemovedItemsContain([$product->id]);
    }

    private function assertBasketContains(array $expectedProductIds)
    {
        $response = $this->getJson('/api/basket');
        $response->assertStatus(200)
            ->assertJson(['items' => collect($expectedProductIds)->map(fn($id) => ['product_id' => $id])->toArray()]);
    }

    private function assertRemovedItemsContain(array $expectedProductIds)
    {
        $response = $this->getJson('/api/basket/removed-items');
        $response->assertStatus(200)
            ->assertJson(['removed_items' => collect($expectedProductIds)->map(fn($id) => ['product_id' => $id])->toArray()]);
    }
}
