<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GscPlusProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Optional: Truncate the table before seeding to prevent duplicate entries on re-run
        // Only do this if you want to clear existing data every time the seeder runs.
        DB::table('products')->truncate();

        // Dummy data for products
        $products = [
            [
                'provider' => 'GSC Plus',
                'currency' => 'USD',
                'status' => 'active',
                'provider_id' => 1,
                'provider_product_id' => 101,
                'product_code' => '1002',
                'product_name' => 'Golden Pharaoh\'s Fortune',
                'game_type' => 'Slots',
                'product_title' => 'Unearth Ancient Riches',
                'short_name' => 'Pharaoh Slots',
                'game_list_status' => true,
            ],
            [
                'provider' => 'GSC Plus',
                'currency' => 'USD',
                'status' => 'active',
                'provider_id' => 1,
                'provider_product_id' => 102,
                'product_code' => 'GSC_LIVE_002',
                'product_name' => 'High Roller Roulette',
                'game_type' => 'Live Casino',
                'product_title' => 'Spin the Wheel, Win Big!',
                'short_name' => 'Live Roulette',
                'game_list_status' => true,
            ],
            [
                'provider' => 'GSC Plus',
                'currency' => 'EUR',
                'status' => 'inactive', // Example of inactive product
                'provider_id' => 1,
                'provider_product_id' => 103,
                'product_code' => 'GSC_ARC_003',
                'product_name' => 'Arcade Blast Mania',
                'game_type' => 'Arcade',
                'product_title' => 'Retro Fun, Modern Wins',
                'short_name' => 'Arcade Mania',
                'game_list_status' => false, // Example of not listed
            ],
            [
                'provider' => 'GSC Plus',
                'currency' => 'USD',
                'status' => 'active',
                'provider_id' => 1,
                'provider_product_id' => 104,
                'product_code' => 'GSC_CARD_004',
                'product_name' => 'Ultimate Blackjack',
                'game_type' => 'Card Games',
                'product_title' => 'Beat the Dealer at 21',
                'short_name' => 'Blackjack Pro',
                'game_list_status' => true,
            ],
            [
                'provider' => 'GSC Plus',
                'currency' => 'JPY',
                'status' => 'active',
                'provider_id' => 1,
                'provider_product_id' => 105,
                'product_code' => 'GSC_POKER_005',
                'product_name' => 'Texas Hold\'em Pro',
                'game_type' => 'Poker',
                'product_title' => 'Bluff Your Way to the Top',
                'short_name' => 'Hold\'em',
                'game_list_status' => true,
            ],
        ];

        // Counter for order
        $orderCounter = 1;

        // Insert each product from the dummy data array
        foreach ($products as $product) {
            DB::table('products')->insert([
                'provider' => $product['provider'],
                'currency' => $product['currency'],
                'status' => $product['status'],
                'provider_id' => $product['provider_id'],
                'provider_product_id' => $product['provider_product_id'],
                'product_code' => $product['product_code'],
                'product_name' => $product['product_name'],
                'game_type' => $product['game_type'],
                'product_title' => $product['product_title'],
                'short_name' => $product['short_name'],
                'order' => $orderCounter++, // Assign and increment the order
                'game_list_status' => $product['game_list_status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}