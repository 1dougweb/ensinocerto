<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class GoogleAnalyticsAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Autenticar com Google Analytics via OAuth 2.0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Autenticando com Google Analytics...');
        
        // Verificar configurações
        $clientId = config('services.google_analytics.client_id');
        $clientSecret = config('services.google_analytics.client_secret');
        
        if (empty($clientId) || empty($clientSecret)) {
            $this->error('❌ Client ID ou Client Secret não configurados!');
            $this->info('Execute primeiro: php artisan analytics:setup');
            return 1;
        }

        // URL de autorização
        $authUrl = $this->buildAuthUrl($clientId);
        
        $this->info("\n🌐 Abra o seguinte link no seu navegador:");
        $this->line($authUrl);
        $this->info("\n📋 Após autorizar, você receberá um código de autorização.");
        
        $authCode = $this->ask('Cole o código de autorização aqui:');
        
        if (empty($authCode)) {
            $this->error('❌ Código de autorização é obrigatório!');
            return 1;
        }

        // Trocar código por token
        $tokens = $this->exchangeCodeForTokens($clientId, $clientSecret, $authCode);
        
        if ($tokens) {
            $this->saveTokens($tokens);
            $this->info('✅ Autenticação realizada com sucesso!');
            $this->info('🎯 Agora você pode usar o dashboard de analytics com dados reais.');
        } else {
            $this->error('❌ Falha na autenticação!');
            return 1;
        }

        return 0;
    }

    /**
     * Construir URL de autorização
     */
    private function buildAuthUrl(string $clientId): string
    {
        $scopes = [
            'https://www.googleapis.com/auth/analytics.readonly',
            'https://www.googleapis.com/auth/adwords',
            'https://www.googleapis.com/auth/analytics.edit'
        ];

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
            'scope' => implode(' ', $scopes),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];

        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    }

    /**
     * Trocar código por tokens
     */
    private function exchangeCodeForTokens(string $clientId, string $clientSecret, string $authCode): ?array
    {
        try {
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $authCode,
                'grant_type' => 'authorization_code',
                'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob'
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                $this->error('Erro na resposta da API: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            $this->error('Erro na requisição: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Salvar tokens no arquivo .env
     */
    private function saveTokens(array $tokens): void
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        $variables = [
            'GOOGLE_ANALYTICS_ACCESS_TOKEN' => $tokens['access_token'] ?? '',
            'GOOGLE_ANALYTICS_REFRESH_TOKEN' => $tokens['refresh_token'] ?? '',
        ];

        foreach ($variables as $key => $value) {
            if (strpos($envContent, $key . '=') !== false) {
                // Atualizar variável existente
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Adicionar nova variável
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
        
        $this->info('💾 Tokens salvos no arquivo .env');
        
        if (isset($tokens['expires_in'])) {
            $expiresIn = $tokens['expires_in'];
            $this->info("⏰ Token expira em {$expiresIn} segundos");
            $this->info("🔄 Use o refresh token para renovar automaticamente");
        }
    }
}
