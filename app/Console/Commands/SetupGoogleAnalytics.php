<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SetupGoogleAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:setup {--client-id=} {--client-secret=} {--property-id=} {--measurement-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configurar Google Analytics e Google Ads para o sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Configurando Google Analytics e Google Ads...');
        
        // Verificar se o arquivo .env existe
        if (!File::exists(base_path('.env'))) {
            $this->error('❌ Arquivo .env não encontrado!');
            return 1;
        }

        // Ler configurações atuais
        $envContent = File::get(base_path('.env'));
        
        // Solicitar informações se não fornecidas
        $clientId = $this->option('client-id') ?: $this->ask('Digite o Client ID do Google Analytics:');
        $clientSecret = $this->option('client-secret') ?: $this->secret('Digite o Client Secret do Google Analytics:');
        $propertyId = $this->option('property-id') ?: $this->ask('Digite o Property ID do Google Analytics (formato: 123456789):');
        $measurementId = $this->option('measurement-id') ?: $this->ask('Digite o Measurement ID (formato: G-XXXXXXXXXX):');

        if (empty($clientId) || empty($clientSecret) || empty($propertyId) || empty($measurementId)) {
            $this->error('❌ Todas as informações são obrigatórias!');
            return 1;
        }

        // Atualizar arquivo .env
        $this->updateEnvFile($envContent, [
            'GOOGLE_ANALYTICS_CLIENT_ID' => $clientId,
            'GOOGLE_ANALYTICS_CLIENT_SECRET' => $clientSecret,
            'GOOGLE_ANALYTICS_PROPERTY_ID' => $propertyId,
            'GOOGLE_ANALYTICS_MEASUREMENT_ID' => $measurementId,
            'GOOGLE_ANALYTICS_VIEW_ID' => $propertyId, // Usar property ID como view ID por padrão
        ]);

        $this->info('✅ Configurações básicas salvas no .env');
        
        // Instruções para obter o token de acesso
        $this->showNextSteps();
        
        return 0;
    }

    /**
     * Atualizar arquivo .env
     */
    private function updateEnvFile(string $content, array $variables): void
    {
        foreach ($variables as $key => $value) {
            if (strpos($content, $key . '=') !== false) {
                // Atualizar variável existente
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $content
                );
            } else {
                // Adicionar nova variável
                $content .= "\n{$key}={$value}";
            }
        }

        File::put(base_path('.env'), $content);
    }

    /**
     * Mostrar próximos passos
     */
    private function showNextSteps(): void
    {
        $this->info("\n📋 PRÓXIMOS PASSOS:");
        $this->info("1. Acesse: https://console.developers.google.com/");
        $this->info("2. Crie um projeto ou selecione um existente");
        $this->info("3. Ative as APIs:");
        $this->info("   - Google Analytics Data API (GA4)");
        $this->info("   - Google Ads API");
        $this->info("4. Configure as credenciais OAuth 2.0");
        $this->info("5. Execute: php artisan analytics:auth");
        $this->info("\n💡 Dica: Use o comando 'php artisan analytics:auth' para autenticar e obter o token de acesso");
    }
}
