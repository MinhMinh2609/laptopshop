<?php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'sku',
        'description', 'price', 'sale_price', 'stock', 'thumbnail',
        'cpu', 'ram', 'storage', 'display', 'gpu', 'os', 'battery', 'weight',
        'is_active', 'is_featured', 'views',
    ];

    protected $casts = [
        'price'       => 'decimal:0',
        'sale_price'  => 'decimal:0',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
    ];

    // ─── Relationships ──────────────────────────────────
    public function category()   { return $this->belongsTo(Category::class); }
    public function brand()      { return $this->belongsTo(Brand::class); }
    public function images()     { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function reviews()    { return $this->hasMany(Review::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
    public function wishlists()  { return $this->hasMany(Wishlist::class); }
}