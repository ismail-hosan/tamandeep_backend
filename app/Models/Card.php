<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function colors()
    {
        return $this->hasMany(CardColor::class);
    }

    public function getImageAttribute($value)
    {
        return $value ? url($value) : null;
    }

    public function getPriceAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
    }


}
