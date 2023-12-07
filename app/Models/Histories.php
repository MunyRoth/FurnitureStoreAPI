<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Histories extends Model
{
    use HasFactory;


    protected $fillable = [
        'product_id',
        'user_id',
        'qty',
    ];

    
    public function product() : BelongsTo {
        return $this->belongsTo(Product::class);
    }


}
