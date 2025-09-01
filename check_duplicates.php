<?php

require_once 'vendor/autoload.php';

// Configurar ambiente
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\GoogleDriveFile;

// Verificar registros duplicados
$fileId = '1yeMvtnXzKiF2b-6C0D6t8aG6jYIgNpFI';
$count = GoogleDriveFile::where('file_id', $fileId)->count();

echo "Registros com file_id {$fileId}: {$count}\n";

// Verificar se há soft deletes
$softDeletedCount = GoogleDriveFile::withTrashed()->where('file_id', $fileId)->count();
echo "Registros incluindo soft deleted: {$softDeletedCount}\n";

// Listar todos os registros duplicados
if ($count > 0) {
    $duplicates = GoogleDriveFile::where('file_id', $fileId)->get();
    foreach ($duplicates as $duplicate) {
        echo "ID: {$duplicate->id}, Created: {$duplicate->created_at}, Name: {$duplicate->name}\n";
    }
}
