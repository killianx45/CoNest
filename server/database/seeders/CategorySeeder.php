<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Open-space',
            'Espace collaboratif',
            'Bureau privé',
            'Espace ludique'
        ];

        foreach ($categories as $category) {
            if (!Category::where('name', $category)->exists()) {
                Category::create([
                    'name' => $category,
                    'slug' => Str::slug($category)
                ]);
            }
        }
    }
}
