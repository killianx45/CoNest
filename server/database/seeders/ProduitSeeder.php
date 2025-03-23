<?php

namespace Database\Seeders;

use App\Models\Produit;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProduitSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $openSpace = Category::where('name', 'Open-space')->first();
    $espaceCollaboratif = Category::where('name', 'Espace collaboratif')->first();
    $bureauPrive = Category::where('name', 'Bureau privé')->first();
    $espaceLudique = Category::where('name', 'Espace ludique')->first();

    $produit1 = Produit::create([
      'nom' => "Le Hub Créatif",
      'description' => "Un espace moderne et inspirant avec des murs végétaux et une ambiance cosy.",
      'adresse' => "12 Rue des Entrepreneurs, Paris",
      'prix' => 8,
      'image' => 'images/produit_1_1741179846.jpg',
      'categorie' => 'Open-space',
      'disponibilite' => '2025-04-01-2025-06-30',
      'created_at' => now(),
      'updated_at' => now(),
      'id_user' => 1,
    ]);
    $produit1->categories()->attach($openSpace->id);

    $produit2 = Produit::create([
      'nom' => "Work & Chill",
      'description' => "Un espace hybride entre un café et un bureau, idéal pour les freelances.",
      'adresse' => "45 Avenue Montaigne, Lyon",
      'prix' => 6,
      'image' => 'images/produit_2_1741179854.jpg',
      'categorie' => 'Open-space',
      'disponibilite' => '2025-04-01-2025-06-30',
      'created_at' => now(),
      'updated_at' => now(),
      'id_user' => 1,
    ]);
    $produit2->categories()->attach($openSpace->id);
    $produit2->categories()->attach($espaceLudique->id);

    $produit3 = Produit::create([
      'nom' => "La Ruche Digitale",
      'description' => "Un lieu collaboratif où se rencontrent startups et indépendants.",
      'adresse' => "28 Quai des Chartrons, Bordeaux",
      'prix' => 7.50,
      'image' => 'images/produit_3.webp',
      'categorie' => 'Espace collaboratif',
      'disponibilite' => '2025-04-01-2025-06-30',
      'created_at' => now(),
      'updated_at' => now(),
      'id_user' => 1,
    ]);
    $produit3->categories()->attach($espaceCollaboratif->id);

    $produit4 = Produit::create([
      'nom' => "Nomad'Office",
      'description' => "Un espace minimaliste et lumineux avec des services premium.",
      'adresse' => "33 Boulevard Saint-Michel, Paris",
      'prix' => 9,
      'image' => 'images/produit_4_1741179869.jpg',
      'categorie' => 'Open-space',
      'disponibilite' => '2025-04-01-2025-06-30',
      'created_at' => now(),
      'updated_at' => now(),
      'id_user' => 1,
    ]);
    $produit4->categories()->attach($openSpace->id);
    $produit4->categories()->attach($espaceCollaboratif->id);

    $produit5 = Produit::create([
      'nom' => "Le Spot Industriel",
      'description' => "Loft industriel avec ambiance startup, idéal pour les équipes.",
      'adresse' => "14 Rue du Faubourg, Lille",
      'prix' => 7,
      'image' => 'images/produit_5.webp',
      'categorie' => 'Espace collaboratif',
      'disponibilite' => '2025-04-01-2025-06-30',
      'created_at' => now(),
      'updated_at' => now(),
      'id_user' => 1,
    ]);
    $produit5->categories()->attach($espaceCollaboratif->id);

    $produit6 = Produit::create([
      'nom' => "The Quiet Corner",
      'description' => "Un espace dédié à la concentration et au travail en silence.",
      'adresse' => "5 Avenue Mozart, Nantes",
      'prix' => 10,
      'image' => 'images/produit_6_1741179885.jpg',
      'categorie' => 'Bureau privé',
      'disponibilite' => '2025-04-01-2025-06-30',
      'created_at' => now(),
      'updated_at' => now(),
      'id_user' => 1,
    ]);
    $produit6->categories()->attach($bureauPrive->id);

    $produit7 = Produit::create([
      'nom' => "Le Lab Créatif",
      'description' => "Espace dédié aux créateurs, designers et développeurs.",
      'adresse' => "22 Rue de la Manufacture, Marseille",
      'prix' => 8.50,
      'image' => 'images/produit_7.webp',
      'categorie' => 'Bureau privé',
      'disponibilite' => '2025-04-01-2025-06-30',
      'created_at' => now(),
      'updated_at' => now(),
      'id_user' => 1,
    ]);
    $produit7->categories()->attach($bureauPrive->id);
    $produit7->categories()->attach($espaceCollaboratif->id);

    $produit8 = Produit::create([
      'nom' => "Flex Office",
      'description' => "Un coworking modulable avec des espaces privatifs et ouverts.",
      'adresse' => "10 Place de la République, Toulouse",
      'prix' => 7,
      'image' => 'images/produit_8_1741179900.jpg',
      'categorie' => 'Open-space',
      'disponibilite' => '2025-04-01-2025-06-30',
      'created_at' => now(),
      'updated_at' => now(),
      'id_user' => 1,
    ]);
    $produit8->categories()->attach($openSpace->id);
    $produit8->categories()->attach($bureauPrive->id);

    $produit9 = Produit::create([
      'nom' => "ZenWork",
      'description' => "Un coworking alliant productivité et bien-être avec un espace méditation.",
      'adresse' => "7 Rue des Artisans, Strasbourg",
      'prix' => 9.50,
      'image' => 'images/produit_9_1741179908.jpg',
      'categorie' => 'Bureau privé',
      'disponibilite' => '2025-04-01-2025-06-30',
      'created_at' => now(),
      'updated_at' => now(),
      'id_user' => 1,
    ]);
    $produit9->categories()->attach($bureauPrive->id);

    $produit10 = Produit::create([
      'nom' => "CoWork & Play",
      'description' => "Un coworking dynamique avec un espace de loisirs (babyfoot, consoles).",
      'adresse' => "19 Boulevard Haussmann, Paris",
      'prix' => 6.50,
      'image' => 'images/produit_10.webp',
      'categorie' => 'Espace ludique',
      'disponibilite' => '2025-04-01-2025-06-30',
      'created_at' => now(),
      'updated_at' => now(),
      'id_user' => 1,
    ]);
    $produit10->categories()->attach($espaceLudique->id);
  }
}
