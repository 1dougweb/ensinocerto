<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use App\Models\GoogleDriveFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class GoogleDriveService
{
    protected $client;
    protected $service;
    protected $rootFolderId;

    public function __construct()
    {
        try {
            // Verificar se estamos em modo de teste
            if (config('app.env') === 'testing' || config('services.google.test_mode', false)) {
                $this->setupTestMode();
                return;
            }
            
            $this->client = new Client();
            
            // Configurar timeout para melhorar performance
            $this->client->setHttpClient(new \GuzzleHttp\Client([
                'timeout' => 120, // 2 minutos para uploads (reduzido de 5 para melhor performance)
                'connect_timeout' => 15, // 15 segundos para conexão (reduzido de 30)
            ]));
            
            // Usar conta de serviço
            $credentialsPath = storage_path('app/google-credentials.json');
            
            if (!file_exists($credentialsPath)) {
                throw new \Exception("Arquivo de credenciais do Google Drive não encontrado em: {$credentialsPath}");
            }
            
            // Sempre usar OAuth 2.0 com as novas credenciais
            \Log::info('GoogleDriveService: Usando OAuth 2.0');
            
            // Configurar OAuth2 com as credenciais do .env
            $this->client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $this->client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $this->client->setScopes([Drive::DRIVE]);
            $this->client->setAccessType('offline');
            
            // Usar refresh token existente
            $refreshToken = env('GOOGLE_DRIVE_REFRESH_TOKEN');
            \Log::info('GoogleDriveService: Verificando refresh token', [
                'refresh_token_exists' => !empty($refreshToken),
                'refresh_token_length' => strlen($refreshToken ?? ''),
                'env_value' => env('GOOGLE_DRIVE_REFRESH_TOKEN') ? 'present' : 'missing'
            ]);
            
            if (!empty($refreshToken)) {
                try {
                    // Obter novo access token usando refresh token
                    $accessToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                    
                    if (isset($accessToken['error'])) {
                        \Log::warning('Erro ao renovar token: ' . ($accessToken['error_description'] ?? $accessToken['error']));
                        throw new \Exception('Erro ao renovar token OAuth2');
                    }
                    
                    $this->client->setAccessToken($accessToken);
                    \Log::info('GoogleDriveService: Token OAuth2 renovado com sucesso');
                    
                } catch (\Exception $e) {
                    \Log::error('Erro ao renovar token OAuth2: ' . $e->getMessage());
                    throw new \Exception('Erro ao configurar OAuth2. Verifique as credenciais.');
                }
            } else {
                throw new \Exception('Refresh token não encontrado no .env');
            }
            
            // Adicionar escopo
            $this->client->addScope(Drive::DRIVE);
            
            $this->service = new Drive($this->client);
            $this->rootFolderId = config('services.google.root_folder_id');
            
            if (empty($this->rootFolderId)) {
                throw new \Exception("ID da pasta raiz do Google Drive não configurado. Adicione GOOGLE_DRIVE_ROOT_FOLDER_ID ao seu arquivo .env");
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao inicializar o serviço do Google Drive: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Configura o modo de teste para o Google Drive
     */
    private function setupTestMode()
    {
        \Log::info('GoogleDriveService::setupTestMode - Inicializando em modo de teste');
        $this->rootFolderId = 'test_root_folder_id';
        // Não inicializa client e service reais
    }
    
    /**
     * Verifica se o serviço está em modo de teste
     */
    private function isTestMode()
    {
        return config('app.env') === 'testing' || config('services.google.test_mode', false);
    }

    /**
     * Verifica e cria a pasta raiz se necessário
     */
    public function ensureRootFolder()
    {
        try {
            if ($this->isTestMode()) {
                return ['status' => 'test_mode', 'message' => 'Modo de teste - pasta raiz não verificada'];
            }

            if (!$this->service) {
                throw new \Exception('Serviço Google Drive não inicializado');
            }

            // Verificar se a pasta raiz existe
            try {
                $folder = $this->service->files->get($this->rootFolderId, [
                    'fields' => 'id, name, mimeType, trashed'
                ]);

                if ($folder->getTrashed()) {
                    \Log::warning('GoogleDriveService::ensureRootFolder - Pasta raiz está na lixeira, tentando restaurar ou criar nova');

                    // Tentar criar uma nova pasta raiz
                    $newFolder = $this->createFolder('Documentos Sistema', null, 'root');
                    $this->rootFolderId = $newFolder->file_id;

                    \Log::info('GoogleDriveService::ensureRootFolder - Nova pasta raiz criada', [
                        'old_folder_id' => $this->rootFolderId,
                        'new_folder_id' => $newFolder->file_id,
                        'new_folder_name' => $newFolder->name
                    ]);

                    return [
                        'status' => 'created',
                        'message' => 'Nova pasta raiz criada pois a anterior estava na lixeira',
                        'folder_id' => $newFolder->file_id
                    ];
                }

                return [
                    'status' => 'exists',
                    'message' => 'Pasta raiz existe e está acessível',
                    'folder_id' => $this->rootFolderId,
                    'folder_name' => $folder->getName()
                ];

            } catch (\Google\Service\Exception $e) {
                if ($e->getCode() === 404) {
                    \Log::warning('GoogleDriveService::ensureRootFolder - Pasta raiz não encontrada, criando nova');

                    // Criar nova pasta raiz
                    $newFolder = $this->createFolder('Documentos Sistema', null, 'root');
                    $this->rootFolderId = $newFolder->file_id;

                    return [
                        'status' => 'created',
                        'message' => 'Pasta raiz não encontrada, nova pasta criada',
                        'folder_id' => $newFolder->file_id
                    ];
                }
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('GoogleDriveService::ensureRootFolder - Erro ao verificar pasta raiz', [
                'error' => $e->getMessage(),
                'root_folder_id' => $this->rootFolderId
            ]);

            return [
                'status' => 'error',
                'message' => 'Erro ao verificar pasta raiz: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Testa a conexão com o Google Drive
     */
    public function testConnection()
    {
        try {
            if ($this->isTestMode()) {
                return ['status' => 'test_mode', 'message' => 'Serviço em modo de teste'];
            }

            if (!$this->service) {
                return ['status' => 'error', 'message' => 'Serviço não inicializado'];
            }

            // Primeiro, verificar se a pasta raiz existe
            try {
                $folder = $this->service->files->get($this->rootFolderId, [
                    'fields' => 'id, name, mimeType, trashed'
                ]);

                if ($folder->getTrashed()) {
                    return [
                        'status' => 'error',
                        'message' => 'A pasta raiz foi movida para a lixeira',
                        'folder_id' => $this->rootFolderId
                    ];
                }

                \Log::info('GoogleDriveService::testConnection - Pasta raiz verificada', [
                    'folder_id' => $this->rootFolderId,
                    'folder_name' => $folder->getName(),
                    'mime_type' => $folder->getMimeType()
                ]);

            } catch (\Google\Service\Exception $e) {
                if ($e->getCode() === 404) {
                    return [
                        'status' => 'error',
                        'message' => 'Pasta raiz não encontrada. Verifique se o ID da pasta está correto.',
                        'folder_id' => $this->rootFolderId,
                        'error_code' => 404
                    ];
                }
                throw $e;
            }

            // Tentar listar arquivos da pasta raiz para testar conexão
            $response = $this->service->files->listFiles([
                'q' => "'{$this->rootFolderId}' in parents and trashed = false",
                'pageSize' => 1,
                'fields' => 'files(id, name)'
            ]);

            return [
                'status' => 'success',
                'message' => 'Conexão com Google Drive estabelecida com sucesso',
                'files_count' => count($response->getFiles()),
                'root_folder_id' => $this->rootFolderId
            ];

        } catch (\Google\Service\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro específico do Google Drive: ' . $e->getMessage(),
                'code' => $e->getCode(),
                'folder_id' => $this->rootFolderId
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro na conexão: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Lista os arquivos na pasta especificada
     *
     * @param string|null $folderId ID da pasta (null para pasta raiz)
     * @param string|null $search Termo de busca
     * @return \Illuminate\Support\Collection
     */
    public function listFiles($folderId = null, $search = null)
    {
        try {
            \Log::info('GoogleDriveService::listFiles - Iniciando listagem de arquivos', [
                'folderId' => $folderId,
                'search' => $search,
                'rootFolderId' => config('services.google.root_folder_id', 'test_root_folder_id')
            ]);
            
            // Se estiver em modo de teste, retornar dados do banco de dados
            if ($this->isTestMode()) {
                \Log::info('GoogleDriveService::listFiles - Modo de teste: retornando dados do banco de dados');
                
                // Se folderId for null, estamos na raiz
                if (!$folderId) {
                    $files = GoogleDriveFile::whereNull('parent_id')
                        ->where('is_trashed', false);
                } else {
                    $files = GoogleDriveFile::where('parent_id', $folderId)
                        ->where('is_trashed', false);
                }
                
                // Aplicar filtro de busca se fornecido
                if ($search) {
                    $files = $files->where('name', 'like', "%{$search}%");
                }
                
                $files = $files->get();
                
                // Adicionar atributo de tamanho formatado
                foreach ($files as $file) {
                    $file->formatted_size = $this->formatSize($file->size ?? 0);
                }
                
                \Log::info('GoogleDriveService::listFiles - Modo de teste: retornando ' . $files->count() . ' arquivos');
                return $files;
            }
            
            // Se folderId for null, estamos na raiz, então usamos o rootFolderId
            // Isso faz com que a API do Google Drive liste o conteúdo da pasta raiz diretamente
            $folderId = $folderId ?: $this->rootFolderId;
            
            \Log::info('GoogleDriveService::listFiles - Usando folderId: ' . $folderId);
            
            // Construir a query para listar apenas os arquivos da pasta especificada
            if ($folderId === 'root') {
                // Para a raiz, listar todos os arquivos que não estão na lixeira
                $query = "trashed = false";
            } else {
                // Para pastas específicas, usar a query normal
                $query = "'{$folderId}' in parents and trashed = false";
            }
            \Log::info('GoogleDriveService::listFiles - Query construída: ' . $query);
            \Log::info('GoogleDriveService::listFiles - Tipo do folderId: ' . gettype($folderId));
            \Log::info('GoogleDriveService::listFiles - Valor do folderId: ' . $folderId);
            
            // Adicionar termo de busca se fornecido
            if ($search) {
                $query .= " and name contains '{$search}'";
                \Log::info('GoogleDriveService::listFiles - Adicionado termo de busca: ' . $search);
            }
            
            $params = [
                'q' => $query,
                'fields' => 'files(id, name, mimeType, createdTime, modifiedTime, size, webViewLink, webContentLink, iconLink, thumbnailLink, parents)',
                'pageSize' => 100
            ];
            
            \Log::info('GoogleDriveService::listFiles - Parâmetros da consulta: ', $params);
            
            $results = $this->service->files->listFiles($params);
            \Log::info('GoogleDriveService::listFiles - Resultados obtidos da API');
            $files = collect();
            
            $apiFiles = $results->getFiles();
            \Log::info('GoogleDriveService::listFiles - Total de arquivos retornados pela API: ' . count($apiFiles));
            
            // Log detalhado dos arquivos retornados
            if (count($apiFiles) > 0) {
                \Log::info('GoogleDriveService::listFiles - Primeiros arquivos retornados:');
                foreach (array_slice($apiFiles, 0, 3) as $index => $file) {
                    \Log::info("  Arquivo {$index}:", [
                        'id' => $file->getId(),
                        'name' => $file->getName(),
                        'mimeType' => $file->getMimeType(),
                        'parents' => $file->getParents()
                    ]);
                }
            } else {
                \Log::warning('GoogleDriveService::listFiles - Nenhum arquivo retornado pela API');
            }
            
            if (count($apiFiles) == 0) {
                \Log::warning('GoogleDriveService::listFiles - Nenhum arquivo retornado pela API para a pasta: ' . $folderId);
                return $files;
            }
            
            foreach ($apiFiles as $file) {
                // Ignorar a pasta raiz se ela aparecer na listagem
                if ($file->getId() === $this->rootFolderId) {
                    \Log::info('GoogleDriveService::listFiles - Ignorando pasta raiz: ' . $file->getName());
                    continue;
                }
                
                $size = $file->getSize() ? $file->getSize() : 0;
                $isFolder = $file->getMimeType() === 'application/vnd.google-apps.folder';
                
                \Log::info('GoogleDriveService::listFiles - Processando arquivo: ' . $file->getName() . ' (ID: ' . $file->getId() . ')');
                
                // Criar um objeto simples com os dados da API
                $fileData = (object) [
                    'id' => $file->getId(), // Usar o file_id como ID
                    'file_id' => $file->getId(),
                    'name' => $file->getName(),
                    'mime_type' => $file->getMimeType(),
                    'web_view_link' => $file->getWebViewLink(),
                    'web_content_link' => $file->getWebContentLink(),
                    'thumbnail_link' => $file->getThumbnailLink(),
                    'size' => $size,
                    'is_folder' => $isFolder,
                    'formatted_size' => $this->formatSize($size),
                    'created_time' => $file->getCreatedTime(),
                    'modified_time' => $file->getModifiedTime(),
                    'parents' => $file->getParents(),
                    'parent_id' => $file->getParents() ? $file->getParents()[0] : null, // Extrair o primeiro parent_id
                    'is_starred' => false, // Valor padrão
                    'is_trashed' => false, // Valor padrão
                    'is_local' => false, // Valor padrão
                ];
                
                $files->push($fileData);
            }
            
            \Log::info('GoogleDriveService::listFiles - Total de arquivos processados e retornados: ' . $files->count());
            return $files;
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService::listFiles - Erro ao listar arquivos: ' . $e->getMessage());
            \Log::error('GoogleDriveService::listFiles - Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Sincroniza recursivamente todas as pastas e arquivos do Google Drive
     */
    public function syncAllFiles($folderId = null, $maxDepth = 10, $currentDepth = 0)
    {
        try {
            if ($currentDepth >= $maxDepth) {
                \Log::warning('GoogleDriveService::syncAllFiles - Profundidade máxima atingida: ' . $maxDepth);
                return collect();
            }

            \Log::info('GoogleDriveService::syncAllFiles - Iniciando sincronização recursiva', [
                'folderId' => $folderId,
                'currentDepth' => $currentDepth,
                'maxDepth' => $maxDepth
            ]);

            $folderId = $folderId ?: $this->rootFolderId;

            // Listar arquivos da pasta atual
            try {
                $files = $this->listFiles($folderId);
                $allFiles = collect();

                foreach ($files as $file) {
                    $allFiles->push($file);

                    // Se for uma pasta, sincronizar recursivamente
                    if ($file->is_folder) {
                        \Log::info('GoogleDriveService::syncAllFiles - Sincronizando subpasta: ' . $file->name . ' (ID: ' . $file->file_id . ')');
                        try {
                            $subFiles = $this->syncAllFiles($file->file_id, $maxDepth, $currentDepth + 1);
                            $allFiles = $allFiles->merge($subFiles);
                        } catch (\Exception $e) {
                            \Log::error('GoogleDriveService::syncAllFiles - Erro ao sincronizar subpasta: ' . $file->name . ' - ' . $e->getMessage());
                            // Continuar com outras pastas mesmo se uma falhar
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('GoogleDriveService::syncAllFiles - Erro ao listar arquivos da pasta: ' . $folderId . ' - ' . $e->getMessage());
                return collect(); // Retornar coleção vazia em caso de erro
            }

            \Log::info('GoogleDriveService::syncAllFiles - Sincronização concluída', [
                'folderId' => $folderId,
                'totalFiles' => $allFiles->count(),
                'currentDepth' => $currentDepth
            ]);

            return $allFiles;
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService::syncAllFiles - Erro na sincronização recursiva: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Formata o tamanho do arquivo para exibição
     */
    private function formatSize($bytes)
    {
        if ($bytes == 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    /**
     * Upload ultra-rápido para arquivos pequenos (< 5MB)
     */
    public function uploadSmallFile(UploadedFile $file, $userId, $parentId = null)
    {
        try {
            // Upload direto sem verificações
            $fileMetadata = new DriveFile([
                'name' => $file->getClientOriginalName(),
                'parents' => [$this->rootFolderId]
            ]);

            $uploadOptions = [
                'data' => file_get_contents($file->getRealPath()),
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id, name, mimeType, size'
            ];

            $uploadedFile = $this->service->files->create($fileMetadata, $uploadOptions);

            // Salvar no banco
            $dbFile = GoogleDriveFile::createOrUpdateFromGoogleDrive([
                'file_id' => $uploadedFile->getId(),
                'name' => $uploadedFile->getName(),
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'parent_id' => $parentId,
                'created_by' => $userId,
                'is_folder' => false,
                'is_trashed' => false
            ]);

            return $dbFile;

        } catch (\Exception $e) {
            throw new \Exception('Erro no upload: ' . $e->getMessage());
        }
    }

    /**
     * Upload de arquivo
     */
    public function uploadFile(UploadedFile $file, $userId, $parentId = null)
    {
        try {
            \Log::info('GoogleDriveService::uploadFile - Iniciando upload', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'parent_id' => $parentId,
                'user_id' => $userId
            ]);

            // Converter o parent_id local para o file_id do Google Drive
            $googleDriveParentId = $this->rootFolderId;
            $localParentId = null;
            
            if ($parentId) {
                $parentFolder = GoogleDriveFile::find($parentId);
                if ($parentFolder && !empty($parentFolder->file_id)) {
                    $googleDriveParentId = $parentFolder->file_id;
                    $localParentId = $parentFolder->id;
                }
            }

            // Determinar estratégia de upload baseada no tamanho do arquivo
            $fileSize = $file->getSize();
            $maxMultipartSize = 5 * 1024 * 1024; // 5 MB
            $useResumableUpload = $fileSize > $maxMultipartSize;

            // Preparar metadados do arquivo
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();

            // Verificar conversão apenas para arquivos que realmente precisam
            if ($fileSize < 1024 * 1024) { // Arquivos menores que 1MB
                $conversionMimeType = $this->getConversionMimeType($mimeType);
                if ($conversionMimeType) {
                    $mimeType = $conversionMimeType;
                }
            }

            $fileMetadata = new DriveFile([
                'name' => $originalName,
                'parents' => [$googleDriveParentId]
            ]);

            if ($mimeType !== $file->getMimeType()) {
                $fileMetadata->setMimeType($mimeType);
            }

            // Adicionar texto indexável apenas para arquivos grandes ou tipos específicos
            if ($fileSize > 1024 * 1024) { // Apenas para arquivos > 1MB
                $indexableText = $this->getIndexableText($originalName, $mimeType);
                if ($indexableText) {
                    try {
                        $contentHints = new \Google\Service\Drive\DriveFileContentHints();
                        $contentHints->setIndexableText($indexableText);
                        $fileMetadata->setContentHints($contentHints);
                    } catch (\Exception $e) {
                        // Continuar sem texto indexável
                    }
                }
            }

            // Ler conteúdo do arquivo
            $content = file_get_contents($file->getRealPath());
            if ($content === false) {
                throw new \Exception('Falha ao ler o conteúdo do arquivo');
            }

            // Verificar se o serviço está inicializado
            if (!$this->service) {
                throw new \Exception('Serviço Google Drive não está inicializado');
            }

            // Preparar opções de upload otimizadas
            $uploadOptions = [
                'data' => $content,
                'mimeType' => $file->getMimeType(),
                'uploadType' => $useResumableUpload ? 'resumable' : 'multipart',
                'fields' => 'id, name, mimeType, webViewLink, webContentLink, thumbnailLink, size, createdTime, modifiedTime',
                'supportsAllDrives' => true
            ];

            if ($useResumableUpload) {
                \Log::info('GoogleDriveService::uploadFile - Configurado para resumable upload');
            }

            // Criar e executar upload
            $request = $this->service->files->create($fileMetadata, $uploadOptions);

            // Executar o upload - corrigido para versão mais recente da API
            try {
                if ($uploadOptions['uploadType'] === 'multipart') {
                    $uploadedFile = $request;
                } else {
                    if (method_exists($request, 'execute')) {
                        $uploadedFile = $request->execute();
                    } else {
                        $uploadedFile = $request;
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('GoogleDriveService::uploadFile - Erro ao executar upload, tentando fallback', [
                    'error' => $e->getMessage(),
                    'upload_type' => $uploadOptions['uploadType']
                ]);
                $uploadedFile = $request;
            }

            \Log::info('GoogleDriveService::uploadFile - Upload concluído com sucesso', [
                'file_id' => $uploadedFile->getId(),
                'file_name' => $uploadedFile->getName(),
                'upload_type' => $useResumableUpload ? 'resumable' : 'multipart'
            ]);

            // Salvar no banco de dados
            $dbFile = GoogleDriveFile::createOrUpdateFromGoogleDrive([
                'file_id' => $uploadedFile->getId(),
                'name' => $uploadedFile->getName(),
                'mime_type' => $uploadedFile->getMimeType(),
                'web_view_link' => $uploadedFile->getWebViewLink(),
                'web_content_link' => $uploadedFile->getWebContentLink(),
                'thumbnail_link' => $uploadedFile->getThumbnailLink(),
                'size' => $uploadedFile->getSize(),
                'parent_id' => $localParentId,
                'created_by' => $userId,
                'is_folder' => false,
                'is_trashed' => false
            ]);

            return $dbFile;

        } catch (\Google\Service\Exception $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();

            \Log::error('GoogleDriveService::uploadFile - Erro específico do Google Service', [
                'error' => $errorMessage,
                'code' => $errorCode,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize()
            ]);

            // Tratar erros específicos
            if ($errorCode === 404) {
                throw new \Exception('Pasta de destino não encontrada no Google Drive.');
            } elseif ($errorCode === 403) {
                throw new \Exception('Permissões insuficientes para fazer upload na pasta especificada.');
            } elseif ($errorCode === 401) {
                throw new \Exception('Token de acesso expirado ou inválido. Renove as credenciais do Google Drive.');
            } else {
                throw new \Exception('Erro no serviço Google Drive: ' . $errorMessage);
            }
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService::uploadFile - Falha geral no upload', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize()
            ]);
            throw $e;
        }
    }

    /**
     * Criar pasta
     */
    public function createFolder($name, $userId, $parentId = null)
    {
        try {
            $parentId = $parentId ?: $this->rootFolderId;
            
            \Log::info('GoogleDriveService::createFolder - Criando pasta', [
                'parent_id' => $parentId,
                'root_folder_id' => $this->rootFolderId
            ]);

            $fileMetadata = new DriveFile([
                'name' => $name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentId]
            ]);

            $folder = $this->service->files->create($fileMetadata, [
                'fields' => 'id, name, mimeType, webViewLink',
                'supportsAllDrives' => true
            ]);

            // Determinar o parent_id local no banco de dados
            $localParentId = null;
            if ($parentId !== $this->rootFolderId) {
                $parentFolder = GoogleDriveFile::where('file_id', $parentId)->first();
                if ($parentFolder) {
                    $localParentId = $parentFolder->id;
                }
            }

            return GoogleDriveFile::createOrUpdateFromGoogleDrive([
                'file_id' => $folder->getId(),
                'name' => $folder->getName(),
                'mime_type' => $folder->getMimeType(),
                'web_view_link' => $folder->getWebViewLink(),
                'parent_id' => $localParentId,
                'is_folder' => true,
                'created_by' => $userId,
                'is_trashed' => false
            ]);
        } catch (\Exception $e) {
            \Log::warning('Falha ao criar pasta no Google Drive: ' . $e->getMessage());
            
            // Fallback: criar pasta local
            $localParentId = null;
            if ($parentId !== $this->rootFolderId) {
                $parentFolder = GoogleDriveFile::where('file_id', $parentId)->first();
                if ($parentFolder) {
                    $localParentId = $parentFolder->id;
                }
            }
            
            return GoogleDriveFile::createOrUpdateFromGoogleDrive([
                'file_id' => 'local_folder_' . uniqid(),
                'name' => $name,
                'mime_type' => 'application/vnd.google-apps.folder',
                'web_view_link' => '#',
                'parent_id' => $localParentId,
                'is_folder' => true,
                'created_by' => $userId,
                'is_local' => true,
                'is_trashed' => false
            ]);
        }
    }

    /**
     * Excluir arquivo/pasta
     */
    public function delete($fileId, $recursive = false)
    {
        try {
            \Log::info('GoogleDriveService::delete - Iniciando exclusão', [
                'fileId' => $fileId,
                'recursive' => $recursive
            ]);
            
            // Se estiver em modo de teste, simular a operação
            if ($this->isTestMode()) {
                \Log::info('GoogleDriveService::delete - Modo de teste: simulando exclusão');
                return true;
            }
            
            // Primeiro, obter informações do arquivo/pasta
            $fileInfo = $this->service->files->get($fileId, [
                'fields' => 'id, name, mimeType, trashed'
            ]);
            
            if (!$fileInfo) {
                \Log::error('GoogleDriveService::delete - Arquivo não encontrado no Google Drive', ['fileId' => $fileId]);
                throw new \Exception('Arquivo não encontrado no Google Drive');
            }
            
            // Verificar se é uma pasta
            $isFolder = $fileInfo->getMimeType() === 'application/vnd.google-apps.folder';
            
            if ($isFolder) {
                \Log::info('GoogleDriveService::delete - Tentando excluir pasta', [
                    'fileId' => $fileId,
                    'name' => $fileInfo->getName(),
                    'recursive' => $recursive
                ]);
                
                // Verificar se a pasta está vazia
                $children = $this->service->files->listFiles([
                    'q' => "'{$fileId}' in parents and trashed = false",
                    'fields' => 'files(id, name, mimeType)',
                    'pageSize' => 1000
                ]);
                
                if ($children->getFiles() && count($children->getFiles()) > 0) {
                    if ($recursive) {
                        \Log::info('GoogleDriveService::delete - Excluindo pasta recursivamente', [
                            'fileId' => $fileId,
                            'name' => $fileInfo->getName(),
                            'children_count' => count($children->getFiles())
                        ]);
                        
                        // Excluir todos os arquivos e pastas dentro da pasta
                        foreach ($children->getFiles() as $child) {
                            $childIsFolder = $child->getMimeType() === 'application/vnd.google-apps.folder';
                            
                            if ($childIsFolder) {
                                // Se é uma subpasta, excluir recursivamente
                                $this->delete($child->getId(), true);
                            } else {
                                // Se é um arquivo, excluir diretamente
                                $this->service->files->delete($child->getId());
                                \Log::info('GoogleDriveService::delete - Arquivo excluído', [
                                    'childId' => $child->getId(),
                                    'childName' => $child->getName()
                                ]);
                            }
                        }
                        
                        \Log::info('GoogleDriveService::delete - Todos os conteúdos da pasta foram excluídos');
                    } else {
                        \Log::warning('GoogleDriveService::delete - Tentativa de excluir pasta não vazia', [
                            'fileId' => $fileId,
                            'name' => $fileInfo->getName(),
                            'children_count' => count($children->getFiles())
                        ]);
                        throw new \Exception('Não é possível excluir uma pasta que contém arquivos ou outras pastas. Use a opção "Excluir Recursivamente" para remover tudo.');
                    }
                }
                
                \Log::info('GoogleDriveService::delete - Pasta está vazia, prosseguindo com exclusão');
            } else {
                \Log::info('GoogleDriveService::delete - Excluindo arquivo', [
                    'fileId' => $fileId,
                    'name' => $fileInfo->getName()
                ]);
            }
            
            // Executar a exclusão
            $this->service->files->delete($fileId);
            
            \Log::info('GoogleDriveService::delete - Exclusão realizada com sucesso', [
                'fileId' => $fileId,
                'name' => $fileInfo->getName(),
                'isFolder' => $isFolder,
                'recursive' => $recursive
            ]);
            
            return true;
        } catch (\Google\Service\Exception $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            \Log::error('GoogleDriveService::delete - Erro da API do Google Drive', [
                'fileId' => $fileId,
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'errors' => $e->getErrors()
            ]);
            
            // Tratar erros específicos
            if ($errorCode === 403) {
                throw new \Exception('Você não tem permissão para excluir este arquivo/pasta.');
            } elseif ($errorCode === 404) {
                throw new \Exception('Arquivo/pasta não encontrado no Google Drive.');
            } else {
                throw new \Exception("Erro do Google Drive: {$errorMessage}");
            }
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService::delete - Erro geral ao excluir arquivo', [
                'fileId' => $fileId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Excluir pasta recursivamente (incluindo todo o conteúdo)
     */
    public function deleteRecursive($fileId)
    {
        return $this->delete($fileId, true);
    }

    /**
     * Mover para lixeira
     */
    public function trash($fileId)
    {
        try {
            $file = new DriveFile();
            $file->setTrashed(true);
            
            $this->service->files->update($fileId, $file);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Restaurar da lixeira
     */
    public function untrash($fileId)
    {
        try {
            $file = new DriveFile();
            $file->setTrashed(false);
            
            $this->service->files->update($fileId, $file);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Renomear arquivo/pasta
     */
    public function rename($fileId, $newName)
    {
        try {
            $file = new DriveFile();
            $file->setName($newName);
            
            $this->service->files->update($fileId, $file);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Mover arquivo/pasta
     */
    public function move($fileId, $newParentId)
    {
        try {
            \Log::info('GoogleDriveService::move - Iniciando movimentação de arquivo', [
                'fileId' => $fileId,
                'newParentId' => $newParentId
            ]);
            
            // Se estiver em modo de teste, simular a operação
            if ($this->isTestMode()) {
                \Log::info('GoogleDriveService::move - Modo de teste: simulando movimentação');
                
                // Atualizar o registro no banco de dados local
                $dbFile = GoogleDriveFile::where('file_id', $fileId)->first();
                
                if ($dbFile) {
                    // Determinar o novo parent_id no banco de dados
                    $parentId = null;
                    
                    // Se não for a pasta raiz, buscar o ID local correspondente
                    if ($newParentId !== $this->rootFolderId) {
                        $parentFolder = GoogleDriveFile::where('file_id', $newParentId)->first();
                        if ($parentFolder) {
                            $parentId = $parentFolder->id;
                        }
                    }
                    
                    // Atualizar o registro no banco de dados
                    $dbFile->parent_id = $parentId;
                    $dbFile->save();
                    
                    \Log::info('GoogleDriveService::move - Modo de teste: registro atualizado no banco de dados', [
                        'dbFileId' => $dbFile->id,
                        'newParentId' => $parentId
                    ]);
                    
                    return true;
                } else {
                    \Log::error('GoogleDriveService::move - Modo de teste: arquivo não encontrado no banco de dados');
                    return false;
                }
            }
            
            // Obter o arquivo do Google Drive
            $file = $this->service->files->get($fileId, ['fields' => 'parents']);
            
            if (!$file) {
                \Log::error('GoogleDriveService::move - Arquivo não encontrado no Google Drive');
                return false;
            }
            
            $previousParents = join(',', $file->getParents());
            \Log::info('GoogleDriveService::move - Parents anteriores: ' . $previousParents);
            
            // Mover o arquivo no Google Drive
            $result = $this->service->files->update($fileId, new DriveFile(), [
                'addParents' => $newParentId,
                'removeParents' => $previousParents,
                'fields' => 'id, parents'
            ]);
            
            if (!$result) {
                \Log::error('GoogleDriveService::move - Falha ao mover arquivo no Google Drive');
                return false;
            }
            
            \Log::info('GoogleDriveService::move - Arquivo movido com sucesso no Google Drive', [
                'newParents' => implode(',', $result->getParents())
            ]);
            
            // Atualizar o registro no banco de dados local (incluindo soft deleted)
            $dbFile = GoogleDriveFile::withTrashed()->where('file_id', $fileId)->first();
            
            if ($dbFile) {
                // Se está soft deleted, restaurar
                if ($dbFile->trashed()) {
                    $dbFile->restore();
                    \Log::info('GoogleDriveService::move - Arquivo restaurado do soft delete', [
                        'file_id' => $fileId,
                        'local_id' => $dbFile->id
                    ]);
                }
                // Determinar o novo parent_id no banco de dados
                $parentId = null;
                
                // Se não for a pasta raiz, buscar o ID local correspondente
                if ($newParentId !== $this->rootFolderId) {
                    $parentFolder = GoogleDriveFile::where('file_id', $newParentId)->first();
                    if ($parentFolder) {
                        $parentId = $parentFolder->id;
                    } else {
                        // Se a pasta de destino não existe no banco de dados local, criar
                        $folderInfo = $this->service->files->get($newParentId, [
                            'fields' => 'id, name, mimeType'
                        ]);
                        
                        if ($folderInfo) {
                                                    $parentFolder = GoogleDriveFile::createOrUpdateFromGoogleDrive([
                            'file_id' => $newParentId,
                            'name' => $folderInfo->getName(),
                            'mime_type' => $folderInfo->getMimeType(),
                            'is_folder' => true,
                            'parent_id' => null, // Assumimos que é raiz se não sabemos
                            'created_by' => auth()->id() ?? 1,
                            'is_trashed' => false,
                        ]);
                            
                            $parentId = $parentFolder->id;
                            \Log::info('GoogleDriveService::move - Pasta de destino criada no banco de dados', [
                                'folderId' => $parentId,
                                'folderName' => $folderInfo->getName()
                            ]);
                        }
                    }
                }
                
                // Atualizar o registro no banco de dados
                $dbFile->parent_id = $parentId;
                $dbFile->save();
                
                \Log::info('GoogleDriveService::move - Registro atualizado no banco de dados', [
                    'dbFileId' => $dbFile->id,
                    'newParentId' => $parentId
                ]);
            } else {
                // Se o arquivo não existe no banco de dados local, criar
                $fileInfo = $this->service->files->get($fileId, [
                    'fields' => 'id, name, mimeType, size'
                ]);
                
                if ($fileInfo) {
                    // Determinar o parent_id para o novo registro
                    $parentId = null;
                    if ($newParentId !== $this->rootFolderId) {
                        $parentFolder = GoogleDriveFile::withTrashed()->where('file_id', $newParentId)->first();
                        if ($parentFolder) {
                            $parentId = $parentFolder->id;
                        }
                    }
                    
                    // Verificar se já existe um registro soft deleted
                    $existingFile = GoogleDriveFile::withTrashed()->where('file_id', $fileId)->first();
                    
                    if ($existingFile) {
                        if ($existingFile->trashed()) {
                            // Restaurar e atualizar
                            $existingFile->restore();
                            $existingFile->update([
                                'name' => $fileInfo->getName(),
                                'mime_type' => $fileInfo->getMimeType(),
                                'size' => $fileInfo->getSize(),
                                'is_folder' => $fileInfo->getMimeType() === 'application/vnd.google-apps.folder',
                                'parent_id' => $parentId,
                                'is_trashed' => false,
                            ]);
                            
                            \Log::info('GoogleDriveService::move - Arquivo restaurado do soft delete', [
                                'fileId' => $fileId,
                                'fileName' => $fileInfo->getName(),
                                'parentId' => $parentId
                            ]);
                        } else {
                            // Já existe e não está soft deleted
                            \Log::info('GoogleDriveService::move - Arquivo já existe no banco de dados', [
                                'fileId' => $fileId,
                                'fileName' => $fileInfo->getName()
                            ]);
                        }
                    } else {
                        // Criar novo registro
                        GoogleDriveFile::createOrUpdateFromGoogleDrive([
                            'file_id' => $fileId,
                            'name' => $fileInfo->getName(),
                            'mime_type' => $fileInfo->getMimeType(),
                            'size' => $fileInfo->getSize(),
                            'is_folder' => $fileInfo->getMimeType() === 'application/vnd.google-apps.folder',
                            'parent_id' => $parentId,
                            'created_by' => auth()->id() ?? 1,
                            'is_trashed' => false,
                        ]);
                        
                        \Log::info('GoogleDriveService::move - Arquivo criado no banco de dados', [
                            'fileId' => $fileId,
                            'fileName' => $fileInfo->getName(),
                            'parentId' => $parentId
                        ]);
                    }
                }
            }
            
            return true;
        } catch (\Google\Service\Exception $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            \Log::error('GoogleDriveService::move - Erro da API do Google Drive', [
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'file_id' => $fileId,
                'new_parent_id' => $newParentId
            ]);
            
            // Tratar especificamente o erro 403 (permissões insuficientes)
            if ($errorCode === 403) {
                throw new \Exception("Erro Google Drive: " . json_encode([
                    'error' => [
                        'code' => $errorCode,
                        'message' => $errorMessage,
                        'errors' => $e->getErrors()
                    ]
                ], JSON_PRETTY_PRINT));
            }
            
            // Para outros erros, re-throw a exceção original
            throw $e;
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService::move - Erro geral ao mover arquivo: ' . $e->getMessage());
            \Log::error('GoogleDriveService::move - Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Download de arquivo
     */
    public function download($fileId)
    {
        try {
            // Obter informações do arquivo
            $file = $this->service->files->get($fileId, ['fields' => 'name, mimeType']);
            
            // Verificar se é um arquivo do Google (Docs, Sheets, etc)
            if (strpos($file->getMimeType(), 'application/vnd.google-apps') === 0) {
                // Determinar o formato de exportação apropriado
                $exportMimeType = $this->getExportMimeType($file->getMimeType());
                
                if ($exportMimeType) {
                    // Exportar o arquivo
                    $content = $this->service->files->export($fileId, $exportMimeType, ['alt' => 'media']);
                    
                    // Determinar a extensão de arquivo apropriada
                    $extension = $this->getFileExtension($exportMimeType);
                    $filename = $file->getName() . $extension;
                    
                    return [
                        'content' => $content->getBody()->getContents(),
                        'filename' => $filename,
                        'mime_type' => $exportMimeType
                    ];
                }
            }
            
            // Para arquivos regulares, fazer download direto
            $content = $this->service->files->get($fileId, ['alt' => 'media']);
            
            return [
                'content' => $content->getBody()->getContents(),
                'filename' => $file->getName(),
                'mime_type' => $file->getMimeType()
            ];
        } catch (\Exception $e) {
            \Log::error('Erro ao fazer download do arquivo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter informações de um arquivo
     */
    public function getFileInfo($fileId)
    {
        try {
            return $this->service->files->get($fileId, [
                'fields' => 'id, name, mimeType, size, webViewLink, webContentLink, thumbnailLink, parents, owners, permissions'
            ]);
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService::getFileInfo - Erro ao obter informações do arquivo', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Determina o tipo MIME para exportação com base no tipo MIME do Google
     */
    private function getExportMimeType($googleMimeType)
    {
        $exportFormats = [
            'application/vnd.google-apps.document' => 'application/pdf',
            'application/vnd.google-apps.spreadsheet' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.google-apps.presentation' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.google-apps.drawing' => 'image/png',
        ];
        
        return $exportFormats[$googleMimeType] ?? null;
    }

    /**
     * Determina a extensão de arquivo com base no tipo MIME
     */
    private function getFileExtension($mimeType)
    {
        $extensions = [
            'application/pdf' => '.pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',
            'image/png' => '.png',
            'image/jpeg' => '.jpg',
        ];
        
        return $extensions[$mimeType] ?? '';
    }

    /**
     * Listar permissões de um arquivo
     */
    public function listPermissions($fileId)
    {
        try {
            // Se for um arquivo local, retornar permissões vazias
            if (strpos($fileId, 'local_') === 0) {
                return [];
            }

            $permissions = $this->service->permissions->listPermissions($fileId, [
                'supportsAllDrives' => true
            ]);

            return $permissions->getPermissions();
        } catch (\Exception $e) {
            \Log::error('Erro ao listar permissões: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Compartilhar arquivo com usuário
     */
    public function shareWithUser($fileId, $email, $role = 'reader', $notify = true)
    {
        try {
            // Se for um arquivo local, não é possível compartilhar
            if (strpos($fileId, 'local_') === 0) {
                throw new \Exception('Não é possível compartilhar arquivos locais');
            }

            $permission = new \Google\Service\Drive\Permission();
            $permission->setType('user');
            $permission->setRole($role);
            $permission->setEmailAddress($email);

            $result = $this->service->permissions->create($fileId, $permission, [
                'sendNotificationEmail' => $notify,
                'supportsAllDrives' => true
            ]);

            return $result;
        } catch (\Exception $e) {
            \Log::error('Erro ao compartilhar arquivo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Criar link público
     */
    public function createPublicLink($fileId, $role = 'reader')
    {
        try {
            // Se for um arquivo local, não é possível criar link público
            if (strpos($fileId, 'local_') === 0) {
                throw new \Exception('Não é possível criar link público para arquivos locais');
            }

            $permission = new \Google\Service\Drive\Permission();
            $permission->setType('anyone');
            $permission->setRole($role);

            $result = $this->service->permissions->create($fileId, $permission, [
                'supportsAllDrives' => true
            ]);

            // Obter o link compartilhável
            $file = $this->service->files->get($fileId, ['fields' => 'webViewLink']);
            
            return [
                'permission' => $result,
                'link' => $file->getWebViewLink()
            ];
        } catch (\Exception $e) {
            \Log::error('Erro ao criar link público: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gerar código de incorporação
     */
    public function generateEmbedLink($fileId, $width = '100%', $height = '600px')
    {
        try {
            // Se for um arquivo local, não é possível gerar embed
            if (strpos($fileId, 'local_') === 0) {
                throw new \Exception('Não é possível gerar código de incorporação para arquivos locais');
            }

            $file = $this->service->files->get($fileId, ['fields' => 'webViewLink']);
            $viewLink = $file->getWebViewLink();
            
            // Converter para link de incorporação
            $embedLink = str_replace('/view', '/preview', $viewLink);
            
            $embedCode = '<iframe src="' . $embedLink . '" width="' . $width . '" height="' . $height . '" frameborder="0"></iframe>';
            
            return [
                'embed_code' => $embedCode,
                'embed_link' => $embedLink
            ];
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar código de incorporação: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remover permissão
     */
    public function removePermission($fileId, $permissionId)
    {
        try {
            // Se for um arquivo local, não é possível remover permissões
            if (strpos($fileId, 'local_') === 0) {
                throw new \Exception('Não é possível remover permissões de arquivos locais');
            }

            $this->service->permissions->delete($fileId, $permissionId, [
                'supportsAllDrives' => true
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Erro ao remover permissão: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar permissão
     */
    public function updatePermission($fileId, $permissionId, $role)
    {
        try {
            // Se for um arquivo local, não é possível atualizar permissões
            if (strpos($fileId, 'local_') === 0) {
                throw new \Exception('Não é possível atualizar permissões de arquivos locais');
            }

            $permission = new \Google\Service\Drive\Permission();
            $permission->setRole($role);

            $result = $this->service->permissions->update($fileId, $permissionId, $permission, [
                'supportsAllDrives' => true
            ]);

            return $result;
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar permissão: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verificar permissões de um arquivo
     */
    public function checkFilePermissions($fileId)
    {
        try {
            if ($this->isTestMode()) {
                return [
                    'can_edit' => true,
                    'can_move' => true,
                    'can_delete' => true,
                    'owner' => 'test@example.com'
                ];
            }
            
            // Obter informações do arquivo incluindo permissões
            $file = $this->service->files->get($fileId, [
                'fields' => 'id, name, owners, permissions, capabilities'
            ]);
            
            if (!$file) {
                return [
                    'can_edit' => false,
                    'can_move' => false,
                    'can_delete' => false,
                    'owner' => null,
                    'error' => 'Arquivo não encontrado'
                ];
            }
            
            // Verificar se o usuário atual é o proprietário
            $currentUserEmail = $this->getCurrentUserEmail();
            $isOwner = false;
            
            if ($file->getOwners()) {
                foreach ($file->getOwners() as $owner) {
                    if ($owner->getEmailAddress() === $currentUserEmail) {
                        $isOwner = true;
                        break;
                    }
                }
            }
            
            // Verificar capacidades do arquivo
            $capabilities = $file->getCapabilities();
            $canEdit = $capabilities ? $capabilities->getCanEdit() : false;
            $canMove = $capabilities ? $capabilities->getCanMoveItemWithinDrive() : false;
            $canDelete = $capabilities ? $capabilities->getCanDelete() : false;
            
            return [
                'can_edit' => $canEdit,
                'can_move' => $canMove,
                'can_delete' => $canDelete,
                'is_owner' => $isOwner,
                'owner' => $file->getOwners() ? $file->getOwners()[0]->getEmailAddress() : null,
                'current_user' => $currentUserEmail
            ];
            
        } catch (\Google\Service\Exception $e) {
            \Log::error('GoogleDriveService::checkFilePermissions - Erro da API', [
                'file_id' => $fileId,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]);
            
            return [
                'can_edit' => false,
                'can_move' => false,
                'can_delete' => false,
                'owner' => null,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService::checkFilePermissions - Erro geral', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'can_edit' => false,
                'can_move' => false,
                'can_delete' => false,
                'owner' => null,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obter email do usuário atual
     */
    private function getCurrentUserEmail()
    {
        try {
            if ($this->isTestMode()) {
                return 'test@example.com';
            }

            // Obter informações do usuário atual
            $about = $this->service->about->get(['fields' => 'user']);

            if ($about && $about->getUser()) {
                return $about->getUser()->getEmailAddress();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService::getCurrentUserEmail - Erro ao obter email do usuário', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Verificar se um MIME type pode ser convertido pelo Google Drive
     * Baseado na documentação: verificar importFormats do recurso about
     */
    private function getConversionMimeType(string $mimeType): ?string
    {
        $conversionMap = [
            // Documentos
            'application/msword' => 'application/vnd.google-apps.document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'application/vnd.google-apps.document',
            'text/plain' => 'application/vnd.google-apps.document',
            'text/html' => 'application/vnd.google-apps.document',
            'application/rtf' => 'application/vnd.google-apps.document',
            'application/vnd.oasis.opendocument.text' => 'application/vnd.google-apps.document',

            // Planilhas
            'application/vnd.ms-excel' => 'application/vnd.google-apps.spreadsheet',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'application/vnd.google-apps.spreadsheet',
            'text/csv' => 'application/vnd.google-apps.spreadsheet',
            'text/tab-separated-values' => 'application/vnd.google-apps.spreadsheet',
            'application/vnd.oasis.opendocument.spreadsheet' => 'application/vnd.google-apps.spreadsheet',

            // Apresentações
            'application/vnd.ms-powerpoint' => 'application/vnd.google-apps.presentation',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'application/vnd.google-apps.presentation',
            'application/vnd.oasis.opendocument.presentation' => 'application/vnd.google-apps.presentation',

            // Imagens (convertidas para Docs com OCR)
            'image/jpeg' => 'application/vnd.google-apps.document',
            'image/png' => 'application/vnd.google-apps.document',
            'image/gif' => 'application/vnd.google-apps.document',
            'image/bmp' => 'application/vnd.google-apps.document',
            'application/pdf' => 'application/vnd.google-apps.document',
        ];

        return $conversionMap[$mimeType] ?? null;
    }

    /**
     * Definir texto indexável para tipos de arquivo desconhecidos
     * Conforme documentação: contentHints.indexableText
     */
    private function getIndexableText(string $fileName, string $mimeType): ?string
    {
        // Tipos MIME que o Google Drive já indexa automaticamente
        $autoIndexedTypes = [
            'text/plain',
            'text/html',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'application/json',
            'text/csv',
            'text/xml'
        ];

        // Se o tipo já é indexado automaticamente, não adicionar texto extra
        if (in_array($mimeType, $autoIndexedTypes)) {
            return null;
        }

        // Para tipos não reconhecidos, criar texto indexável baseado no nome do arquivo
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Mapear extensões para descrições legíveis
        $extensionDescriptions = [
            'zip' => 'arquivo compactado',
            'rar' => 'arquivo compactado',
            '7z' => 'arquivo compactado',
            'tar' => 'arquivo compactado',
            'gz' => 'arquivo compactado',
            'bz2' => 'arquivo compactado',

            'mp4' => 'vídeo MP4',
            'avi' => 'vídeo AVI',
            'mkv' => 'vídeo MKV',
            'mov' => 'vídeo MOV',
            'wmv' => 'vídeo WMV',
            'flv' => 'vídeo FLV',

            'mp3' => 'áudio MP3',
            'wav' => 'áudio WAV',
            'flac' => 'áudio FLAC',
            'aac' => 'áudio AAC',
            'ogg' => 'áudio OGG',

            'exe' => 'aplicativo executável',
            'msi' => 'instalador Windows',
            'dmg' => 'imagem disco macOS',
            'iso' => 'imagem disco',
            'bin' => 'arquivo binário',

            'sql' => 'script banco de dados',
            'db' => 'arquivo banco de dados',
            'sqlite' => 'banco de dados SQLite',

            'psd' => 'arquivo Photoshop',
            'ai' => 'arquivo Illustrator',
            'xd' => 'arquivo Adobe XD',
            'fig' => 'arquivo Figma',

            'dwg' => 'desenho CAD',
            'dxf' => 'desenho CAD DXF',

            'ttf' => 'fonte TrueType',
            'otf' => 'fonte OpenType',
            'woff' => 'fonte web',

            'log' => 'arquivo de log',
            'txt' => 'arquivo de texto',
            'md' => 'arquivo Markdown',
            'yml' => 'arquivo YAML',
            'yaml' => 'arquivo YAML',
            'json' => 'arquivo JSON',
            'xml' => 'arquivo XML',
            'css' => 'folha de estilo CSS',
            'js' => 'script JavaScript',
            'php' => 'script PHP',
            'py' => 'script Python',
            'java' => 'arquivo Java',
            'cpp' => 'arquivo C++',
            'c' => 'arquivo C',
            'h' => 'arquivo header',
        ];

        $description = $extensionDescriptions[$extension] ?? 'arquivo ' . strtoupper($extension);

        // Criar texto indexável
        $indexableText = $fileNameWithoutExtension . ' ' . $description . ' ' . $fileName;

        // Limitar tamanho (Google recomenda menos de 200 caracteres)
        if (strlen($indexableText) > 200) {
            $indexableText = substr($indexableText, 0, 197) . '...';
        }

        return $indexableText;
    }
}
