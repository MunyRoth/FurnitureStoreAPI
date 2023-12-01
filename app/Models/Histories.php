<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Histories extends Model
{
    use HasFactory;


    protected $fillable = [
        'product_id',
        'qty'
    ];

    
    public function products() : BelongsTo {
        return $this->belongsTo(Histories::class);
    }
}