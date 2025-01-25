<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItems extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    
    public function color()
    {
        return $this->belongsTo(CardColor::class, 'color_id'); // Assuming 'color_id' is the foreign key
    }

    public function product()
    {
        return $this->belongsTo(Card::class, 'card_id'); // Assuming 'product_id' is the foreign key
    }
}
