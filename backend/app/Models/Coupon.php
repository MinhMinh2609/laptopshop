<?php
// app/Models/Coupon.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order', 'max_discount',
        'usage_limit', 'usage_count', 'is_active', 'starts_at', 'expires_at',
    ];

    protected $casts = [
        'value'        => 'decimal:2',
        'min_order'    => 'decimal:0',
        'max_discount' => 'decimal:0',
        'is_active'    => 'boolean',
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
    ];

    // Luôn lưu mã dưới dạng chữ in hoa để so sánh nhất quán
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper($value);
    }

    // ─── KIỂM TRA MÃ CÒN HIỆU LỰC ───────────────────────
    // Trả về null nếu hợp lệ, ngược lại trả về lý do không hợp lệ
    public function validationError(): ?string
    {
        if (!$this->is_active) return 'Mã giảm giá hiện không khả dụng.';
        if ($this->starts_at && $this->starts_at->isFuture()) return 'Mã giảm giá chưa đến thời gian áp dụng.';
        if ($this->expires_at && $this->expires_at->isPast()) return 'Mã giảm giá đã hết hạn.';
        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) return 'Mã giảm giá đã hết lượt sử dụng.';

        return null;
    }

    public function isValid(): bool
    {
        return $this->validationError() === null;
    }

    // ─── TÍNH SỐ TIỀN ĐƯỢC GIẢM ─────────────────────────
    public function calculateDiscount(float $orderTotal): float
    {
        if ($orderTotal < $this->min_order) return 0;

        $discount = $this->type === 'percent'
            ? $orderTotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return min($discount, $orderTotal);
    }
}
