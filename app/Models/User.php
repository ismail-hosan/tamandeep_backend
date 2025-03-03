<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['avartar_image'];
    protected $fillable = [
        'name',
        'email',
        'password',
        'stripe_id',
        'avatar',
        'occipation'
    ];

    public function getAvartarImageAttribute()
    {
        return url($this->avartar);  
    }


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function productTypes()
    {
        return $this->hasMany(Product_Type::class, 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class,'user_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class,'user_id');
    }


}
