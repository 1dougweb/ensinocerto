<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Matricula;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }
    public function sendPaymentWhatsApp(Request $request)
    {
        // Log dos dados recebidos para debug
        Log::info('WhatsApp request data', [
            'all_data' => $request->all(),
            'matricula_id' => $request->matricula_id,
            'payment_type' => $request->payment_type,
            'payment_data' => $request->payment_data,
            'student_phone' => $request->student_phone,
            'student_name' => $request->student_name,
            'course_name' => $request->course_name
        ]);
        
        try {
            $request->validate([
                'matricula_id' => 'nullable|exists:matriculas,id',
                'payment_type' => 'required|in:pix,boleto,cartao',
                'payment_data' => 'required|array',
                'student_phone' => 'required|string',
                'student_name' => 'required|string',
                'course_name' => 'required|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed for WhatsApp', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos: ' . collect($e->errors())->flatten()->first(),
                'details' => $e->errors()
            ], 422);
        }

        try {
            $matricula = $request->matricula_id ? Matricula::findOrFail($request->matricula_id) : null;
            $paymentType = $request->payment_type;
            $paymentData = $request->payment_data;
            $studentPhone = $this->formatPhone($request->student_phone);
            $studentName = $request->student_name;
            $courseName = $request->course_name;

            // Gerar mensagem baseada no tipo de pagamento
            $message = $this->generatePaymentMessage($paymentType, $paymentData, $studentName, $courseName, $matricula);

            // Verificar se WhatsApp está configurado
            if (!$this->whatsAppService->hasValidSettings()) {
                return response()->json([
                    'success' => false,
                    'error' => 'WhatsApp não está configurado. Acesse as configurações para conectar.'
                ], 500);
            }

            // Enviar mensagem via Evolution API dependendo do tipo
            $result = $this->sendPaymentMessage($paymentType, $paymentData, $studentPhone, $message);

            if ($result['success']) {
                Log::info('WhatsApp enviado via Evolution API', [
                    'matricula_id' => $matricula ? $matricula->id : null,
                    'payment_type' => $paymentType,
                    'phone' => $studentPhone,
                    'message_id' => $result['message_id'] ?? null
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Mensagem enviada via WhatsApp com sucesso!',
                    'data' => $result
                ]);
            } else {
                throw new \Exception($result['error'] ?? 'Erro desconhecido ao enviar WhatsApp');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar WhatsApp', [
                'error' => $e->getMessage(),
                'matricula_id' => $request->matricula_id
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generatePaymentMessage($paymentType, $paymentData, $studentName, $courseName, $matricula)
    {
        $baseMessage = "🎓 *Ensino Certo - Pagamento Disponível*\n\n";
        $baseMessage .= "Olá *{$studentName}*!\n\n";
        $baseMessage .= "Seu pagamento do curso *{$courseName}* está disponível:\n\n";

        switch ($paymentType) {
            case 'pix':
                $message = $baseMessage;
                $message .= "📱 *PAGAMENTO VIA PIX*\n\n";
                
                // Inclui QR Code em Base64 se disponível
                if (isset($paymentData['qr_code_base64'])) {
                    // Nota: WhatsApp Web não suporta envio direto de imagens via URL API
                    // O QR Code será mostrado no modal da aplicação
                    $message .= "🖼️ *QR Code disponível no sistema*\n\n";
                }
                
                if (isset($paymentData['qr_code'])) {
                    $message .= "🔢 *Código PIX (Copia e Cola):*\n";
                    $message .= "`{$paymentData['qr_code']}`\n\n";
                }
                
                $message .= "📋 *Como pagar:*\n";
                $message .= "1️⃣ Abra o app do seu banco\n";
                $message .= "2️⃣ Vá em PIX → Copia e Cola\n";
                $message .= "3️⃣ Cole o código acima\n";
                $message .= "4️⃣ Confirme o pagamento\n\n";
                $message .= "⚡ *Pagamento instantâneo!*";
                break;

            case 'boleto':
                $message = $baseMessage;
                $message .= "📄 *BOLETO BANCÁRIO*\n\n";
                
                if (isset($paymentData['ticket_url'])) {
                    $message .= "📎 *Acesse seu boleto em PDF:*\n";
                    $message .= "{$paymentData['ticket_url']}\n\n";
                }
                
                $message .= "📋 *Como pagar:*\n";
                $message .= "1️⃣ Clique no link acima para baixar o PDF\n";
                $message .= "2️⃣ Imprima o boleto ou use o código de barras\n";
                $message .= "3️⃣ Pague em qualquer banco, lotérica ou app\n";
                $message .= "4️⃣ Pagamento processado em até 3 dias úteis\n\n";
                $message .= "📅 *Vencimento:* Confira na data do boleto";
                break;

            case 'cartao':
                $message = $baseMessage;
                $message .= "💳 *CARTÃO DE CRÉDITO*\n\n";
                
                if (isset($paymentData['init_point'])) {
                    $message .= "🔗 *Link de pagamento seguro:*\n";
                    $message .= "{$paymentData['init_point']}\n\n";
                }
                
                $message .= "📋 *Como pagar:*\n";
                $message .= "1️⃣ Clique no link acima\n";
                $message .= "2️⃣ Preencha os dados do seu cartão\n";
                $message .= "3️⃣ Escolha o número de parcelas\n";
                $message .= "4️⃣ Confirme o pagamento\n\n";
                $message .= "🔒 *Ambiente 100% seguro - MercadoPago*\n";
                $message .= "💰 *Aproveite: Parcelamento sem juros!*";
                break;
        }

        $message .= "\n\n";
        $message .= "❓ *Dúvidas?* Responda esta mensagem!\n";
        $message .= "📞 *Contato:* (11) 99999-9999\n";
        $message .= "🌐 *Site:* ensinocerto.com\n\n";
        $message .= "✅ *Comprovante será enviado automaticamente após o pagamento*";

        return $message;
    }

    private function sendPaymentMessage($paymentType, $paymentData, $phone, $message)
    {
        try {
            switch ($paymentType) {
                case 'pix':
                    // Para PIX, enviar mensagem + QR Code se disponível
                    if (isset($paymentData['qr_code_base64']) && isset($paymentData['qr_code'])) {
                        return $this->whatsAppService->sendPixQrCode(
                            $phone, 
                            $paymentData['qr_code'], 
                            $message
                        );
                    } else {
                        // Só mensagem de texto se não tiver QR Code
                        return $this->whatsAppService->sendMessage($phone, $message);
                    }
                    break;

                case 'boleto':
                    // Para boleto, enviar mensagem de texto com link do PDF
                    return $this->whatsAppService->sendMessage($phone, $message);
                    break;

                case 'cartao':
                    // Para cartão, enviar mensagem de texto com link de pagamento
                    return $this->whatsAppService->sendMessage($phone, $message);
                    break;

                default:
                    throw new \Exception('Tipo de pagamento não suportado');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem via WhatsApp Service', [
                'payment_type' => $paymentType,
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function formatPhone($phone)
    {
        // O WhatsAppService já tem seu próprio formatPhone, mas vamos manter consistência
        $phone = preg_replace('/\D/', '', $phone);
        
        // Adiciona código do país se não tiver
        if (strlen($phone) == 10) {
            $phone = '55' . $phone;
        } elseif (strlen($phone) == 11) {
            $phone = '55' . $phone;
        } elseif (strlen($phone) == 13 && substr($phone, 0, 2) == '55') {
            // Já tem código do país
        } else {
            // Formato padrão
            $phone = '5511999999999';
        }
        
        return $phone;
    }
} 