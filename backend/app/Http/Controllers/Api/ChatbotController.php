<?php
// app/Http/Controllers/Api/ChatbotController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    // ─── GỬI TIN NHẮN ĐẾN CHATBOT ───────────────────────
    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array|max:10',
            'session_id' => 'nullable|string|max:100',
        ]);

        $userMessage = $request->message;

        // Lấy ngữ cảnh sản phẩm từ DB để chatbot tư vấn chính xác
        $productContextData = $this->getProductContext($userMessage);
        $productContext = $productContextData['context'];
        $recommendedProducts = $productContextData['products'];

        // System prompt cho chatbot
        $systemPrompt = "Bạn là chatbot tư vấn bán laptop cho website LaptopShop.

        Quy tắc bắt buộc:
        - Chỉ được tư vấn các sản phẩm xuất hiện trong DANH SÁCH SẢN PHẨM HIỆN CÓ.
        - Không được bịa tên laptop, giá, CPU, RAM, tồn kho, khuyến mãi hoặc thông số không có trong danh sách.
        - Nếu không có sản phẩm phù hợp, hãy nói hiện shop chưa có mẫu phù hợp và hỏi khách có muốn đổi ngân sách/nhu cầu không.
        - Trả lời bằng tiếng Việt, ngắn gọn, thân thiện, ưu tiên tư vấn để khách chọn mua.
        - Khi gợi ý sản phẩm, nêu tên, giá, CPU, RAM, SSD, GPU, tình trạng còn hàng nếu có.

        DANH SÁCH SẢN PHẨM HIỆN CÓ:
        {$productContext}";

        // Xây dựng lịch sử chat
        $messages = [];

        // Thêm lịch sử chat, bỏ tin hiện tại nếu frontend đã gửi kèm ở cuối history
        if ($request->history) {
            $history = array_values($request->history);

            foreach ($history as $index => $msg) {
                $isCurrentMessage = $index === array_key_last($history)
                    && ($msg['role'] ?? null) === 'user'
                    && ($msg['content'] ?? null) === $userMessage;

                if ($isCurrentMessage) {
                    continue;
                }

                if (in_array($msg['role'] ?? null, ['user', 'assistant']) && is_string($msg['content'] ?? null)) {
                    $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
                }
            }
        }

        // Thêm tin nhắn hiện tại
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        // Gọi Gemini API
        $apiKey = env('GEMINI_API_KEY');
        $usedGemini = false;
        $tokenUsage = null;

        if (!$apiKey || $apiKey === 'your_gemini_api_key') {
            // Fallback khi chưa có API key — dùng rule-based
            $reply = $this->ruleBasedResponse($userMessage, $productContext, $messages);
        } else {
            $geminiResult = $this->callGemini($systemPrompt, $messages, $apiKey);
            $reply = $geminiResult['reply'] ?? null;
            $tokenUsage = $geminiResult['usage'] ?? null;

            if (!$reply) {
                $reply = $this->ruleBasedResponse($userMessage, $productContext, $messages);
            } else {
                $usedGemini = true;
            }
        }

        $this->logChatbotMessage($request, $userMessage, $reply, $recommendedProducts, $usedGemini, $tokenUsage);

        return response()->json([
            'success' => true,
            'data'    => [
                'reply' => $reply,
                'products' => $this->formatProductCards($recommendedProducts),
            ],
        ]);
    }

    // ─── LẤY NGỮ CẢNH SẢN PHẨM ─────────────────────────
    private function getProductContext(string $message): array
    {
        $limit = $this->productResultLimit($message);

        if ($this->isProductListRequest($message)) {
            $products = Product::with('brand')
                ->where('is_active', 1)
                ->latest()
                ->take($limit)
                ->get();

            return [
                'context' => $this->formatProductContext($products),
                'products' => $products,
            ];
        }

        // Tìm sản phẩm liên quan đến câu hỏi
        $products = Product::with('brand')
            ->where('is_active', 1)
            ->where(function ($q) use ($message) {
                $keywords = explode(' ', $message);
                foreach ($keywords as $kw) {
                    if (strlen($kw) > 2) {
                        $q->orWhere('name',    'like', "%{$kw}%")
                          ->orWhere('cpu',     'like', "%{$kw}%")
                          ->orWhere('ram',     'like', "%{$kw}%")
                          ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%{$kw}%"));
                    }
                }
            })
            ->take($limit)
            ->get();

        if ($products->isEmpty()) {
            // Lấy sản phẩm nổi bật nếu câu hỏi không khớp từ khóa cụ thể
            $products = Product::with('brand')->where('is_active', 1)
                ->where('is_featured', 1)->take($limit)->get();
        }

        if ($products->isEmpty()) {
            // Nếu chưa đánh dấu sản phẩm nổi bật, vẫn lấy các sản phẩm đang bán
            $products = Product::with('brand')
                ->where('is_active', 1)
                ->latest()
                ->take($limit)
                ->get();
        }

        return [
            'context' => $this->formatProductContext($products),
            'products' => $products,
        ];
    }

    private function productResultLimit(string $message): int
    {
        $msg = mb_strtolower($message);

        if (
            str_contains($msg, 'tất cả')
            || str_contains($msg, 'tat ca')
            || str_contains($msg, 'toàn bộ')
            || str_contains($msg, 'toan bo')
        ) {
            return 8;
        }

        if (
            str_contains($msg, 'danh sách')
            || str_contains($msg, 'danh sach')
            || str_contains($msg, 'xem thêm')
            || str_contains($msg, 'xem them')
            || str_contains($msg, 'thêm mẫu')
            || str_contains($msg, 'them mau')
        ) {
            return 4;
        }

        return 2;
    }

    private function isProductListRequest(string $message): bool
    {
        $msg = mb_strtolower($message);

        return str_contains($msg, 'danh sách')
            || str_contains($msg, 'san pham')
            || str_contains($msg, 'sản phẩm')
            || str_contains($msg, 'có mẫu nào')
            || str_contains($msg, 'co mau nao')
            || str_contains($msg, 'có laptop')
            || str_contains($msg, 'co laptop')
            || str_contains($msg, 'cho xem')
            || str_contains($msg, 'show');
    }

    private function formatProductContext($products): string
    {
        if ($products->isEmpty()) {
            return 'Hiện chưa có sản phẩm đang bán trong database.';
        }

        return $products->map(fn($p) =>
            "- ID: {$p->id} | {$p->name} | Hãng: {$p->brand?->name ?? 'N/A'} | Giá: " .
            number_format($p->sale_price ?? $p->price) .
            "đ | CPU: {$p->cpu} | RAM: {$p->ram} | SSD: {$p->storage} | GPU: {$p->gpu} | Màn hình: {$p->display} | Tồn kho: {$p->stock} | Link: /products/{$p->slug}"
        )->join("\n");
    }

    private function formatProductCards($products): array
    {
        return $products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'brand' => $p->brand?->name,
            'price' => (float) $p->price,
            'sale_price' => $p->sale_price ? (float) $p->sale_price : null,
            'stock' => $p->stock,
            'thumbnail' => $p->thumbnail,
            'cpu' => $p->cpu,
            'ram' => $p->ram,
            'storage' => $p->storage,
            'gpu' => $p->gpu,
            'display' => $p->display,
        ])->values()->all();
    }

    private function logChatbotMessage(Request $request, string $message, string $reply, $products, bool $usedGemini, ?array $tokenUsage = null): void
    {
        Log::info('Chatbot conversation', [
            'user_id' => $request->user()?->id,
            'session_id' => $request->input('session_id'),
            'ip' => $request->ip(),
            'message' => $message,
            'reply' => $reply,
            'product_ids' => $products->pluck('id')->values()->all(),
            'used_gemini' => $usedGemini,
            'token_usage' => $tokenUsage,
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    // ─── GỌI GEMINI API ──────────────────────────────────
    private function callGemini(string $systemPrompt, array $messages, string $apiKey): ?array
    {
        try {
            $model = env('GEMINI_MODEL', 'gemini-3.5-flash');

            $contents = collect($messages)->map(function ($msg) {
                return [
                    'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [
                        ['text' => $msg['content']],
                    ],
                ];
            })->values()->all();

            $response = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(20)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemPrompt],
                    ],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => 500,
                    'temperature' => 0.4,
                ],
            ]);

            if ($response->successful()) {
                return [
                    'reply' => $response->json('candidates.0.content.parts.0.text') ?? 'Xin lỗi, tôi chưa có câu trả lời phù hợp.',
                    'usage' => $response->json('usageMetadata'),
                ];
            }

            Log::warning('Gemini chatbot API failed', [
                'status' => $response->status(),
                'body' => $response->json() ?: $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini chatbot exception', [
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }

    // ─── FALLBACK KHI CHƯA CÓ API KEY ───────────────────
    private function hasRecentIntent(array $messages, array $keywords): bool
    {
        $recentText = collect($messages)
            ->take(-6)
            ->pluck('content')
            ->join(' ');

        $recentText = mb_strtolower($recentText);

        foreach ($keywords as $keyword) {
            if (str_contains($recentText, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function hasBudgetSignal(string $message): bool
    {
        $msg = mb_strtolower($message);

        return str_contains($msg, 'ngân sách')
            || str_contains($msg, 'ngan sach')
            || str_contains($msg, 'triệu')
            || str_contains($msg, 'trieu')
            || preg_match('/\d+\s*(m|tr|trieu|triệu)/u', $msg);
    }

    private function ruleBasedResponse(string $message, string $context, array $messages = []): string
    {
        $msg = mb_strtolower($message);

        if ($this->isProductListRequest($message)) {
            return "Dạ, em gửi anh/chị các sản phẩm hiện có bên dưới. Anh/chị có thể bấm xem chi tiết từng mẫu.";
        }

        if ($this->hasBudgetSignal($message) && $this->hasRecentIntent($messages, ['gaming', 'game'])) {
            return "Với nhu cầu gaming và ngân sách của anh/chị, em ưu tiên laptop có GPU rời, RAM tối thiểu 16GB và SSD 512GB. Em gửi các mẫu phù hợp bên dưới để anh/chị xem nhanh.";
        }

        if ($this->hasBudgetSignal($message)) {
            return "Dạ, với ngân sách anh/chị đưa ra, em gửi các mẫu đang bán bên dưới. Anh/chị dùng chủ yếu để học tập, gaming hay đồ họa để em lọc kỹ hơn?";
        }

        if (str_contains($msg, 'gaming') || str_contains($msg, 'game')) {
            return "Để chơi game tốt, bạn cần laptop có GPU riêng (RTX 3050 trở lên), RAM 16GB, màn hình 144Hz. Ngân sách phổ biến từ 18-30 triệu. Bạn có ngân sách bao nhiêu để tôi tư vấn cụ thể hơn?";
        }
        if (str_contains($msg, 'sinh viên') || str_contains($msg, 'học')) {
            return "Laptop cho sinh viên cần nhẹ, pin trâu, giá hợp lý. Tôi khuyên nên chọn CPU Intel Core i5 gen 12/13, RAM 8GB, SSD 512GB, giá 12-18 triệu. Bạn học ngành gì để tôi tư vấn phù hợp hơn?";
        }
        if (str_contains($msg, 'đồ họa') || str_contains($msg, 'design') || str_contains($msg, 'render')) {
            return "Laptop đồ họa cần màn hình màu sắc chuẩn, GPU mạnh, RAM 16GB trở lên. Các dòng phù hợp: Asus ProArt, Dell XPS, MacBook Pro. Ngân sách từ 25-40 triệu.";
        }
        if (str_contains($msg, 'giá') || str_contains($msg, 'rẻ') || str_contains($msg, 'triệu')) {
            return "Dạ, tôi có thể tư vấn theo ngân sách:\n• Dưới 12 triệu: Acer Aspire, Lenovo IdeaPad\n• 12-18 triệu: Asus VivoBook, Dell Inspiron\n• 18-25 triệu: HP Envy, Lenovo ThinkPad\n• Trên 25 triệu: Gaming, MacBook\n\nBạn muốn xem dòng nào?";
        }

        return "Cảm ơn câu hỏi của bạn! Để tư vấn chính xác hơn, bạn cho tôi biết:\n1. Mục đích sử dụng (học tập/gaming/đồ họa)?\n2. Ngân sách dự kiến?\n3. Ưu tiên pin hay hiệu năng?\n\nTôi sẽ gợi ý laptop phù hợp nhất cho bạn! 😊";
    }
}
