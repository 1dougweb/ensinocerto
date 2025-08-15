<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar apenas configurações específicas para sandbox do Mercado Pago
        // Os campos de produção já existem (mercadopago_access_token e mercadopago_public_key)
        DB::table('system_settings')->insert([
            [
                'key' => 'mercadopago_sandbox_access_token',
                'value' => '',
                'type' => 'string',
                'category' => 'payments',
                'description' => 'Access Token do Mercado Pago para ambiente sandbox',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'mercadopago_sandbox_public_key',
                'value' => '',
                'type' => 'string',
                'category' => 'payments',
                'description' => 'Public Key do Mercado Pago para ambiente sandbox',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover apenas configurações sandbox específicas
        DB::table('system_settings')->whereIn('key', [
            'mercadopago_sandbox_access_token',
            'mercadopago_sandbox_public_key'
        ])->delete();
    }
}; 