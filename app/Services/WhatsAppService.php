<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;
    protected $apiKey;
    protected $instance;
    
    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Carregar configurações do banco de dados
     */
    protected function loadSettings()
    {
        // Verificar se estamos em contexto de migração ou teste
        if ($this->isInMigrationContext()) {
            $this->baseUrl = '';
            $this->apiKey = '';
            $this->instance = 'default';
            return;
        }

        try {
            $this->baseUrl = SystemSetting::get('evolution_api_base_url', '');
            $this->apiKey = SystemSetting::get('evolution_api_key', '');
            $this->instance = SystemSetting::get('evolution_api_instance', 'default');
        } catch (\Exception $e) {
            // Se falhar ao acessar configurações (ex: durante migrações), usar configurações padrão
            Log::warning('Erro ao carregar configurações do WhatsApp, usando configurações padrão: ' . $e->getMessage());
            $this->baseUrl = '';
            $this->apiKey = '';
            $this->instance = 'default';
        }
    }

    /**
     * Verificar se estamos em contexto de migração ou comando artisan
     */
    private function isInMigrationContext(): bool
    {
        // Verificar se estamos rodando via linha de comando
        if (app()->runningInConsole()) {
            $command = $_SERVER['argv'][1] ?? '';
            
            // Lista de comandos que não devem tentar acessar o banco
            $migrationCommands = [
                'migrate',
                'migrate:fresh',
                'migrate:refresh',
                'migrate:reset',
                'migrate:rollback',
                'migrate:status',
                'db:seed',
                'db:wipe',
                'config:cache',
                'config:clear',
                'cache:clear',
                'route:cache',
                'route:clear',
                'view:cache',
                'view:clear',
                'optimize',
                'optimize:clear'
            ];
            
            foreach ($migrationCommands as $migrationCommand) {
                if (str_contains($command, $migrationCommand)) {
                    return true;
                }
            }
        }
        
        // Verificar se estamos em ambiente de teste
        if (app()->environment('testing')) {
            return true;
        }
        
        return false;
    }

    /**
     * Atualizar configurações
     */
    public function updateSettings($baseUrl, $apiKey, $instance)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->instance = $instance;

        // Salvar as configurações individuais
        SystemSetting::set('evolution_api_base_url', $baseUrl, 'string', 'evolution_api', 'URL base da Evolution API');
        SystemSetting::set('evolution_api_key', $apiKey, 'string', 'evolution_api', 'Chave da Evolution API');
        SystemSetting::set('evolution_api_instance', $instance, 'string', 'evolution_api', 'Nome da instância da Evolution API');
        SystemSetting::set('evolution_api_connected', false, 'boolean', 'evolution_api', 'Status da conexão');
        SystemSetting::set('evolution_api_last_connection', '', 'string', 'evolution_api', 'Última conexão');
    }

    /**
     * Verificar se as configurações estão completas
     */
    public function hasValidSettings()
    {
        return !empty($this->baseUrl) && !empty($this->apiKey) && !empty($this->instance);
    }

    /**
     * Criar uma nova instância do WhatsApp
     */
    public function createInstance()
    {
        if (!$this->hasValidSettings()) {
            throw new \Exception('Configurações da API não estão completas');
        }

        try {
            // Primeiro, verificar se a instância já existe
            if ($this->instanceExists()) {
                Log::info('Instância já existe, retornando informações');
                
                return [
                    'instance' => [
                        'instanceName' => $this->instance,
                        'status' => 'already_exists'
                    ],
                    'message' => 'Instância já existe. Use "Gerar QR Code" para conectar.'
                ];
            }

            Log::info('Criando nova instância WhatsApp', [
                'instance' => $this->instance,
                'base_url' => $this->baseUrl,
                'full_url' => "{$this->baseUrl}/instance/create"
            ]);

            // Testar conectividade primeiro
            try {
                $pingResponse = Http::timeout(10)->get($this->baseUrl . '/manager/fetchInstances');
                if (!$pingResponse->successful()) {
                    Log::warning('Servidor Evolution API não está respondendo adequadamente', [
                        'status' => $pingResponse->status()
                    ]);
                }
            } catch (\Exception $pingError) {
                Log::warning('Problema de conectividade detectado antes da criação', [
                    'error' => $pingError->getMessage()
                ]);
            }

            // Criar nova instância
            $response = Http::timeout(90) // Aumentar timeout para 90 segundos
                ->connectTimeout(20) // Timeout de conexão
                ->retry(3, 3000) // Tentar 3 vezes com 3 segundos entre tentativas
                ->withHeaders([
                    'apikey' => $this->apiKey,
                    'Content-Type' => 'application/json'
                ])
                ->post("{$this->baseUrl}/instance/create", [
                    'instanceName' => $this->instance,
                    'qrcode' => true,
                    'integration' => 'WHATSAPP-BAILEYS'
                ]);

            Log::info('Resposta da criação de instância', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                $errorBody = $response->body();
                $statusCode = $response->status();
                
                Log::warning('Resposta não bem-sucedida ao criar instância', [
                    'status' => $statusCode,
                    'body' => $errorBody,
                    'headers' => $response->headers()
                ]);
                
                // Verificar se é erro de nome duplicado
                if (str_contains($errorBody, 'already in use') || 
                    str_contains($errorBody, 'já existe') || 
                    str_contains($errorBody, 'Instance already exists') ||
                    $statusCode === 409) {
                    Log::info('Instância já existe (detectado no erro), continuando...');
                    
                    return [
                        'instance' => [
                            'instanceName' => $this->instance,
                            'status' => 'already_exists'
                        ],
                        'message' => 'Instância já existe. Use "Gerar QR Code" para conectar.'
                    ];
                }
                
                // Verificar erros específicos da Evolution API
                $errorMessage = "Erro HTTP {$statusCode}";
                if ($statusCode === 401) {
                    $errorMessage = 'API Key inválida ou expirada. Verifique suas credenciais.';
                } elseif ($statusCode === 403) {
                    $errorMessage = 'Acesso negado. Verifique as permissões da API Key.';
                } elseif ($statusCode === 404) {
                    $errorMessage = 'Endpoint não encontrado. Verifique a URL da API.';
                } elseif ($statusCode >= 500) {
                    $errorMessage = 'Erro interno do servidor Evolution API. Tente novamente em alguns minutos.';
                } else {
                    $errorMessage = "Erro {$statusCode}: {$errorBody}";
                }
                
                Log::error('Erro ao criar instância - detalhado', [
                    'status' => $statusCode,
                    'body' => $errorBody,
                    'instance' => $this->instance,
                    'base_url' => $this->baseUrl
                ]);
                
                throw new \Exception($errorMessage);
            }

            $data = $response->json();
            Log::info('Instância WhatsApp criada com sucesso', $data);
            
            // Aguardar um pouco para a instância inicializar
            sleep(2);
            
            return $data;
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $errorMsg = 'Erro de conexão: Não foi possível conectar ao servidor da Evolution API. Verifique se o servidor está online.';
            Log::error('Erro de conexão ao criar instância WhatsApp', [
                'error' => $e->getMessage(),
                'base_url' => $this->baseUrl,
                'instance' => $this->instance
            ]);
            throw new \Exception($errorMsg);
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Em caso de timeout, verificar se a instância foi criada mesmo assim
            Log::warning('Timeout detectado, verificando se instância foi criada...', [
                'error' => $e->getMessage(),
                'instance' => $this->instance
            ]);
            
            // Aguardar um pouco e verificar se a instância foi criada
            sleep(5);
            
            try {
                if ($this->instanceExists()) {
                    Log::info('Instância foi criada apesar do timeout');
                    return [
                        'instance' => [
                            'instanceName' => $this->instance,
                            'status' => 'created_after_timeout'
                        ],
                        'message' => 'Instância criada com sucesso (verificação após timeout)'
                    ];
                }
            } catch (\Exception $verifyError) {
                Log::warning('Erro ao verificar instância após timeout: ' . $verifyError->getMessage());
            }
            
            $errorMsg = 'Timeout: A criação da instância demorou mais que o esperado. A instância pode ter sido criada. Verifique o status na página.';
            Log::error('Timeout confirmado ao criar instância WhatsApp', [
                'error' => $e->getMessage(),
                'instance' => $this->instance,
                'base_url' => $this->baseUrl
            ]);
            throw new \Exception($errorMsg);
            
        } catch (\Exception $e) {
            Log::error('Erro geral ao criar instância WhatsApp', [
                'error' => $e->getMessage(),
                'instance' => $this->instance,
                'base_url' => $this->baseUrl,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obter QR Code para conexão
     */
    public function getQrCode()
    {
        if (!$this->hasValidSettings()) {
            throw new \Exception('Configurações da API não estão completas');
        }

        try {
            // Primeiro, verificar se a instância existe
            if (!$this->instanceExists()) {
                // Se não existe, criar uma nova
                Log::info('Instância não existe, criando nova...');
                $createResult = $this->createInstance();
                
                if (!isset($createResult['instance'])) {
                    throw new \Exception('Falha ao criar instância');
                }
            }

            // Tentar obter QR Code
            Log::info('Solicitando QR Code', [
                'instance' => $this->instance,
                'url' => "{$this->baseUrl}/instance/connect/{$this->instance}"
            ]);
            
            $response = Http::timeout(60) // Aumentar timeout para QR Code
                ->connectTimeout(15)
                ->retry(2, 2000) // 2 tentativas com 2 segundos entre elas
                ->withHeaders([
                    'apikey' => $this->apiKey
                ])
                ->get("{$this->baseUrl}/instance/connect/{$this->instance}");

            Log::info('Resposta do endpoint connect', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Se retornou 401, a instância pode ter sido deletada
            if ($response->status() === 401) {
                Log::info('Erro 401 - tentando recriar instância');
                
                // Tentar criar nova instância
                $createResult = $this->createInstance();
                
                if (isset($createResult['instance'])) {
                    // Aguardar um pouco e tentar novamente
                    sleep(2);
                    
                    $response = Http::timeout(60)
                        ->connectTimeout(15)
                        ->withHeaders([
                            'apikey' => $this->apiKey
                        ])
                        ->get("{$this->baseUrl}/instance/connect/{$this->instance}");
                }
            }

            if (!$response->successful()) {
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                Log::error('Erro ao obter QR Code', [
                    'status' => $statusCode,
                    'body' => $errorBody,
                    'instance' => $this->instance
                ]);
                
                $errorMessage = "Erro ao obter QR Code";
                if ($statusCode === 401) {
                    $errorMessage = 'API Key inválida. Verifique suas credenciais.';
                } elseif ($statusCode === 404) {
                    $errorMessage = 'Instância não encontrada. Crie uma nova instância.';
                } elseif ($statusCode >= 500) {
                    $errorMessage = 'Servidor Evolution API indisponível. Tente novamente em alguns minutos.';
                } else {
                    $errorMessage = "Erro {$statusCode}: {$errorBody}";
                }
                
                throw new \Exception($errorMessage);
            }

            $data = $response->json();
            
            // Log da resposta completa para debug
            Log::info('Resposta completa da API Evolution', $data);
            
            // Se já estiver conectado, não há QR code
            if (isset($data['instance']['state']) && $data['instance']['state'] === 'open') {
                return [
                    'success' => true,
                    'connected' => true,
                    'message' => 'Dispositivo já conectado'
                ];
            }

            // Verificar diferentes formatos de QR code na resposta
            $qrcode = null;
            
            // Formato 1: base64 direto
            if (isset($data['base64']) && !empty($data['base64'])) {
                $qrcode = $data['base64'];
            }
            // Formato 2: dentro de qrcode.base64
            elseif (isset($data['qrcode']['base64']) && !empty($data['qrcode']['base64'])) {
                $qrcode = $data['qrcode']['base64'];
            }
            // Formato 3: qrcode como string
            elseif (isset($data['qrcode']) && is_string($data['qrcode']) && !empty($data['qrcode'])) {
                $qrcode = $data['qrcode'];
            }
            // Formato 4: dentro de qr.base64
            elseif (isset($data['qr']['base64']) && !empty($data['qr']['base64'])) {
                $qrcode = $data['qr']['base64'];
            }

            Log::info('QR Code extraído', [
                'found' => !empty($qrcode),
                'length' => $qrcode ? strlen($qrcode) : 0,
                'available_keys' => array_keys($data)
            ]);

            if (empty($qrcode)) {
                // Se não tem QR code, verificar se precisa aguardar inicialização
                Log::info('QR Code vazio, aguardando inicialização...');
                
                // Aguardar um pouco e tentar novamente
                sleep(3);
                
                $retryResponse = Http::timeout(30)
                    ->withHeaders(['apikey' => $this->apiKey])
                    ->get("{$this->baseUrl}/instance/connect/{$this->instance}");
                
                if ($retryResponse->successful()) {
                    $retryData = $retryResponse->json();
                    Log::info('Segunda tentativa de QR Code', $retryData);
                    
                    // Tentar extrair QR code novamente
                    if (isset($retryData['base64'])) {
                        $qrcode = $retryData['base64'];
                    } elseif (isset($retryData['qrcode']['base64'])) {
                        $qrcode = $retryData['qrcode']['base64'];
                    } elseif (isset($retryData['qrcode']) && is_string($retryData['qrcode'])) {
                        $qrcode = $retryData['qrcode'];
                    }
                }
                
                if (empty($qrcode)) {
                    return [
                        'success' => false,
                        'connected' => false,
                        'message' => 'QR Code não disponível. A instância pode estar inicializando. Aguarde alguns segundos e tente novamente.'
                    ];
                }
            }

            // Verificar se o QR code é válido (deve começar com data:image)
            if (!str_starts_with($qrcode, 'data:image/')) {
                $qrcode = 'data:image/png;base64,' . $qrcode;
            }

            return [
                'success' => true,
                'connected' => false,
                'qrcode' => [
                    'base64' => $qrcode
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao obter QR Code: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Verificar status da conexão
     */
    public function getConnectionStatus()
    {
        if (!$this->hasValidSettings()) {
            return ['connected' => false, 'message' => 'Configurações incompletas'];
        }

        // Primeiro verificar se a instância existe
        if (!$this->instanceExists()) {
            return [
                'connected' => false, 
                'state' => 'not_found',
                'message' => 'Instância não encontrada'
            ];
        }

        try {
            // Usar timeout maior em produção se detectar problema de conectividade
            $timeout = app()->environment('production') ? 30 : 10;
            $connectTimeout = app()->environment('production') ? 15 : 5;
            
            $response = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->withHeaders([
                    'apikey' => $this->apiKey
                ])
                ->get("{$this->baseUrl}/instance/connectionState/{$this->instance}");

            if (!$response->successful()) {
                return ['connected' => false, 'message' => 'Erro ao verificar status'];
            }

            $data = $response->json();
            $connected = isset($data['instance']['state']) && $data['instance']['state'] === 'open';
            
            // Atualizar status no banco
            $this->updateConnectionStatus($connected);
            
            return [
                'connected' => $connected,
                'state' => $data['instance']['state'] ?? 'unknown',
                'message' => $connected ? 'Conectado' : 'Desconectado'
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status da conexão: ' . $e->getMessage());
            return ['connected' => false, 'message' => 'Erro de conexão'];
        }
    }

    /**
     * Atualizar status da conexão no banco
     */
    protected function updateConnectionStatus($connected)
    {
        SystemSetting::set('evolution_api_connected', $connected, 'boolean', 'evolution_api', 'Status da conexão');
        SystemSetting::set('evolution_api_last_connection', now()->toISOString(), 'string', 'evolution_api', 'Última conexão');
    }

    /**
     * Enviar mensagem de confirmação de inscrição
     */
    public function sendInscricaoConfirmation($inscricao)
    {
        try {
            $formSettings = SystemSetting::getFormSettings();
            $cursos = $formSettings['available_courses'] ?? [];
            $modalidades = $formSettings['available_modalities'] ?? [];
            
            $cursoLabel = $cursos[$inscricao->curso] ?? $inscricao->curso;
            $modalidadeLabel = $modalidades[$inscricao->modalidade] ?? $inscricao->modalidade;

            // Buscar template de confirmação de inscrição
            $template = \App\Models\WhatsAppTemplate::where('name', 'inscricao_confirmacao')
                ->where('active', true)
                ->first();

            if ($template) {
                // Preparar dados para o template
                $data = [
                    'nome' => $inscricao->nome,
                    'curso' => $cursoLabel,
                    'modalidade' => $modalidadeLabel,
                    'data' => $inscricao->created_at->format('d/m/Y H:i')
                ];

                // Validar se todas as variáveis necessárias estão presentes
                if (!$template->validateVariables($data)) {
                    throw new \Exception('Dados insuficientes para o template de confirmação de inscrição');
                }

                // Substituir variáveis e enviar mensagem
                $message = $template->replaceVariables($data);
            } else {
                // Fallback para mensagem padrão se não houver template
                $message = "🎓 *Confirmação de Inscrição*\n\n";
                $message .= "Olá *{$inscricao->nome}*! 👋\n\n";
                $message .= "Recebemos sua inscrição com sucesso!\n\n";
                $message .= "💻 *Modalidade:* {$modalidadeLabel}\n";
                $message .= "📅 *Data:* " . $inscricao->created_at->format('d/m/Y H:i') . "\n\n";
                $message .= "✅ Quais séries você precisa concluir?\n";
                $message .= "✅ Você tem 18 anos completos?";
            }

            return $this->sendMessage($inscricao->telefone, $message);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar confirmação de inscrição WhatsApp: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar mensagem de confirmação de matrícula
     */
    public function sendMatriculaConfirmation($matricula)
    {
        try {
            // Buscar template de confirmação de matrícula
            $template = \App\Models\WhatsAppTemplate::where('name', 'matricula_confirmacao')
                ->where('active', true)
                ->first();

            if ($template) {
                // Preparar dados para o template
                $data = [
                    'nome' => $matricula->nome_completo,
                    'numero_matricula' => $matricula->numero_matricula,
                    'curso' => $matricula->curso,
                    'modalidade' => $matricula->modalidade,
                    'data' => $matricula->created_at->format('d/m/Y')
                ];

                // Validar se todas as variáveis necessárias estão presentes
                if (!$template->validateVariables($data)) {
                    throw new \Exception('Dados insuficientes para o template de confirmação de matrícula');
                }

                // Substituir variáveis e enviar mensagem
                $message = $template->replaceVariables($data);
            }

            return $this->sendMessage($matricula->telefone_celular, $message);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar confirmação de matrícula WhatsApp: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar mensagem de boas-vindas para parceiro
     */
    public function sendParceiroBoasVindas($parceiro)
    {
        try {
            // Buscar template de boas-vindas para parceiros
            $template = \App\Models\WhatsAppTemplate::where('name', 'parceiro_boas_vindas')
                ->where('active', true)
                ->first();

            if ($template) {
                // Preparar dados para o template
                $data = [
                    'nome' => $parceiro->nome_completo,
                    'nome_fantasia' => $parceiro->nome_fantasia ?: $parceiro->nome_completo,
                    'telefone' => $parceiro->telefone,
                    'email' => $parceiro->email
                ];

                // Validar se todas as variáveis necessárias estão presentes
                if (!$template->validateVariables($data)) {
                    throw new \Exception('Dados insuficientes para o template de boas-vindas para parceiro');
                }

                // Substituir variáveis e enviar mensagem
                $message = $template->replaceVariables($data);
            } else {
                // Fallback para mensagem padrão se não houver template
                $message = "🎉 *BEM-VINDO(A) À NOSSA REDE DE PARCEIROS!*\n\n";
                $message .= "Olá *{$parceiro->nome_completo}*!\n\n";
                $message .= "Parabéns! Seu cadastro como parceiro foi realizado com sucesso! ✅\n\n";
                $message .= "🏢 *Dados da Parceria:*\n";
                $message .= "📌 *Nome/Empresa:* " . ($parceiro->nome_fantasia ?: $parceiro->nome_completo) . "\n";
                $message .= "📞 *Telefone:* {$parceiro->telefone}\n";
                $message .= "📧 *Email:* {$parceiro->email}\n\n";
                $message .= "🚀 Nossa equipe de parcerias entrará em contato em breve!\n\n";
                $message .= "_Atenciosamente,_\n*Equipe de Parcerias - EJA Supletivo*";
            }

            // Usar WhatsApp se disponível, senão usar telefone
            $phone = $parceiro->whatsapp ?: $parceiro->telefone;
            
            return $this->sendMessage($phone, $message);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar boas-vindas WhatsApp para parceiro ' . $parceiro->id . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar mensagem usando um template específico
     */
    public function sendTemplateMessage($phone, $templateName, array $data)
    {
        try {
            // Buscar template
            $template = \App\Models\WhatsAppTemplate::where('name', $templateName)
                ->where('active', true)
                ->first();

            if (!$template) {
                throw new \Exception("Template '{$templateName}' não encontrado ou inativo");
            }

            // Validar se todas as variáveis necessárias estão presentes
            if (!$template->validateVariables($data)) {
                throw new \Exception("Dados insuficientes para o template '{$templateName}'");
            }

            // Substituir variáveis e enviar mensagem
            $message = $template->replaceVariables($data);
            return $this->sendMessage($phone, $message);
        } catch (\Exception $e) {
            Log::error("Erro ao enviar mensagem usando template '{$templateName}': " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar notificação de contrato via WhatsApp
     */
    public function sendContractNotification($contract)
    {
        try {
            // Buscar template de contrato
            $template = \App\Models\WhatsAppTemplate::where('name', 'contrato_enviado')
                ->where('active', true)
                ->first();

            if ($template) {
                // Preparar dados para o template
                $data = [
                    'nome' => $contract->matricula->nome_completo,
                    'numero_contrato' => $contract->contract_number,
                    'titulo_contrato' => $contract->title,
                    'link_acesso' => $contract->getAccessLink(),
                    'data_expiracao' => $contract->access_expires_at->format('d/m/Y'),
                ];

                // Validar se todas as variáveis necessárias estão presentes
                if (!$template->validateVariables($data)) {
                    throw new \Exception('Dados insuficientes para o template de contrato');
                }

                // Substituir variáveis e enviar mensagem
                $message = $template->replaceVariables($data);
            } else {
                // Fallback para mensagem padrão se não houver template
                $message = "📄 *CONTRATO DIGITAL DISPONÍVEL*\n\n";
                $message .= "Olá *{$contract->matricula->nome_completo}*!\n\n";
                $message .= "Seu contrato digital está pronto para assinatura! ✅\n\n";
                $message .= "📋 *Detalhes do Contrato:*\n";
                $message .= "📌 *Número:* {$contract->contract_number}\n";
                $message .= "📝 *Título:* {$contract->title}\n";
                $message .= "⏰ *Válido até:* " . $contract->access_expires_at->format('d/m/Y') . "\n\n";
                $message .= "🔗 *Clique no link abaixo para acessar e assinar:*\n";
                $message .= $contract->getAccessLink() . "\n\n";
                $message .= "⚠️ *Importante:* Este link é válido até " . $contract->access_expires_at->format('d/m/Y') . ". Não deixe para depois!\n\n";
                $message .= "_Em caso de dúvidas, entre em contato conosco._\n\n";
                $message .= "*Atenciosamente,*\n*Equipe EJA Supletivo*";
            }

            // Usar WhatsApp se disponível, senão usar telefone
            $phone = $contract->matricula->telefone_celular ?: $contract->matricula->telefone_fixo;
            
            return $this->sendMessage($phone, $message);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de contrato WhatsApp ' . $contract->id . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar mensagem genérica
     */
    public function sendMessage($phone, $message)
    {
        if (!$this->hasValidSettings()) {
            throw new \Exception('Configurações da API não estão completas');
        }

        try {
            // Verificação simples se está conectado
            if (!$this->keepAlive()) {
                throw new \Exception('WhatsApp não está conectado. Acesse /admin/whatsapp para verificar a conexão.');
            }

            $formattedPhone = $this->formatPhone($phone);
            
            // Log para debug
            Log::info('Tentando enviar mensagem WhatsApp', [
                'phone' => $formattedPhone,
                'message_length' => strlen($message),
                'message_preview' => substr($message, 0, 50) . '...'
            ]);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'apikey' => $this->apiKey,
                    'Content-Type' => 'application/json'
                ])
                ->post("{$this->baseUrl}/message/sendText/{$this->instance}", [
                    'number' => $formattedPhone,
                    'options' => [
                        'delay' => 1200,
                        'presence' => 'composing'
                    ],
                    'text' => $message
                ]);

            if (!$response->successful()) {
                Log::error('Erro na resposta ao enviar mensagem WhatsApp', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'phone' => $formattedPhone
                ]);
                throw new \Exception('Erro ao enviar mensagem: ' . $response->body());
            }

            $data = $response->json();
            Log::info('Mensagem WhatsApp enviada com sucesso', [
                'phone' => $formattedPhone,
                'response' => $data
            ]);
            
            return [
                'success' => true,
                'message_id' => $data['key']['id'] ?? null,
                'response' => $data
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem WhatsApp: ' . $e->getMessage(), [
                'phone' => $phone,
                'message' => substr($message, 0, 100) . '...'
            ]);
            throw $e;
        }
    }

    /**
     * Enviar mensagem com imagem (para QR Code)
     */
    public function sendImageMessage($phone, $imageUrl, $caption = '')
    {
        if (!$this->hasValidSettings()) {
            throw new \Exception('Configurações da API não estão completas');
        }

        try {
            // Verificação simples se está conectado
            if (!$this->keepAlive()) {
                throw new \Exception('WhatsApp não está conectado. Acesse /admin/whatsapp para verificar a conexão.');
            }

            $formattedPhone = $this->formatPhone($phone);
            
            // Verificar se a URL é uma imagem base64
            $isBase64 = strpos($imageUrl, 'data:image') === 0;
            
            if ($isBase64) {
                // Para base64, usar o endpoint correto
                $response = Http::timeout(30)
                    ->withHeaders([
                        'apikey' => $this->apiKey,
                        'Content-Type' => 'application/json'
                    ])
                    ->post("{$this->baseUrl}/message/sendText/{$this->instance}", [
                        'number' => $formattedPhone,
                        'options' => [
                            'delay' => 1200,
                            'presence' => 'composing'
                        ],
                        'text' => "Não foi possível enviar o QR Code como imagem. Por favor, entre em contato com o suporte."
                    ]);
                
                // Log para debug
                Log::warning('Tentativa de enviar imagem base64 convertida para texto', [
                    'phone' => $formattedPhone,
                    'is_base64' => true,
                    'base64_length' => strlen($imageUrl)
                ]);
            } else {
                // Para URLs normais, usar o endpoint para envio de imagem por URL
                $response = Http::timeout(30)
                    ->withHeaders([
                        'apikey' => $this->apiKey,
                        'Content-Type' => 'application/json'
                    ])
                    ->post("{$this->baseUrl}/message/sendText/{$this->instance}", [
                        'number' => $formattedPhone,
                        'options' => [
                            'delay' => 1200,
                            'presence' => 'composing'
                        ],
                        'text' => $caption . "\n\n" . $imageUrl
                    ]);
                
                // Log para debug
                Log::warning('Tentativa de enviar URL da imagem como texto', [
                    'phone' => $formattedPhone,
                    'is_base64' => false,
                    'url' => $imageUrl
                ]);
            }

            if (!$response->successful()) {
                Log::error('Falha ao enviar imagem: ' . $response->body(), [
                    'status' => $response->status()
                ]);
                throw new \Exception('Erro ao enviar imagem: ' . $response->body());
            }

            $data = $response->json();
            Log::info('Mensagem alternativa enviada com sucesso (sem imagem)', [
                'phone' => $formattedPhone,
                'response' => $data
            ]);
            
            return [
                'success' => true,
                'message_id' => $data['key']['id'] ?? null,
                'response' => $data,
                'warning' => 'Imagem não pôde ser enviada. Enviada como texto ou mensagem alternativa.'
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao enviar imagem WhatsApp: ' . $e->getMessage(), [
                'phone' => $phone,
                'image_url' => substr($imageUrl, 0, 100) . '...' // Limitar o tamanho do log
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar QR Code PIX com mensagem
     */
    public function sendPixQrCode($phone, $qrCodeContent, $message)
    {
        if (!$this->hasValidSettings()) {
            throw new \Exception('Configurações da API não estão completas');
        }

        try {
            // Verificação simples se está conectado
            if (!$this->keepAlive()) {
                throw new \Exception('WhatsApp não está conectado. Acesse /admin/whatsapp para verificar a conexão.');
            }

            // Primeiro, enviar a mensagem de texto com as instruções
            $textResult = $this->sendMessage($phone, $message);
            
            // Verificar se o conteúdo é uma imagem base64 ou um texto de código PIX
            $isBase64 = strpos($qrCodeContent, 'data:image') === 0;
            
            if (!$isBase64) {
                // Formatar o código PIX para fácil cópia
                $pixCodeMessage = "```\n" . $qrCodeContent . "\n```\n\n👆 *Toque e segure o código acima para copiar*";
                $pixCodeResult = $this->sendMessage($phone, $pixCodeMessage);
                
                // Validar o conteúdo do código PIX
                if (empty($qrCodeContent) || strlen($qrCodeContent) < 10) {
                    Log::error('Código PIX inválido ou muito curto', [
                        'phone' => $phone,
                        'qr_content_length' => strlen($qrCodeContent)
                    ]);
                    throw new \Exception('Código PIX inválido ou não encontrado');
                }
                
                // Gerar QR Code usando QuickChart API (mais confiável para códigos longos)
                $qrCodeUrl = "https://quickchart.io/qr?text=" . urlencode($qrCodeContent);
                
                // Log para debug
                Log::info('Gerando QR code para PIX usando QuickChart', [
                    'phone' => $phone,
                    'qr_url' => $qrCodeUrl,
                    'qr_content_length' => strlen($qrCodeContent)
                ]);
                
                // Enviar a imagem do QR code usando o endpoint sendMedia
                $formattedPhone = $this->formatPhone($phone);
                
                $response = Http::timeout(30)
                    ->withHeaders([
                        'apikey' => $this->apiKey,
                        'Content-Type' => 'application/json'
                    ])
                    ->post("{$this->baseUrl}/message/sendMedia/{$this->instance}", [
                        'number' => $formattedPhone,
                        'media' => $qrCodeUrl,
                        'mediatype' => 'image',
                        'caption' => '*QR Code PIX para pagamento*'
                    ]);
                
                if (!$response->successful()) {
                    Log::error('Erro na resposta ao enviar QR code como imagem', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'phone' => $formattedPhone
                    ]);
                    
                    // Se falhar, enviar apenas uma mensagem com o link para o QR code
                    $qrLinkMessage = "*QR Code PIX:*\n\nEscaneie o QR code através deste link:\n" . $qrCodeUrl;
                    $qrImageResult = $this->sendMessage($phone, $qrLinkMessage);
                    
                    Log::info('Enviado link para QR code como alternativa', [
                        'phone' => $phone,
                        'url' => $qrCodeUrl
                    ]);
                } else {
                    $qrImageResult = $response->json();
                    Log::info('QR Code PIX enviado como imagem com sucesso', [
                        'phone' => $phone,
                        'url' => $qrCodeUrl
                    ]);
                }
            } else {
                // Se for base64, extrair o conteúdo e tentar gerar um QR code
                Log::info('Recebido QR code base64, enviando apenas o código PIX');
                
                // Enviar mensagem explicando que o código PIX deve ser copiado
                $pixCodeMessage = "*Código PIX para pagamento:*\n\nPor favor, utilize o código PIX enviado acima para realizar o pagamento.";
                $pixCodeResult = $this->sendMessage($phone, $pixCodeMessage);
                
                // Não temos um QR link neste caso
                $qrImageResult = ['success' => true];
            }
            
            return [
                'success' => ($textResult['success'] ?? false) && ($pixCodeResult['success'] ?? false),
                'text_message_id' => $textResult['message_id'] ?? null,
                'pix_code_message_id' => $pixCodeResult['message_id'] ?? null,
                'qr_image_message_id' => $qrImageResult['key']['id'] ?? null
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao enviar QR Code PIX: ' . $e->getMessage(), [
                'phone' => $phone
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Formatar número de telefone para padrão internacional
     */
    protected function formatPhone($phone)
    {
        // Remove tudo que não for número
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove códigos de país duplicados
        if (str_starts_with($phone, '5555')) {
            $phone = substr($phone, 2);
        }
        
        // Adiciona código do país se necessário
        if (strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }
        
        // Adiciona 9 no celular se necessário (formato novo do Brasil)
        if (strlen($phone) == 12 && !str_starts_with(substr($phone, 4), '9')) {
            $phone = substr($phone, 0, 4) . '9' . substr($phone, 4);
        }
        
        return $phone;
    }

    /**
     * Desconectar instância
     */
    public function disconnect()
    {
        if (!$this->hasValidSettings()) {
            throw new \Exception('Configurações da API não estão completas');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'apikey' => $this->apiKey
                ])
                ->delete("{$this->baseUrl}/instance/logout/{$this->instance}");

            $this->updateConnectionStatus(false);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Erro ao desconectar WhatsApp: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Deletar instância
     */
    public function deleteInstance()
    {
        if (!$this->hasValidSettings()) {
            throw new \Exception('Configurações da API não estão completas');
        }

        try {
            Log::info('Tentando deletar instância WhatsApp', [
                'base_url' => $this->baseUrl,
                'instance' => $this->instance,
                'full_url' => "{$this->baseUrl}/instance/delete/{$this->instance}"
            ]);

            $response = Http::timeout(60) // Aumentar timeout para 60 segundos
                ->connectTimeout(15) // Timeout de conexão
                ->retry(3, 2000) // Tentar 3 vezes com 2 segundos entre tentativas
                ->withHeaders([
                    'apikey' => $this->apiKey,
                    'Content-Type' => 'application/json'
                ])
                ->delete("{$this->baseUrl}/instance/delete/{$this->instance}");

            if (!$response->successful()) {
                $errorMsg = "Erro HTTP {$response->status()}: " . $response->body();
                Log::error('Erro na resposta ao deletar instância', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception($errorMsg);
            }

            $this->updateConnectionStatus(false);
            
            Log::info('Instância WhatsApp deletada com sucesso');
            return $response->json();
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $errorMsg = 'Erro de conexão: Não foi possível conectar ao servidor da Evolution API. Verifique se o servidor está online.';
            Log::error('Erro de conexão ao deletar instância WhatsApp', [
                'error' => $e->getMessage(),
                'base_url' => $this->baseUrl
            ]);
            throw new \Exception($errorMsg);
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $errorMsg = 'Timeout: A operação demorou mais que o esperado. Tente novamente em alguns minutos.';
            Log::error('Timeout ao deletar instância WhatsApp', [
                'error' => $e->getMessage(),
                'instance' => $this->instance
            ]);
            throw new \Exception($errorMsg);
            
        } catch (\Exception $e) {
            Log::error('Erro geral ao deletar instância WhatsApp', [
                'error' => $e->getMessage(),
                'instance' => $this->instance,
                'base_url' => $this->baseUrl
            ]);
            throw $e;
        }
    }

    /**
     * Reconectar instância (útil quando QR Code expira)
     */
    public function reconnectInstance()
    {
        if (!$this->hasValidSettings()) {
            throw new \Exception('Configurações da API não estão completas');
        }

        try {
            // Primeiro tentar desconectar
            $this->disconnect();
            
            // Aguardar um pouco
            sleep(2);
            
            // Tentar obter novo QR Code
            return $this->getQrCode();
        } catch (\Exception $e) {
            Log::error('Erro ao reconectar instância: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verificar se a instância existe
     */
    public function instanceExists()
    {
        if (!$this->hasValidSettings()) {
            return false;
        }

        try {
            // Usar timeout maior em produção se detectar problema de conectividade
            $timeout = app()->environment('production') ? 30 : 10;
            $connectTimeout = app()->environment('production') ? 15 : 5;
            
            $response = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->withHeaders([
                    'apikey' => $this->apiKey
                ])
                ->get("{$this->baseUrl}/instance/fetchInstances");

            if (!$response->successful()) {
                Log::error('Erro ao verificar instâncias: ' . $response->body());
                return false;
            }

            $data = $response->json();
            
            // A Evolution API retorna diretamente um array de instâncias
            if (is_array($data)) {
                foreach ($data as $instance) {
                    // Verificar pelo campo 'name' que é o que a Evolution API usa
                    if (isset($instance['name']) && $instance['name'] === $this->instance) {
                        Log::info('Instância encontrada', [
                            'instance' => $this->instance,
                            'status' => $instance['connectionStatus'] ?? 'unknown',
                            'id' => $instance['id'] ?? 'unknown'
                        ]);
                        return true;
                    }
                }
            }

            Log::info('Instância não encontrada', ['instance' => $this->instance]);
            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar se instância existe: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se a instância foi deslogada e tentar reconectar automaticamente
     */
    protected function handleDisconnectedInstance()
    {
        if (!$this->hasValidSettings()) {
            return false;
        }

        try {
            Log::info('Instância desconectada detectada - aguardando inicialização natural...');
            
            // Apenas fazer logout simples sem deletar a instância
            try {
                $logoutResponse = Http::timeout(30)
                    ->withHeaders(['apikey' => $this->apiKey])
                    ->delete("{$this->baseUrl}/instance/logout/{$this->instance}");
                
                Log::info('Logout forçado realizado', [
                    'status' => $logoutResponse->status(),
                    'success' => $logoutResponse->successful()
                ]);
            } catch (\Exception $e) {
                Log::info('Logout forçado falhou (normal se instância não existir): ' . $e->getMessage());
            }
            
            // Aguardar um pouco para limpeza
            sleep(2);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao fazer logout: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Diagnóstico de conectividade
     */
    public function diagnosticConnectivity()
    {
        if (!$this->hasValidSettings()) {
            return [
                'success' => false,
                'error' => 'Configurações da API não estão completas',
                'tests' => []
            ];
        }

        $tests = [];
        $overallSuccess = true;

        // Teste 1: Conectividade básica
        try {
            $start = microtime(true);
            $response = Http::timeout(10)->get($this->baseUrl . '/manager/fetchInstances');
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            $tests['basic_connectivity'] = [
                'name' => 'Conectividade Básica',
                'success' => $response->successful(),
                'duration_ms' => $duration,
                'status_code' => $response->status(),
                'message' => $response->successful() ? 'OK' : 'Falha na conectividade'
            ];
            
            if (!$response->successful()) {
                $overallSuccess = false;
            }
        } catch (\Exception $e) {
            $tests['basic_connectivity'] = [
                'name' => 'Conectividade Básica',
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Erro de conexão'
            ];
            $overallSuccess = false;
        }

        // Teste 2: Autenticação da API
        try {
            $start = microtime(true);
            $response = Http::timeout(10)
                ->withHeaders(['apikey' => $this->apiKey])
                ->get($this->baseUrl . '/instance/fetchInstances');
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            $tests['api_auth'] = [
                'name' => 'Autenticação da API',
                'success' => $response->successful(),
                'duration_ms' => $duration,
                'status_code' => $response->status(),
                'message' => $response->successful() ? 'API Key válida' : 'API Key inválida ou expirada'
            ];
            
            if (!$response->successful()) {
                $overallSuccess = false;
            }
        } catch (\Exception $e) {
            $tests['api_auth'] = [
                'name' => 'Autenticação da API',
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Erro na autenticação'
            ];
            $overallSuccess = false;
        }

        // Teste 3: Teste específico do endpoint create (GET para verificar se existe)
        try {
            $start = microtime(true);
            $response = Http::timeout(10)
                ->withHeaders(['apikey' => $this->apiKey])
                ->get($this->baseUrl . '/instance/create');
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            // 404 é esperado para GET no endpoint create (só aceita POST)
            $success = $response->status() === 404 && str_contains($response->body(), 'Cannot GET');
            
            $tests['create_endpoint'] = [
                'name' => 'Endpoint Create Instance',
                'success' => $success,
                'duration_ms' => $duration,
                'status_code' => $response->status(),
                'message' => $success ? 'Endpoint disponível (aguardando POST)' : 'Endpoint com problema'
            ];
            
            if (!$success) {
                $overallSuccess = false;
            }
        } catch (\Exception $e) {
            $tests['create_endpoint'] = [
                'name' => 'Endpoint Create Instance',
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Erro ao acessar endpoint'
            ];
            $overallSuccess = false;
        }

        // Teste 4: Verificar se instância já existe para evitar conflitos
        try {
            $start = microtime(true);
            $response = Http::timeout(10)
                ->withHeaders(['apikey' => $this->apiKey])
                ->get($this->baseUrl . '/instance/fetchInstances');
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            $instanceExists = false;
            if ($response->successful()) {
                $instances = $response->json();
                foreach ($instances as $inst) {
                    if ($inst['name'] === $this->instance) {
                        $instanceExists = true;
                        break;
                    }
                }
            }
            
            $tests['instance_check'] = [
                'name' => 'Verificação de Instância',
                'success' => $response->successful(),
                'duration_ms' => $duration,
                'status_code' => $response->status(),
                'message' => $instanceExists ? 
                    "Instância '{$this->instance}' já existe" : 
                    "Instância '{$this->instance}' não existe - pode ser criada",
                'instance_exists' => $instanceExists
            ];
        } catch (\Exception $e) {
            $tests['instance_check'] = [
                'name' => 'Verificação de Instância',
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Erro ao verificar instâncias'
            ];
        }

        return [
            'success' => $overallSuccess,
            'timestamp' => now()->toISOString(),
            'config' => [
                'base_url' => $this->baseUrl,
                'instance' => $this->instance,
                'has_api_key' => !empty($this->apiKey)
            ],
            'tests' => $tests
        ];
    }

    /**
     * Verificar e corrigir automaticamente conexões caídas
     */
    public function checkAndReconnectIfNeeded()
    {
        if (!$this->hasValidSettings()) {
            return false;
        }

        try {
            // Verificar se instância existe
            if (!$this->instanceExists()) {
                Log::info('Instância não existe, precisa ser recriada');
                return false;
            }

            // Verificar status na Evolution API
            $response = Http::timeout(30)
                ->withHeaders(['apikey' => $this->apiKey])
                ->get("{$this->baseUrl}/instance/fetchInstances");

            if (!$response->successful()) {
                Log::error('Erro ao verificar instâncias na Evolution API');
                return false;
            }

            $instances = $response->json();
            $instance = null;
            
            // Encontrar nossa instância
            foreach ($instances as $inst) {
                if ($inst['name'] === $this->instance) {
                    $instance = $inst;
                    break;
                }
            }

            if (!$instance) {
                Log::info('Instância não encontrada na lista da Evolution API');
                return false;
            }

            // Verificar códigos de desconexão
            $disconnectionCode = $instance['disconnectionReasonCode'] ?? null;
            $state = $instance['state'] ?? null;
            
            Log::info('Status da instância na Evolution API', [
                'state' => $state,
                'disconnectionCode' => $disconnectionCode,
                'status' => $instance['status'] ?? null
            ]);

            // Se foi deslogada (código 401) ou está em estado problemático
            if ($disconnectionCode == 401 || $state === 'close' || $state === null) {
                Log::info('Conexão caiu, tentando reconectar automaticamente...');
                
                // Atualizar status local
                $this->updateConnectionStatus(false);
                
                // Tentar reconectar
                return $this->autoReconnect();
            }

            // Se está conectada, atualizar status local
            if ($state === 'open') {
                $this->updateConnectionStatus(true);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar conexão: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reconexão automática
     */
    public function autoReconnect()
    {
        try {
            Log::info('Iniciando reconexão automática...');
            
            // 1. Fazer logout forçado
            try {
                Http::timeout(30)
                    ->withHeaders(['apikey' => $this->apiKey])
                    ->delete("{$this->baseUrl}/instance/logout/{$this->instance}");
                Log::info('Logout forçado realizado');
            } catch (\Exception $e) {
                Log::info('Logout forçado falhou (esperado): ' . $e->getMessage());
            }

            // 2. Aguardar limpeza
            sleep(2);

            // 3. Verificar se instância ainda existe
            if (!$this->instanceExists()) {
                Log::info('Instância não existe mais, criando nova...');
                $createResult = $this->createInstance();
                if (!isset($createResult['instance'])) {
                    throw new \Exception('Falha ao criar nova instância');
                }
                sleep(2);
            }

            // 4. Tentar obter novo QR Code
            Log::info('Tentando gerar novo QR Code para reconexão...');
            $qrResult = $this->getQrCode();
            
            if (isset($qrResult['qrcode']['base64'])) {
                Log::info('QR Code gerado com sucesso para reconexão');
                
                // Salvar timestamp da última tentativa de reconexão
                SystemSetting::set('evolution_api_last_reconnect', now()->toISOString(), 'string', 'evolution_api', 'Última tentativa de reconexão');
                
                return [
                    'success' => true,
                    'message' => 'QR Code gerado para reconexão. Escaneie para reconectar.',
                    'qrcode' => $qrResult['qrcode']['base64']
                ];
            } else if (isset($qrResult['connected']) && $qrResult['connected']) {
                Log::info('Reconexão automática bem-sucedida!');
                $this->updateConnectionStatus(true);
                return [
                    'success' => true,
                    'message' => 'Reconectado automaticamente!',
                    'connected' => true
                ];
            }

            return [
                'success' => false,
                'message' => 'Não foi possível gerar QR Code para reconexão'
            ];

        } catch (\Exception $e) {
            Log::error('Erro na reconexão automática: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro na reconexão: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar se precisa de monitoramento (última verificação há mais de X minutos)
     */
    public function needsMonitoring($intervalMinutes = 5)
    {
        $lastCheck = SystemSetting::get('evolution_api_last_check');
        
        if (!$lastCheck) {
            return true;
        }

        $lastCheckTime = \Carbon\Carbon::parse($lastCheck);
        return $lastCheckTime->diffInMinutes(now()) >= $intervalMinutes;
    }

    /**
     * Monitoramento automático da conexão
     */
    public function monitorConnection()
    {
        try {
            // Marcar timestamp da verificação
            SystemSetting::set('evolution_api_last_check', now()->toISOString(), 'string', 'evolution_api', 'Última verificação');
            
            // Verificar e reconectar se necessário
            $result = $this->checkAndReconnectIfNeeded();
            
            Log::info('Monitoramento de conexão realizado', [
                'success' => $result,
                'timestamp' => now()->toISOString()
            ]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Erro no monitoramento: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Keep-alive simples: verifica se está conectado e registra atividade
     */
    public function keepAlive()
    {
        if (!$this->hasValidSettings()) {
            return false;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['apikey' => $this->apiKey])
                ->get("{$this->baseUrl}/instance/connectionState/{$this->instance}");

            if ($response->successful()) {
                $data = $response->json();
                $connected = isset($data['instance']['state']) && $data['instance']['state'] === 'open';
                
                // Atualizar status local
                $this->updateConnectionStatus($connected);
                
                // Log simples
                Log::info('WhatsApp keep-alive', [
                    'connected' => $connected,
                    'state' => $data['instance']['state'] ?? 'unknown'
                ]);
                
                return $connected;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Keep-alive WhatsApp falhou: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificação simples se precisa reconectar
     */
    public function needsReconnection()
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['apikey' => $this->apiKey])
                ->get("{$this->baseUrl}/instance/fetchInstances");

            if (!$response->successful()) {
                return true;
            }

            $instances = $response->json();
            foreach ($instances as $inst) {
                if ($inst['name'] === $this->instance) {
                    $disconnectionCode = $inst['disconnectionReasonCode'] ?? null;
                    $state = $inst['state'] ?? null;
                    
                    // Se foi deslogada ou está fechada
                    if ($disconnectionCode == 401 || $state === 'close' || $state === null) {
                        Log::info('WhatsApp precisa reconectar', [
                            'state' => $state,
                            'disconnectionCode' => $disconnectionCode
                        ]);
                        return true;
                    }
                    
                    return false;
                }
            }
            
            return true; // Instância não encontrada
        } catch (\Exception $e) {
            Log::error('Erro ao verificar se precisa reconectar: ' . $e->getMessage());
            return true;
        }
    }
} 