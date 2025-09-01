<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoogleDriveFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'file_id',
        'mime_type',
        'web_view_link',
        'web_content_link',
        'thumbnail_link',
        'size',
        'path',
        'created_by',
        'parent_id',
        'is_folder',
        'is_starred',
        'is_trashed',
        'local_path',
        'is_local'
    ];

    protected $casts = [
        'size' => 'integer',
        'is_folder' => 'boolean',
        'is_starred' => 'boolean',
        'is_trashed' => 'boolean',
        'is_local' => 'boolean'
    ];

    // Relacionamento com o usuário que criou o arquivo
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relacionamento com a pasta pai
    public function parent()
    {
        return $this->belongsTo(GoogleDriveFile::class, 'parent_id');
    }

    // Relacionamento com os arquivos/pastas filhos
    public function children()
    {
        return $this->hasMany(GoogleDriveFile::class, 'parent_id');
    }

    // Escopo para arquivos não excluídos
    public function scopeNotTrashed($query)
    {
        return $query->where('is_trashed', false);
    }

    // Escopo para pastas
    public function scopeFolders($query)
    {
        return $query->where('is_folder', true);
    }

    // Escopo para arquivos
    public function scopeFiles($query)
    {
        return $query->where('is_folder', false);
    }

    // Escopo para arquivos favoritos
    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    // Retorna o tamanho formatado
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Retorna todos os ancestrais (caminho até a raiz)
    public function getAncestorsAttribute()
    {
        $ancestors = collect([]);
        $current = $this->parent;
        $visited = collect([$this->id]); // Evitar loops infinitos
        
        while ($current && !$visited->contains($current->id)) {
            $ancestors->push($current);
            $visited->push($current->id);
            $current = $current->parent;
        }
        
        return $ancestors->reverse();
    }

    // Retorna o ícone baseado no mime type
    public function getIconAttribute()
    {
        if ($this->is_folder) {
            return 'fas fa-folder';
        }

        $mimeTypeIcons = [
            'image/' => 'fas fa-image',
            'video/' => 'fas fa-video',
            'audio/' => 'fas fa-music',
            'application/pdf' => 'fas fa-file-pdf',
            'application/msword' => 'fas fa-file-word',
            'application/vnd.ms-excel' => 'fas fa-file-excel',
            'application/vnd.ms-powerpoint' => 'fas fa-file-powerpoint',
            'text/' => 'fas fa-file-alt',
            'application/zip' => 'fas fa-file-archive'
        ];

        foreach ($mimeTypeIcons as $type => $icon) {
            if (str_starts_with($this->mime_type, $type)) {
                return $icon;
            }
        }

        return 'fas fa-file';
    }

    /**
     * Método helper para criar ou atualizar arquivos do Google Drive de forma segura
     * Evita duplicatas verificando se o file_id já existe (incluindo soft deleted)
     */
    public static function createOrUpdateFromGoogleDrive(array $attributes): self
    {
        // Verificar se file_id foi fornecido
        if (!isset($attributes['file_id'])) {
            throw new \InvalidArgumentException('file_id é obrigatório para criar arquivo do Google Drive');
        }

        // Primeiro, verificar se já existe (incluindo soft deleted)
        try {
            $existingFile = self::withTrashed()->where('file_id', $attributes['file_id'])->first();
        } catch (\Exception $e) {
            \Log::warning('GoogleDriveFile::createOrUpdateFromGoogleDrive - Método withTrashed não disponível, verificando apenas registros ativos', [
                'file_id' => $attributes['file_id'],
                'error' => $e->getMessage()
            ]);
            $existingFile = self::where('file_id', $attributes['file_id'])->first();
        }

        if ($existingFile) {
            // Se está soft deleted, restaurar
            if ($existingFile->trashed()) {
                try {
                    $existingFile->restore();
                    \Log::info('GoogleDriveFile::createOrUpdateFromGoogleDrive - Restaurado registro soft deleted', [
                        'file_id' => $attributes['file_id'],
                        'name' => $attributes['name'] ?? 'N/A'
                    ]);
                } catch (\Exception $e) {
                    \Log::error('GoogleDriveFile::createOrUpdateFromGoogleDrive - Erro ao restaurar registro soft deleted', [
                        'file_id' => $attributes['file_id'],
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

            // Atualizar os dados
            try {
                $updateData = array_merge($attributes, [
                    'is_trashed' => $attributes['is_trashed'] ?? false,
                    'is_folder' => $attributes['is_folder'] ?? false,
                    'is_starred' => $attributes['is_starred'] ?? false,
                    'is_local' => $attributes['is_local'] ?? false,
                ]);

                $existingFile->update($updateData);
            } catch (\Exception $e) {
                \Log::error('GoogleDriveFile::createOrUpdateFromGoogleDrive - Erro ao atualizar registro', [
                    'file_id' => $attributes['file_id'],
                    'error' => $e->getMessage(),
                    'attributes' => $attributes
                ]);
                throw $e;
            }

            \Log::info('GoogleDriveFile::createOrUpdateFromGoogleDrive - Atualizado registro existente', [
                'file_id' => $attributes['file_id'],
                'name' => $attributes['name'] ?? 'N/A'
            ]);

            return $existingFile;
        }

        // Se não existe, criar novo
        try {
            $createData = array_merge($attributes, [
                'is_trashed' => $attributes['is_trashed'] ?? false,
                'is_folder' => $attributes['is_folder'] ?? false,
                'is_starred' => $attributes['is_starred'] ?? false,
                'is_local' => $attributes['is_local'] ?? false,
                'created_by' => $attributes['created_by'] ?? 1,
                'name' => $attributes['name'] ?? 'Arquivo sem nome',
                'mime_type' => $attributes['mime_type'] ?? 'application/octet-stream',
            ]);

            $file = self::create($createData);

            \Log::info('GoogleDriveFile::createOrUpdateFromGoogleDrive - Criado novo registro', [
                'file_id' => $attributes['file_id'],
                'name' => $attributes['name'] ?? 'N/A',
                'id' => $file->id
            ]);

            return $file;
        } catch (\Exception $e) {
            \Log::error('GoogleDriveFile::createOrUpdateFromGoogleDrive - Erro ao criar registro', [
                'file_id' => $attributes['file_id'],
                'error' => $e->getMessage(),
                'attributes' => $attributes
            ]);
            throw $e;
        }
    }
}
