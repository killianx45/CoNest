<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource]
class Category extends Model
{
    protected $fillable = ['name', 'slug'];

    public function produits()
    {
        return $this->belongsToMany(Produit::class);
    }
}
