<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\BoletoSecondViaService;
use Illuminate\Support\Facades\Http;
use App\Models\SystemSetting;

class MercadoPagoPaymentController extends Controller
{
    protected $boletoService;
    
    public function __construct(BoletoSecondViaService $boletoService)
    {
        $this->boletoService = $boletoService;
    }
    
    /**
     * Gerar link direto de pagamento do Mercado Pago
     */
    public function generatePaymentLink(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|integer|exists:payments,id',
                'email' => 'required|email'
            ]);
            
            $payment = Payment::with('matricula')->findOrFail($request->payment_id);
            
            // Verificar se o email corresponde à matrícula
            if ($payment->matricula->email !== $request->email) {
                return response()->json([
                    'error' => 'Email não autorizado para este pagamento'
                ], 403);
            }
            
            // Verificar se o pagamento está vencido e pendente
            if ($payment->data_vencimento >= now() || $payment->status !== 'pending') {
                return response()->json([
                    'error' => 'Pagamento não elegível para link de pagamento'
                ], 400);
            }
            
            // Verificar se já existe um link válido no banco
            if ($payment->payment_link && $payment->payment_link_expires_at && $payment->payment_link_expires_at > now()) {
                $paymentLink = $payment->payment_link;
            } else {
                // Gerar novo link baseado na forma de pagamento
                $paymentLink = $this->generateMercadoPagoLink($payment);
                
                // Salvar o link no banco de dados
                $this->savePaymentLink($payment, $paymentLink);
            }
            
            return response()->json([
                'success' => true,
                'payment_link' => $paymentLink,
                'payment_info' => [
                    'id' => $payment->id,
                    'descricao' => $payment->descricao,
                    'valor' => $payment->valor,
                    'data_vencimento' => $payment->data_vencimento->format('d/m/Y'),
                    'forma_pagamento' => $payment->forma_pagamento,
                    'days_overdue' => now()->diffInDays($payment->data_vencimento)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao gerar link de pagamento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Gerar link específico do Mercado Pago baseado na forma de pagamento
     */
    protected function generateMercadoPagoLink(Payment $payment): string
    {
        // Usar as configurações corretas do Mercado Pago
        $isSandbox = SystemSetting::get('mercadopago_sandbox', false);
        $accessToken = $isSandbox 
            ? SystemSetting::get('mercadopago_sandbox_access_token')
            : SystemSetting::get('mercadopago_access_token');
        
        if (!$accessToken) {
            throw new \Exception('Token do Mercado Pago não configurado');
        }
        
        // Mapear formas de pagamento para os tipos corretos
        $paymentType = $this->mapPaymentType($payment->forma_pagamento);
        
        switch ($paymentType) {
            case 'boleto':
                return $this->generateBoletoLink($payment, $accessToken);
                
            case 'pix':
                return $this->generatePixLink($payment, $accessToken);
                
            case 'cartao':
                return $this->generateCardLink($payment, $accessToken);
                
            default:
                return $this->generateGenericLink($payment, $accessToken);
        }
    }
    
    /**
     * Mapear forma de pagamento para tipo do Mercado Pago
     */
    protected function mapPaymentType(string $formaPagamento): string
    {
        $mapping = [
            'boleto' => 'boleto',
            'cartao_credito' => 'cartao',
            'cartao_debito' => 'cartao',
            'cartao' => 'cartao',
            'pix' => 'pix',
            'pagamento_a_vista' => 'boleto', // Padrão para pagamento à vista
            'pagamento_parcelado' => 'cartao'
        ];
        
        return $mapping[$formaPagamento] ?? 'boleto';
    }
    
    /**
     * Gerar link para boleto
     */
    protected function generateBoletoLink(Payment $payment, string $accessToken): string
    {
        try {
            // Verificar se pode gerar segunda via
            if (!$this->boletoService->canGenerateSecondVia($payment)) {
                throw new \Exception('Pagamento não elegível para segunda via');
            }
            
            // Gerar nova via do boleto
            $boletoVia = $this->boletoService->generateSecondVia($payment);
            
            if ($boletoVia && $boletoVia->boleto_url) {
                return $boletoVia->boleto_url;
            }
            
            throw new \Exception('Não foi possível gerar o boleto');
            
        } catch (\Exception $e) {
            // Se falhar, criar link direto para geração
            return $this->createDirectBoletoLink($payment, $accessToken);
        }
    }
    
    /**
     * Criar link direto para boleto no Mercado Pago
     */
    protected function createDirectBoletoLink(Payment $payment, string $accessToken): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'boleto_' . $payment->id . '_' . time()
            ])->post('https://api.mercadopago.com/v1/orders', [
                'type' => 'online',
                'processing_mode' => 'automatic',
                'external_reference' => 'payment_' . $payment->id,
                'total_amount' => $payment->valor * 100, // Mercado Pago usa centavos
                'description' => $payment->descricao,
                'payer' => [
                    'email' => $payment->matricula->email,
                    'first_name' => explode(' ', $payment->matricula->nome_completo)[0] ?? '',
                    'last_name' => explode(' ', $payment->matricula->nome_completo)[1] ?? '',
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $payment->matricula->cpf ?? '00000000000'
                    ]
                ],
                'transactions' => [
                    [
                        'payments' => [
                            [
                                'payment_method' => [
                                    'id' => 'boleto',
                                    'type' => 'ticket'
                                ],
                                'expiration_time' => 'P3D' // 3 dias
                            ]
                        ]
                    ]
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['ticket_url'] ?? config('app.url') . '/api/boleto/generate-second-via?payment_id=' . $payment->id;
            }
            
            throw new \Exception('Erro na API do Mercado Pago: ' . $response->body());
            
        } catch (\Exception $e) {
            // Fallback para link de geração
            return config('app.url') . '/api/boleto/generate-second-via?payment_id=' . $payment->id;
        }
    }
    
    /**
     * Gerar link para PIX
     */
    protected function generatePixLink(Payment $payment, string $accessToken): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'pix_' . $payment->id . '_' . time()
            ])->post('https://api.mercadopago.com/v1/orders', [
                'type' => 'online',
                'processing_mode' => 'automatic',
                'external_reference' => 'payment_' . $payment->id,
                'total_amount' => $payment->valor * 100,
                'description' => $payment->descricao,
                'payer' => [
                    'email' => $payment->matricula->email,
                    'first_name' => explode(' ', $payment->matricula->nome_completo)[0] ?? '',
                    'last_name' => explode(' ', $payment->matricula->nome_completo)[1] ?? '',
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $payment->matricula->cpf ?? '00000000000'
                    ]
                ],
                'transactions' => [
                    [
                        'payments' => [
                            [
                                'payment_method' => [
                                    'id' => 'pix',
                                    'type' => 'bank_transfer'
                                ],
                                'expiration_time' => 'P1D' // 1 dia
                            ]
                        ]
                    ]
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['ticket_url'] ?? config('app.url') . '/api/payment/generate-pix?payment_id=' . $payment->id;
            }
            
            throw new \Exception('Erro na API do Mercado Pago: ' . $response->body());
            
        } catch (\Exception $e) {
            return config('app.url') . '/api/payment/generate-pix?payment_id=' . $payment->id;
        }
    }
    
    /**
     * Gerar link para cartão
     */
    protected function generateCardLink(Payment $payment, string $accessToken): string
    {
        try {
            // Para cartão, criar preferência de pagamento
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [
                    [
                        'title' => $payment->descricao,
                        'quantity' => 1,
                        'unit_price' => $payment->valor
                    ]
                ],
                'payer' => [
                    'email' => $payment->matricula->email,
                    'name' => $payment->matricula->nome_completo
                ],
                'external_reference' => 'payment_' . $payment->id,
                'notification_url' => config('app.url') . '/api/webhooks/mercadopago',
                'back_urls' => [
                    'success' => config('app.url') . '/payment/success',
                    'failure' => config('app.url') . '/payment/failure',
                    'pending' => config('app.url') . '/payment/pending'
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['init_point'] ?? config('app.url') . '/payment/checkout/' . $payment->id;
            }
            
            throw new \Exception('Erro na API do Mercado Pago: ' . $response->body());
            
        } catch (\Exception $e) {
            return config('app.url') . '/payment/checkout/' . $payment->id;
        }
    }
    
    /**
     * Gerar link genérico
     */
    protected function generateGenericLink(Payment $payment, string $accessToken): string
    {
        // Para formas genéricas, tentar boleto como padrão
        return $this->generateBoletoLink($payment, $accessToken);
    }
    
    /**
     * Salvar link de pagamento no banco de dados
     */
    protected function savePaymentLink(Payment $payment, string $paymentLink): void
    {
        try {
            $paymentType = $this->mapPaymentType($payment->forma_pagamento);
            $expiresAt = now()->addDays(30); // Links expiram em 30 dias
            
            $updateData = [
                'payment_link' => $paymentLink,
                'payment_type' => $paymentType,
                'payment_link_expires_at' => $expiresAt
            ];
            
            // Adicionar campos específicos baseados no tipo
            switch ($paymentType) {
                case 'boleto':
                    $updateData['boleto_url'] = $paymentLink;
                    break;
                    
                case 'pix':
                    // Para PIX, extrair QR code se disponível
                    if (str_contains($paymentLink, 'ticket_url')) {
                        $updateData['pix_qr_code'] = $paymentLink;
                    }
                    break;
                    
                case 'cartao':
                    $updateData['init_point'] = $paymentLink;
                    break;
            }
            
            $payment->update($updateData);
            
        } catch (\Exception $e) {
            // Log do erro, mas não falhar o processo
            \Log::error('Erro ao salvar link de pagamento', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
