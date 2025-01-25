<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardColor extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function cards()
    {
        return $this->belongsToMany(Card::class, 'card_colors'); // Pivot table example
    }
}
