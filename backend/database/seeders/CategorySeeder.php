<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Hiking', 'description' => 'Mountain and trail walks'],
            ['name' => 'Cultural', 'description' => 'Heritage and local culture'],
            ['name' => 'Nature', 'description' => 'Lakes, forests, wildlife'],
            ['name' => 'Adventure', 'description' => 'Active and outdoor experiences'],
        ];

        foreach ($rows as $row) {
            Category::firstOrCreate(
                ['name' => $row['name']],
                ['description' => $row['description']]
            );
        }
    }
}
