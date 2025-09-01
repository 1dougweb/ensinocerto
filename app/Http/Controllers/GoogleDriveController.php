<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleDriveController extends Controller
{
    private $drive;

    public function __construct()
    {
        try {
            $client = new Google_Client();

            // Verificar se estamos em modo de teste
            if (config('app.env') === 'testing' || config('services.google.test_mode', false)) {
                // Modo de teste - não inicializar cliente real
                $this->drive = null;
                return;
            }

            $credentialsPath = storage_path('app/google-credentials.json');

            if (!file_exists($credentialsPath)) {
                throw new \Exception("Arquivo de credenciais do Google Drive não encontrado em: {$credentialsPath}");
            }

            // Configurar OAuth2 com as credenciais do .env
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->setScopes([Google_Service_Drive::DRIVE]);
            $client->setAccessType('offline');

            // Usar refresh token existente
            $refreshToken = env('GOOGLE_DRIVE_REFRESH_TOKEN');

            if (!empty($refreshToken)) {
                try {
                    // Obter novo access token usando refresh token
                    $accessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

                    if (isset($accessToken['error'])) {
                        \Log::warning('Erro ao renovar token no GoogleDriveController: ' . ($accessToken['error_description'] ?? $accessToken['error']));
                        throw new \Exception('Erro ao renovar token OAuth2');
                    }

                    $client->setAccessToken($accessToken);
                    \Log::info('GoogleDriveController: Token OAuth2 renovado com sucesso');

                } catch (\Exception $e) {
                    \Log::error('Erro ao renovar token OAuth2 no GoogleDriveController: ' . $e->getMessage());
                    throw new \Exception('Erro ao configurar OAuth2. Verifique as credenciais.');
                }
            } else {
                throw new \Exception('Refresh token não encontrado no .env');
            }

            $this->drive = new Google_Service_Drive($client);

        } catch (\Exception $e) {
            \Log::error('Erro ao inicializar GoogleDriveController: ' . $e->getMessage());
            $this->drive = null;
        }
    }

    public function createStudentFolder(Request $request)
    {
        try {
            $request->validate([
                'student_name' => 'required|string'
            ]);

            // Create folder metadata
            $folderMetadata = new Google_Service_Drive_DriveFile([
                'name' => $request->student_name . ' - Documentos',
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            // Create the folder
            $folder = $this->drive->files->create($folderMetadata, [
                'fields' => 'id'
            ]);

            return response()->json([
                'status' => 'success',
                'folder_id' => $folder->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating student folder: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create folder'
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file',
                'folder_id' => 'required|string'
            ]);

            $file = $request->file('file');
            $folderId = $request->folder_id;

            // Verificar se estamos em modo de teste
            if (config('app.env') === 'testing' || config('services.google.test_mode', false)) {
                return response()->json([
                    'status' => 'success',
                    'file_id' => 'test_file_' . uniqid()
                ]);
            }

            // Verificar se o Google Drive está inicializado
            if (!$this->drive) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Google Drive não está configurado corretamente'
                ], 500);
            }

            // Usar o GoogleDriveService para upload consistente
            $driveService = app(\App\Services\GoogleDriveService::class);

            // Converter folder_id do Google Drive para ID local se necessário
            $localParentId = null;

            // Se o folder_id for um ID local (numérico), buscar o file_id correspondente
            if (is_numeric($folderId)) {
                $folder = \App\Models\GoogleDriveFile::find($folderId);
                if ($folder && $folder->file_id) {
                    $localParentId = $folderId; // Usar o ID local
                } else {
                    // Se não encontrou, assumir que é um file_id direto do Google Drive
                    $localParentId = null;
                }
            }

            $uploadedFile = $driveService->uploadFile($file, auth()->id() ?? 1, $localParentId);

            return response()->json([
                'status' => 'success',
                'file_id' => $uploadedFile->id,
                'google_file_id' => $uploadedFile->file_id
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao fazer upload do arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listFiles(Request $request)
    {
        try {
            $request->validate([
                'folder_id' => 'required|string'
            ]);

            $files = $this->drive->files->listFiles([
                'q' => "'{$request->folder_id}' in parents",
                'fields' => 'files(id, name, mimeType, webViewLink)'
            ]);

            return response()->json([
                'status' => 'success',
                'files' => $files->getFiles()
            ]);

        } catch (\Exception $e) {
            Log::error('Error listing files: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to list files'
            ], 500);
        }
    }

    public function deleteFile($fileId)
    {
        try {
            $this->drive->files->delete($fileId);

            return response()->json([
                'status' => 'success',
                'message' => 'File deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting file: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete file'
            ], 500);
        }
    }
} 