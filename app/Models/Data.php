<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function Category()
    {
        return $this->belongsTo(Product_Type::class, 'category_id');
    }
}
