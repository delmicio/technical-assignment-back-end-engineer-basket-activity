<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Pioneer DJ Mixer', 'price' => 699],
            ['name' => 'Roland Wave Sampler', 'price' => 485],
            ['name' => 'Reloop Headphone', 'price' => 159],
            ['name' => 'Rokit Monitor', 'price' => 189.9],
            ['name' => 'Fisherprice Baby Mixer', 'price' => 120],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate($product);
        }
    }
}
