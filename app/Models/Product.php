<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    public function imageUrl(): HasOne
    {
        return $this->hasOne(Image::class)->oldestOfMany();

    }
}
