<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $service = app(\App\Services\GoogleDriveService::class);

    echo "=== Teste de Conexão ===\n";
    $result = $service->testConnection();
    print_r($result);
    echo "\n";

    if ($result['status'] === 'success') {
        echo "=== Verificação da Pasta Raiz ===\n";
        $folderResult = $service->ensureRootFolder();
        print_r($folderResult);
        echo "\n";

        if ($folderResult['status'] === 'created') {
            echo "IMPORTANTE: Uma nova pasta raiz foi criada. Atualize seu .env com o novo ID: " . $folderResult['folder_id'] . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Erro ao testar: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
