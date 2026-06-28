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
            'compare_product_ids' => 'nullable|array|max:3',
            'compare_product_ids.*' => 'integer|distinct|exists:products,id',
        ]);

        $userMessage = $request->message;
        $compareProductIds = array_values($request->input('compare_product_ids') ?: []);

        // Lấy ngữ cảnh sản phẩm từ DB để chatbot tư vấn chính xác
        $productContextData = $this->getProductContext($userMessage, $compareProductIds);
        $productContext = $productContextData['context'];
        $recommendedProducts = $productContextData['products'];
        $productContextTitle = $productContextData['is_compare_context']
            ? 'DANH SÁCH SẢN PHẨM ĐANG SO SÁNH'
            : 'DANH SÁCH SẢN PHẨM HIỆN CÓ';

        // System prompt cho chatbot
        $systemPrompt = "Bạn là chatbot tư vấn bán laptop cho website LaptopShop.

        Quy tắc bắt buộc:
        - Chỉ được tư vấn các sản phẩm xuất hiện trong DANH SÁCH SẢN PHẨM HIỆN CÓ.
        - Không được bịa tên laptop, giá, CPU, RAM, tồn kho, khuyến mãi hoặc thông số không có trong danh sách.
        - Nếu không có sản phẩm phù hợp, hãy nói hiện shop chưa có mẫu phù hợp và hỏi khách có muốn đổi ngân sách/nhu cầu không.
        - Trả lời bằng tiếng Việt, ngắn gọn, thân thiện, ưu tiên tư vấn để khách chọn mua.
        - Khi gợi ý sản phẩm, nêu tên, giá, CPU, RAM, SSD, GPU, tình trạng còn hàng nếu có.
        - Khi người dùng hỏi so sánh, hãy phân tích rõ máy nào hơn ở CPU, RAM, SSD, GPU, màn hình, pin/trọng lượng nếu dữ liệu có, giá bán và kết luận nên chọn máy nào theo nhu cầu.

        {$productContextTitle}:
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

        if ($this->isCompareRequest($userMessage) && $recommendedProducts->count() < 2) {
            $reply = $this->ruleBasedResponse($userMessage, $productContext, $messages, $recommendedProducts);
        } elseif (!$apiKey || $apiKey === 'your_gemini_api_key') {
            // Fallback khi chưa có API key — dùng rule-based
            $reply = $this->ruleBasedResponse($userMessage, $productContext, $messages, $recommendedProducts);
        } else {
            $geminiResult = $this->callGemini($systemPrompt, $messages, $apiKey);
            $reply = $geminiResult['reply'] ?? null;
            $tokenUsage = $geminiResult['usage'] ?? null;

            if (!$reply) {
                $reply = $this->ruleBasedResponse($userMessage, $productContext, $messages, $recommendedProducts);
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
    private function getProductContext(string $message, array $compareProductIds = []): array
    {
        $limit = $this->productResultLimit($message);
        $priceFilter = $this->extractPriceFilter($message);

        if ($this->isCompareRequest($message) && count($compareProductIds) < 2) {
            return [
                'context' => $this->formatProductContext(collect()),
                'products' => collect(),
                'is_compare_context' => true,
            ];
        }

        if ($this->isCompareRequest($message) && count($compareProductIds) >= 2) {
            $products = Product::with('brand')
                ->where('is_active', 1)
                ->whereIn('id', $compareProductIds)
                ->get()
                ->sortBy(fn($product) => array_search($product->id, $compareProductIds))
                ->values();

            return [
                'context' => $this->formatProductContext($products),
                'products' => $products,
                'is_compare_context' => true,
            ];
        }

        if ($this->isProductListRequest($message)) {
            $products = Product::with('brand')
                ->where('is_active', 1)
                ->when($priceFilter['min'], fn($q, $min) => $q->whereRaw('COALESCE(sale_price, price) >= ?', [$min]))
                ->when($priceFilter['max'], fn($q, $max) => $q->whereRaw('COALESCE(sale_price, price) <= ?', [$max]))
                ->when($priceFilter['min'] || $priceFilter['max'], fn($q) => $q->orderByRaw('COALESCE(sale_price, price) asc'))
                ->latest()
                ->take($limit)
                ->get();

            return [
                'context' => $this->formatProductContext($products),
                'products' => $products,
                'is_compare_context' => false,
            ];
        }

        // Tìm sản phẩm liên quan đến câu hỏi
        $products = Product::with('brand')
            ->where('is_active', 1)
            ->when($priceFilter['min'], fn($q, $min) => $q->whereRaw('COALESCE(sale_price, price) >= ?', [$min]))
            ->when($priceFilter['max'], fn($q, $max) => $q->whereRaw('COALESCE(sale_price, price) <= ?', [$max]))
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
            ->when($priceFilter['min'] || $priceFilter['max'], fn($q) => $q->orderByRaw('COALESCE(sale_price, price) asc'))
            ->take($limit)
            ->get();

        if ($products->isEmpty()) {
            // Lấy sản phẩm nổi bật nếu câu hỏi không khớp từ khóa cụ thể
            $products = Product::with('brand')->where('is_active', 1)
                ->when($priceFilter['min'], fn($q, $min) => $q->whereRaw('COALESCE(sale_price, price) >= ?', [$min]))
                ->when($priceFilter['max'], fn($q, $max) => $q->whereRaw('COALESCE(sale_price, price) <= ?', [$max]))
                ->where('is_featured', 1)
                ->when($priceFilter['min'] || $priceFilter['max'], fn($q) => $q->orderByRaw('COALESCE(sale_price, price) asc'))
                ->take($limit)
                ->get();
        }

        if ($products->isEmpty()) {
            // Nếu chưa đánh dấu sản phẩm nổi bật, vẫn lấy các sản phẩm đang bán
            $products = Product::with('brand')
                ->where('is_active', 1)
                ->when($priceFilter['min'], fn($q, $min) => $q->whereRaw('COALESCE(sale_price, price) >= ?', [$min]))
                ->when($priceFilter['max'], fn($q, $max) => $q->whereRaw('COALESCE(sale_price, price) <= ?', [$max]))
                ->when($priceFilter['min'] || $priceFilter['max'], fn($q) => $q->orderByRaw('COALESCE(sale_price, price) asc'))
                ->latest()
                ->take($limit)
                ->get();
        }

        return [
            'context' => $this->formatProductContext($products),
            'products' => $products,
            'is_compare_context' => false,
        ];
    }

    private function extractPriceFilter(string $message): array
    {
        $msg = mb_strtolower($message);
        $number = '(\d+(?:[\.,]\d+)?)';
        $unit = '(?:tr|trieu|triệu|m)';

        if (preg_match("/{$number}\s*(?:-|đến|den|tới|toi)\s*{$number}\s*{$unit}/u", $msg, $matches)) {
            return [
                'min' => $this->millionToVnd($matches[1]),
                'max' => $this->millionToVnd($matches[2]),
            ];
        }

        if (preg_match("/(?:dưới|duoi|nhỏ hơn|nho hon|tối đa|toi da|<=|<)\s*{$number}\s*{$unit}/u", $msg, $matches)) {
            return [
                'min' => null,
                'max' => $this->millionToVnd($matches[1]),
            ];
        }

        if (preg_match("/(?:trên|tren|lớn hơn|lon hon|tối thiểu|toi thieu|>=|>)\s*{$number}\s*{$unit}/u", $msg, $matches)) {
            return [
                'min' => $this->millionToVnd($matches[1]),
                'max' => null,
            ];
        }

        if ($this->hasBudgetSignal($message) && preg_match("/{$number}\s*{$unit}/u", $msg, $matches)) {
            return [
                'min' => null,
                'max' => $this->millionToVnd($matches[1]),
            ];
        }

        return ['min' => null, 'max' => null];
    }

    private function millionToVnd(string $value): int
    {
        return (int) round(((float) str_replace(',', '.', $value)) * 1000000);
    }

    private function isCompareRequest(string $message): bool
    {
        $msg = mb_strtolower($message);

        return str_contains($msg, 'so sánh')
            || str_contains($msg, 'so sanh')
            || str_contains($msg, 'khác nhau')
            || str_contains($msg, 'khac nhau')
            || str_contains($msg, 'máy nào hơn')
            || str_contains($msg, 'may nao hon')
            || str_contains($msg, 'nên chọn máy nào')
            || str_contains($msg, 'nen chon may nao')
            || (
                (str_contains($msg, 'phân tích') || str_contains($msg, 'phan tich') || str_contains($msg, 'đánh giá') || str_contains($msg, 'danh gia'))
                && (
                    str_contains($msg, 'máy này')
                    || str_contains($msg, 'may nay')
                    || str_contains($msg, 'sản phẩm này')
                    || str_contains($msg, 'san pham nay')
                    || str_contains($msg, '2 máy')
                    || str_contains($msg, '2 may')
                    || str_contains($msg, 'hai máy')
                    || str_contains($msg, 'hai may')
                )
            );
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

        return $products->map(function ($p) {
            $brand = $p->brand?->name ?? 'N/A';
            $price = number_format($p->sale_price ?? $p->price);
            return "- ID: {$p->id} | {$p->name} | Hãng: {$brand} | Giá: {$price}đ | CPU: {$p->cpu} | RAM: {$p->ram} | SSD: {$p->storage} | GPU: {$p->gpu} | Màn hình: {$p->display} | Pin: {$p->battery} | Trọng lượng: {$p->weight} | Tồn kho: {$p->stock} | Link: /products/{$p->slug}";
        })->join("\n");
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
            'os' => $p->os,
            'battery' => $p->battery,
            'weight' => $p->weight,
        ])->values()->all();
    }

    private function buildComparisonResponse($products): string
    {
        $products = $products->values();

        if ($products->count() < 2) {
            return "Dạ, anh/chị cần thêm ít nhất 2 sản phẩm vào khay so sánh trước. Sau đó hỏi lại “so sánh 2 sản phẩm này”, em sẽ phân tích máy nào hơn ở CPU, RAM, SSD, GPU và giá.";
        }

        $wins = [];
        foreach ($products as $product) {
            $wins[$product->id] = 0;
        }

        $lines = [
            'Dạ, em so sánh nhanh các mẫu đang có trong khay:',
        ];

        $cpuBest = $this->bestProductByScore($products, fn($p) => $this->cpuScore($p->cpu));
        if ($cpuBest) {
            $wins[$cpuBest->id]++;
            $lines[] = "- CPU: {$cpuBest->name} nhỉnh hơn với {$cpuBest->cpu}.";
        }

        $ramBest = $this->bestProductByScore($products, fn($p) => $this->capacityScore($p->ram));
        if ($ramBest) {
            $wins[$ramBest->id]++;
            $lines[] = "- RAM: {$ramBest->name} lợi thế hơn ({$ramBest->ram}).";
        }

        $storageBest = $this->bestProductByScore($products, fn($p) => $this->capacityScore($p->storage));
        if ($storageBest) {
            $wins[$storageBest->id]++;
            $lines[] = "- SSD: {$storageBest->name} rộng hơn ({$storageBest->storage}).";
        }

        $gpuBest = $this->bestProductByScore($products, fn($p) => $this->gpuScore($p->gpu));
        if ($gpuBest) {
            $wins[$gpuBest->id]++;
            $lines[] = "- GPU: {$gpuBest->name} mạnh hơn cho gaming/đồ họa ({$gpuBest->gpu}).";
        }

        $priceBest = $this->bestProductByScore($products, fn($p) => $this->actualPrice($p), false);
        if ($priceBest) {
            $wins[$priceBest->id]++;
            $lines[] = "- Giá: {$priceBest->name} dễ mua hơn ({$this->formatVnd($this->actualPrice($priceBest))}).";
        }

        arsort($wins);
        $winnerId = array_key_first($wins);
        $winner = $products->firstWhere('id', $winnerId);

        if ($winner && reset($wins) > 0) {
            $lines[] = "Kết luận: nếu chọn tổng thể theo các thông số hiện có, em nghiêng về {$winner->name}. Nếu anh/chị ưu tiên gaming/đồ họa thì xem dòng GPU; còn ưu tiên tiết kiệm thì chọn mẫu có giá tốt hơn.";
        } else {
            $lines[] = "Kết luận: dữ liệu hiện có chưa đủ để khẳng định máy nào hơn rõ ràng. Anh/chị nên xem thêm nhu cầu chính: học tập, văn phòng, gaming hay đồ họa.";
        }

        return implode("\n", $lines);
    }

    private function bestProductByScore($products, callable $scoreResolver, bool $higherIsBetter = true)
    {
        $scored = $products
            ->map(fn($product) => ['product' => $product, 'score' => $scoreResolver($product)])
            ->filter(fn($item) => $item['score'] !== null)
            ->values();

        if ($scored->count() < 2 || $scored->pluck('score')->unique()->count() < 2) {
            return null;
        }

        return $scored
            ->sortBy(fn($item) => $higherIsBetter ? -$item['score'] : $item['score'])
            ->first()['product'];
    }

    private function actualPrice($product): float
    {
        return (float) ($product->sale_price ?: $product->price ?: 0);
    }

    private function formatVnd(float $value): string
    {
        return number_format($value, 0, ',', '.') . 'đ';
    }

    private function capacityScore(?string $value): ?float
    {
        if (!$value) {
            return null;
        }

        preg_match_all('/(\d+(?:[\.,]\d+)?)\s*(tb|gb)/i', $value, $matches, PREG_SET_ORDER);
        if (!$matches) {
            return null;
        }

        $total = 0;
        foreach ($matches as $match) {
            $number = (float) str_replace(',', '.', $match[1]);
            $total += strtolower($match[2]) === 'tb' ? $number * 1024 : $number;
        }

        return $total;
    }

    private function cpuScore(?string $cpu): ?float
    {
        if (!$cpu) {
            return null;
        }

        $text = mb_strtolower($cpu);
        $score = 0;

        foreach (['i9' => 900, 'ryzen 9' => 900, 'ultra 9' => 880, 'i7' => 700, 'ryzen 7' => 700, 'ultra 7' => 680, 'i5' => 500, 'ryzen 5' => 500, 'ultra 5' => 480, 'i3' => 300, 'ryzen 3' => 300] as $needle => $value) {
            if (str_contains($text, $needle)) {
                $score = max($score, $value);
            }
        }

        if (preg_match('/\b(1[0-9]|[7-9])\d{2,3}[a-z]*\b/i', $cpu, $matches)) {
            $score += ((int) substr($matches[0], 0, 2)) * 5;
        }

        return $score ?: null;
    }

    private function gpuScore(?string $gpu): ?float
    {
        if (!$gpu) {
            return null;
        }

        $text = mb_strtolower($gpu);

        if (str_contains($text, 'integrated') || str_contains($text, 'iris') || str_contains($text, 'uhd') || str_contains($text, 'radeon graphics')) {
            return 100;
        }

        if (preg_match('/rtx\s*(\d{4})/i', $gpu, $matches)) {
            return 3000 + (int) $matches[1];
        }

        if (preg_match('/gtx\s*(\d{4})/i', $gpu, $matches)) {
            return 2000 + (int) $matches[1];
        }

        if (preg_match('/rx\s*(\d{4})/i', $gpu, $matches)) {
            return 2500 + (int) $matches[1];
        }

        return 500;
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

    private function ruleBasedResponse(string $message, string $context, array $messages = [], $products = null): string
    {
        $msg = mb_strtolower($message);

        if ($this->isCompareRequest($message)) {
            return $this->buildComparisonResponse(collect($products));
        }

        if (!str_contains($context, '- ID:')) {
            return "Dạ, hiện shop chưa có mẫu phù hợp với ngân sách/nhu cầu này. Anh/chị có thể đổi khoảng giá hoặc nói thêm mục đích sử dụng để em lọc lại.";
        }

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
