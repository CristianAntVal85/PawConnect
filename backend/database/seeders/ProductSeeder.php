<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $providers = Provider::factory()->count(8)->create();

        Product::factory()
            ->count(30)
            ->recycle($providers)
            ->create();
    }
}
