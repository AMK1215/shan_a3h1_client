<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameTypeProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // id, game_type, product_title from SQL dump
            ['product_id' => 1, 'game_type_id' => 1, 'image' => 'SBO.png', 'rate' => 1.0000],
            
        ];

        DB::table('game_type_product')->insert($data);
    }
}
