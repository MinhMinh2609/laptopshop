<?php
// app/Http/Controllers/Api/ChatbotController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    // ─── GỬI TIN NHẮN ĐẾN CHATBOT ───────────────────────
    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array|max:10',
        ]);

        $userMessage = $request->message;

        // Lấy ngữ cảnh sản phẩm từ DB để chatbot tư vấn chính xác
        $productContext = $this->getProductContext($userMessage);

        // System prompt cho chatbot
        $systemPrompt = "Bạn là AI tư vấn laptop của LaptopShop, website bán laptop hướng đến sinh viên Việt Nam.
        Bạn giúp khách hàng tìm laptop phù hợp với nhu cầu và ngân sách.
        Trả lời bằng tiếng Việt, ngắn gọn, thân thiện và hữu ích.
        Nếu khách hỏi về sản phẩm cụ thể, hãy dựa vào danh sách sản phẩm bên dưới.
        Không bịa đặt thông tin sản phẩm không có trong danh sách.

        DANH SÁCH SẢN PHẨM HIỆN CÓ:
        {$productContext}

        Khi tư vấn, hãy gợi ý sản phẩm phù hợp và đề nghị khách xem chi tiết trên website.";

        // Xây dựng lịch sử chat
        $messages = [['role' => 'user', 'content' => $systemPrompt . "\n\nTin nhắn đầu tiên của khách: Xin chào!"],
                     ['role' => 'assistant', 'content' => 'Xin chào! Tôi là AI tư vấn laptop của LaptopShop. Tôi có thể giúp bạn tìm laptop phù hợp. Bạn cần laptop để làm gì và ngân sách khoảng bao nhiêu?']];

        // Thêm lịch sử chat (bỏ qua tin đầu tiên mặc định)
        if ($request->history) {
            foreach ($request->history as $msg) {
                if (in_array($msg['role'], ['user', 'assistant'])) {
                    $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
                }
            }
        }

        // Thêm tin nhắn hiện tại
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        // Gọi API AI (OpenAI / Gemini)
        $apiKey = env('AI_CHATBOT_API_KEY');

        if (!$apiKey || $apiKey === 'your_openai_or_gemini_api_key') {
            // Fallback khi chưa có API key — dùng rule-based
            $reply = $this->ruleBasedResponse($userMessage, $productContext);
        } else {
            $reply = $this->callOpenAI($messages, $apiKey);
        }

        return response()->json([
            'success' => true,
            'data'    => ['reply' => $reply],
        ]);
    }

    // ─── LẤY NGỮ CẢNH SẢN PHẨM ─────────────────────────
    private function getProductContext(string $message): string
    {
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
            ->take(5)
            ->get();

        if ($products->isEmpty()) {
            // Lấy 5 sản phẩm nổi bật
            $products = Product::with('brand')->where('is_active', 1)
                ->where('is_featured', 1)->take(5)->get();
        }

        return $products->map(fn($p) =>
            "- {$p->name} | Hãng: {$p->brand->name} | Giá: " . number_format($p->sale_price ?? $p->price) . "đ | CPU: {$p->cpu} | RAM: {$p->ram} | SSD: {$p->storage}"
        )->join("\n");
    }

    // ─── GỌI OPENAI API ──────────────────────────────────
    private function callOpenAI(array $messages, string $apiKey): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                'model'       => env('AI_CHATBOT_MODEL', 'gpt-3.5-turbo'),
                'messages'    => $messages,
                'max_tokens'  => 300,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? 'Xin lỗi, tôi không hiểu câu hỏi của bạn.';
            }
        } catch (\Exception $e) {
            // Log lỗi
        }

        return 'Xin lỗi, dịch vụ tư vấn AI tạm thời gián đoạn. Vui lòng thử lại sau hoặc liên hệ hotline để được hỗ trợ.';
    }

    // ─── FALLBACK KHI CHƯA CÓ API KEY ───────────────────
    private function ruleBasedResponse(string $message, string $context): string
    {
        $msg = mb_strtolower($message);

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