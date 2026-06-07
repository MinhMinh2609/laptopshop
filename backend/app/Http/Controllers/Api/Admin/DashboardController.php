<?php
// app/Http/Controllers/Api/Admin/DashboardController.php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // ─── TỔNG QUAN DASHBOARD ─────────────────────────────
    public function index()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        return response()->json([
            'success' => true,
            'data'    => [
                // Thống kê tổng
                'total_users'    => User::where('role', 'user')->count(),
                'total_products' => Product::where('is_active', 1)->count(),
                'total_orders'   => Order::count(),
                'total_revenue'  => Order::where('payment_status', 'paid')->sum('final_amount'),

                // Tháng này
                'orders_this_month'  => Order::where('created_at', '>=', $thisMonth)->count(),
                'revenue_this_month' => Order::where('payment_status', 'paid')
                    ->where('created_at', '>=', $thisMonth)
                    ->sum('final_amount'),

                // Hôm nay
                'orders_today'   => Order::where('created_at', '>=', $today)->count(),
                'revenue_today'  => Order::where('payment_status', 'paid')
                    ->where('created_at', '>=', $today)
                    ->sum('final_amount'),

                // Đơn hàng theo trạng thái
                'orders_by_status' => Order::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')->get(),

                // Sản phẩm sắp hết hàng (stock < 5)
                'low_stock_products' => Product::where('stock', '<', 5)
                    ->where('is_active', 1)
                    ->select('id', 'name', 'sku', 'stock')
                    ->take(10)->get(),
            ]
        ]);
    }

    // ─── THỐNG KÊ DOANH THU THEO THỜI GIAN ──────────────
    public function revenue(Request $request)
    {
        $period = $request->period ?? 'month'; // week | month | year

        $data = match($period) {
            'week'  => $this->revenueByDay(7),
            'month' => $this->revenueByDay(30),
            'year'  => $this->revenueByMonth(12),
            default => $this->revenueByDay(30),
        };

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function revenueByDay(int $days): array
    {
        return Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(final_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function revenueByMonth(int $months): array
    {
        return Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subMonths($months))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(final_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    // ─── TOP SẢN PHẨM BÁN CHẠY ──────────────────────────
    public function topProducts(Request $request)
    {
        $limit = $request->limit ?? 10;

        $products = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'delivered')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.thumbnail',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.thumbnail')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();

        return response()->json(['success' => true, 'data' => $products]);
    }
}
