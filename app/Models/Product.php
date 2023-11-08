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
        'name',
        'price',
        'description'
    ];

    public function imageUrls(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    // Get the first inserted child model
    public function imageUrl(): HasMany
    {
        return $this->hasMany(Image::class);
        // return $this->hasMany(Image::class)->oldestOfMany();

    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
        
    }
}
