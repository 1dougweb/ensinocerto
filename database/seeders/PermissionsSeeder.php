<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar papéis básicos
        $adminRole = Role::create([
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Acesso completo ao sistema',
        ]);

        $vendedorRole = Role::create([
            'name' => 'Vendedor',
            'slug' => 'vendedor',
            'description' => 'Acesso às funcionalidades de vendas',
        ]);

        $colaboradorRole = Role::create([
            'name' => 'Colaborador',
            'slug' => 'colaborador',
            'description' => 'Acesso básico ao sistema',
        ]);

        $midiaRole = Role::create([
            'name' => 'Mídia',
            'slug' => 'midia',
            'description' => 'Acesso às funcionalidades de mídia',
        ]);

        $parceiroRole = Role::create([
            'name' => 'Parceiro',
            'slug' => 'parceiro',
            'description' => 'Acesso às funcionalidades de parceiro',
        ]);

        // Definir módulos do sistema
        $modules = [
            'dashboard' => 'Dashboard',
            'usuarios' => 'Usuários',
            'inscricoes' => 'Inscrições',
            'matriculas' => 'Matrículas',
            'financeiro' => 'Financeiro',
            'contratos' => 'Contratos',
            'parceiros' => 'Parceiros',
            'configuracoes' => 'Configurações',
            'relatorios' => 'Relatórios',
            'kanban' => 'Kanban',
            'arquivos' => 'Arquivos',
        ];

        // Criar permissões para cada módulo
        foreach ($modules as $moduleSlug => $moduleName) {
            // Permissão de visualização
            Permission::create([
                'name' => "Ver {$moduleName}",
                'slug' => "view_{$moduleSlug}",
                'module' => $moduleSlug,
                'description' => "Permite visualizar o módulo {$moduleName}",
            ]);

            // Permissão de criação
            Permission::create([
                'name' => "Criar {$moduleName}",
                'slug' => "create_{$moduleSlug}",
                'module' => $moduleSlug,
                'description' => "Permite criar no módulo {$moduleName}",
            ]);

            // Permissão de edição
            Permission::create([
                'name' => "Editar {$moduleName}",
                'slug' => "edit_{$moduleSlug}",
                'module' => $moduleSlug,
                'description' => "Permite editar no módulo {$moduleName}",
            ]);

            // Permissão de exclusão
            Permission::create([
                'name' => "Excluir {$moduleName}",
                'slug' => "delete_{$moduleSlug}",
                'module' => $moduleSlug,
                'description' => "Permite excluir no módulo {$moduleName}",
            ]);
        }

        // Atribuir todas as permissões ao papel de administrador
        $adminRole->permissions()->attach(Permission::all());

        // Atribuir permissões específicas ao papel de vendedor
        $vendedorPermissions = Permission::whereIn('module', [
            'dashboard', 'inscricoes', 'matriculas', 'contratos', 'parceiros', 'kanban', 'arquivos'
        ])->get();
        $vendedorRole->permissions()->attach($vendedorPermissions);

        // Atribuir permissões específicas ao papel de mídia
        $midiaPermissions = Permission::whereIn('module', [
            'dashboard', 'inscricoes', 'relatorios'
        ])->get();
        $midiaRole->permissions()->attach($midiaPermissions);

        // Atribuir permissões específicas ao papel de colaborador
        $colaboradorPermissions = Permission::whereIn('slug', [
            'view_dashboard', 'view_inscricoes', 'view_matriculas'
        ])->get();
        $colaboradorRole->permissions()->attach($colaboradorPermissions);

        // Atribuir permissões específicas ao papel de parceiro
        $parceiroPermissions = Permission::whereIn('slug', [
            'view_dashboard', 'view_contratos', 'view_arquivos'
        ])->get();
        $parceiroRole->permissions()->attach($parceiroPermissions);

        // Atribuir papéis aos usuários existentes com base no tipo_usuario
        $users = User::all();
        foreach ($users as $user) {
            switch ($user->tipo_usuario) {
                case 'admin':
                    $user->roles()->attach($adminRole);
                    break;
                case 'vendedor':
                    $user->roles()->attach($vendedorRole);
                    break;
                case 'colaborador':
                    $user->roles()->attach($colaboradorRole);
                    break;
                case 'midia':
                    $user->roles()->attach($midiaRole);
                    break;
                case 'parceiro':
                    $user->roles()->attach($parceiroRole);
                    break;
            }
        }
    }
} 