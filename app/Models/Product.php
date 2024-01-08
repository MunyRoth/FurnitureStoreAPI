<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'category_id',
        'name',
        'price',
        'description'
    ];

    public function isFavorite(): HasOne
    {
        return $this->hasOne(Favourite::class, 'product_id');
    }

    public function imageUrls(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    // Get the first inserted child model
    public function imageUrl(): HasMany
    {
        return $this->hasMany(Image::class);

    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function shoppingCarts() {
        return $this->hasMany(shoppingCarts::class);
    }
}
