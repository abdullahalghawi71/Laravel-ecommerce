<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 300; $i++) {
            Product::create([
                'sku' => strtoupper(Str::random(8)),
                'name' => $faker->word() . ' ' . $faker->randomElement(['Widget', 'Gadget', 'Device', 'Tool']),
                'quantity' => $faker->numberBetween(1, 100),
                'code' => strtoupper('P' . $i . Str::random(5)),
                'description' => $faker->sentence(),
                'unit_price' => $faker->randomFloat(2, 5, 1000),
            ]);
        }
    }
}
