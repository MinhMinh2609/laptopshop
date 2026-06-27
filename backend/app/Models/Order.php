<?php
// app/Models/Order.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_code', 'total_amount', 'discount_amount', 'final_amount',
        'status', 'payment_method', 'payment_status', 'vnpay_txn_ref',
        'shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city', 'note',
    ];

    protected $casts = [
        'total_amount'    => 'decimal:0',
        'discount_amount' => 'decimal:0',
        'final_amount'    => 'decimal:0',
    ];

    public function user()  { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }

    public function restoreStock(): void
    {
        foreach ($this->items as $item) {
            Product::where('id', $item->product_id)
                ->increment('stock', $item->quantity);
        }
    }

}
