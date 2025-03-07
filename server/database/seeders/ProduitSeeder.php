<?php

namespace Database\Seeders;

use App\Models\Produit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProduitSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    if (!File::exists(public_path('images'))) {
      File::makeDirectory(public_path('images'), 0777, true);
    }

    for ($i = 1; $i <= 15; $i++) {
      $imageUrl = "https://picsum.photos/800/600?random=" . $i;
      $imageContent = file_get_contents($imageUrl);
      $imageName = 'produit_' . $i . '_' . time() . '.jpg';
      $imagePath = 'images/' . $imageName;
      file_put_contents(public_path($imagePath), $imageContent);

      Produit::create([
        'nom' => "Produit $i",
        'description' => "Voici le produit n°$i",
        'prix' => rand(1, 100),
        'image' => $imagePath,
        'disponibilite' => now()->format('Y-m-d') . '-' . now()->addMonths(3)->format('Y-m-d'),
        'created_at' => now(),
        'updated_at' => now(),
        'id_user' => 1,
      ]);

      sleep(1);
    }
  }
}
