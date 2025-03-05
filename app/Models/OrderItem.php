<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->hasMany(Product_Type::class);
    }

    public function qrcodes()
    {
        return $this->hasOne(UserQrcode::class);
    }
    public function productTypes()
    {
        return $this->hasMany(Product_Type::class, 'order_item_id');
    }

    public function taps()
    {
        return $this->hasMany(Tap::class);
    }
}
