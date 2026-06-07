<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'phone', 'address', 'avatar', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ─── Relationships ──────────────────────────────────
    public function orders()    { return $this->hasMany(Order::class); }
    public function carts()     { return $this->hasMany(Cart::class); }
    public function reviews()   { return $this->hasMany(Review::class); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }

    // ─── Helpers ────────────────────────────────────────
    public function isAdmin(): bool { return $this->role === 'admin'; }
}