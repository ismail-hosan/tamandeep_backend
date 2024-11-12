<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_Type extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function data()
    {
        return $this->hasMany(Data::class, 'category_id');
    }
}
