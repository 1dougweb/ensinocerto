<?php $__env->startSection('title', 'Detalhes do Pagamento'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-credit-card me-2"></i>
                Detalhes do Pagamento #<?php echo e($payment->id); ?>

            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.payments.index')); ?>">Pagamentos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?php echo e(route('admin.payments.edit', $payment)); ?>" class="btn btn-outline-warning me-2">
                <i class="fas fa-edit me-1"></i>
                Editar
            </a>
            <a href="<?php echo e(route('admin.payments.index')); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Informações do Pagamento -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Informações do Pagamento
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">ID do Pagamento</label>
                            <p class="mb-0"><strong>#<?php echo e($payment->id); ?></strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <p class="mb-0">
                                <?php switch($payment->status):
                                    case ('pending'): ?>
                                        <span class="badge bg-warning fs-6">🟡 Pendente</span>
                                        <?php break; ?>
                                    <?php case ('processing'): ?>
                                        <span class="badge bg-info fs-6">🔄 Processando</span>
                                        <?php break; ?>
                                    <?php case ('paid'): ?>
                                        <span class="badge bg-success fs-6">🟢 Pago</span>
                                        <?php break; ?>
                                    <?php case ('failed'): ?>
                                        <span class="badge bg-danger fs-6">🔴 Falhou</span>
                                        <?php break; ?>
                                    <?php case ('cancelled'): ?>
                                        <span class="badge bg-dark fs-6">⚫ Cancelado</span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge bg-light text-dark fs-6"><?php echo e($payment->status); ?></span>
                                <?php endswitch; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Valor</label>
                            <?php
                                $gateway = $payment->matricula->payment_gateway ?? 'mercado_pago';
                                $valorExibir = $payment->valor;
                                
                                // Para outros bancos, usar valores da matrícula
                                if ($gateway !== 'mercado_pago') {
                                    if ($payment->matricula->valor_pago && $payment->matricula->numero_parcelas > 1) {
                                        $valorExibir = $payment->matricula->valor_pago / $payment->matricula->numero_parcelas;
                                    } elseif ($payment->matricula->valor_pago) {
                                        $valorExibir = $payment->matricula->valor_pago;
                                    } elseif ($payment->matricula->numero_parcelas > 1) {
                                        $valorExibir = $payment->matricula->valor_total_curso / $payment->matricula->numero_parcelas;
                                    } else {
                                        $valorExibir = $payment->matricula->valor_total_curso;
                                    }
                                }
                            ?>
                            <p class="mb-0"><strong class="text-success">R$ <?php echo e(number_format($valorExibir, 2, ',', '.')); ?></strong></p>
                        </div>
                        <?php if($payment->isOverdue() && $payment->status === 'pending'): ?>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Valor Atualizado (com juros)</label>
                            <p class="mb-0">
                                <strong class="text-danger"><?php echo e($payment->getFormattedValorAtualizado()); ?></strong>
                                <small class="text-muted ms-2">(+ <?php echo e($payment->getFormattedValorJurosMora()); ?> de juros)</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Dias em Atraso</label>
                            <p class="mb-0"><span class="badge bg-danger"><?php echo e($payment->getFormattedDaysOverdue()); ?></span></p>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Gateway de Pagamento</label>
                            <p class="mb-0">
                                <?php switch($payment->matricula->payment_gateway ?? 'mercado_pago'):
                                    case ('mercado_pago'): ?>
                                        <span class="badge bg-primary">Mercado Pago</span>
                                        <?php break; ?>
                                    <?php case ('asas'): ?>
                                        <span class="badge bg-info">Banco Asas</span>
                                        <?php break; ?>
                                    <?php case ('infiny_pay'): ?>
                                        <span class="badge bg-warning">Infiny Pay</span>
                                        <?php break; ?>
                                    <?php case ('cora'): ?>
                                        <span class="badge bg-success">Banco Cora</span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge bg-secondary"><?php echo e($payment->matricula->payment_gateway); ?></span>
                                <?php endswitch; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Forma de Pagamento</label>
                            <p class="mb-0">
                                <?php if(($payment->matricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago'): ?>
                                    <?php switch($payment->forma_pagamento):
                                        case ('pix'): ?>
                                            <span class="badge bg-info">PIX</span>
                                            <?php break; ?>
                                        <?php case ('cartao_credito'): ?>
                                            <span class="badge bg-primary">Cartão de Crédito</span>
                                            <?php break; ?>
                                        <?php case ('boleto'): ?>
                                            <span class="badge bg-secondary">Boleto</span>
                                            <?php break; ?>
                                        <?php default: ?>
                                            <span class="badge bg-light text-dark"><?php echo e($payment->forma_pagamento); ?></span>
                                    <?php endswitch; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pagamento Manual</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Data de Vencimento</label>
                            <p class="mb-0">
                                <?php echo e($payment->data_vencimento->format('d/m/Y')); ?>

                                <?php if($payment->data_vencimento->isPast() && in_array($payment->status, ['pending', 'processing'])): ?>
                                    <span class="badge bg-danger ms-2">Vencido</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Data de Pagamento</label>
                            <p class="mb-0">
                                <?php if($payment->data_pagamento): ?>
                                    <?php echo e($payment->data_pagamento->format('d/m/Y H:i')); ?>

                                <?php else: ?>
                                    <span class="text-muted">Não pago</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Parcela</label>
                            <p class="mb-0">
                                <?php if($payment->total_parcelas > 1): ?>
                                    <?php echo e($payment->numero_parcela); ?>/<?php echo e($payment->total_parcelas); ?>

                                <?php else: ?>
                                    À vista
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Criado em</label>
                            <p class="mb-0"><?php echo e($payment->created_at->format('d/m/Y H:i')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações da Matrícula -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        Informações da Matrícula
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if($payment->matricula): ?>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Nome Completo</label>
                            <p class="mb-0"><strong><?php echo e($payment->matricula->nome_completo); ?></strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">CPF</label>
                            <p class="mb-0"><?php echo e($payment->matricula->cpf); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">E-mail</label>
                            <p class="mb-0"><?php echo e($payment->matricula->email); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Telefone</label>
                                <p class="mb-0"><?php echo e($payment->matricula->telefone_celular ?? 'Não informado'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Modalidade</label>
                            <p class="mb-0"><?php echo e($payment->matricula->modalidade ?? 'Não informado'); ?></p>
                        </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Curso</label>
                                <p class="mb-0"><?php echo e($payment->matricula->curso ?? 'Não informado'); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Matrícula não encontrada</strong><br>
                                    Este pagamento está vinculado à matrícula ID: <?php echo e($payment->matricula_id); ?>, mas ela não foi encontrada no sistema.
                                    Isso pode ter ocorrido se a matrícula foi excluída.
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status da Matrícula</label>
                            <p class="mb-0">
                                <?php switch($payment->matricula->status):
                                    case ('pre_matricula'): ?>
                                        <span class="badge bg-warning">🟡 Pré-Matrícula</span>
                                        <?php break; ?>
                                    <?php case ('matricula_confirmada'): ?>
                                        <span class="badge bg-success">🟢 Matrícula Confirmada</span>
                                        <?php break; ?>
                                    <?php case ('cancelada'): ?>
                                        <span class="badge bg-danger">🔴 Cancelada</span>
                                        <?php break; ?>
                                    <?php case ('trancada'): ?>
                                        <span class="badge bg-dark">⚫ Trancada</span>
                                        <?php break; ?>
                                    <?php case ('concluida'): ?>
                                        <span class="badge bg-info">⭐ Concluída</span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge bg-light text-dark"><?php echo e($payment->matricula->status); ?></span>
                                <?php endswitch; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel Lateral -->
        <div class="col-md-4">
            <!-- Ações Rápidas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('admin.payments.edit', $payment)); ?>" class="btn btn-outline-warning">
                            <i class="fas fa-edit me-2"></i>
                            Editar Pagamento
                        </a>
                        <?php if($payment->status === 'pending'): ?>
                            <?php
                                $isManualGateway = $payment->matricula && 
                                    in_array($payment->matricula->payment_gateway ?? 'mercado_pago', ['asas', 'infiny_pay', 'cora']);
                            ?>
                            
                            <?php if($isManualGateway): ?>
                                <form method="POST" action="<?php echo e(route('admin.payments.update', $payment)); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <input type="hidden" name="status" value="paid">
                                    <input type="hidden" name="valor" value="<?php echo e($payment->valor); ?>">
                                    <input type="hidden" name="forma_pagamento" value="<?php echo e($payment->forma_pagamento); ?>">
                                    <input type="hidden" name="data_vencimento" value="<?php echo e($payment->data_vencimento->format('Y-m-d')); ?>">
                                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Marcar como pago?')">
                                        <i class="fas fa-check me-2"></i>
                                        Marcar como Pago
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-robot me-2"></i>
                                    <strong>Mercado Pago</strong><br>
                                    O status será atualizado automaticamente via webhook.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if($payment->matricula): ?>
                        <a href="<?php echo e(route('admin.matriculas.show', $payment->matricula)); ?>" class="btn btn-outline-info">
                            <i class="fas fa-user-graduate me-2"></i>
                            Ver Matrícula
                        </a>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary" disabled>
                                <i class="fas fa-user-graduate me-2"></i>
                                Matrícula não encontrada
                            </button>
                        <?php endif; ?>
                        
                        <?php if($payment->hasBoleto()): ?>
                            <a href="<?php echo e(route('admin.payments.download-boleto', $payment)); ?>" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-download me-2"></i>
                                Download Boleto
                            </a>
                        <?php endif; ?>
                        
                        <?php if($payment->hasPixCode()): ?>
                            <button type="button" class="btn btn-outline-info w-100 mb-2" onclick="showPixCode('<?php echo e($payment->codigo_pix); ?>')">
                                <i class="fas fa-qrcode me-2"></i>
                                Ver Código PIX
                            </button>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?php echo e(route('admin.payments.resend-notifications', $payment)); ?>" class="d-inline mt-2">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-primary w-100" onclick="return confirm('Reenviar notificações de pagamento?')">
                                <i class="fas fa-paper-plane me-2"></i>
                                Reenviar Notificações
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Informações do Gateway -->
            <?php if(($payment->matricula->payment_gateway ?? 'mercado_pago') !== 'mercado_pago'): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bank me-2"></i>
                            <?php echo e($payment->matricula->payment_gateway === 'asas' ? 'Banco Asas' : 
                                ($payment->matricula->payment_gateway === 'infiny_pay' ? 'Infiny Pay' : 
                                ($payment->matricula->payment_gateway === 'cora' ? 'Banco Cora' : 'Banco'))); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if($payment->matricula->bank_info): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Informações do Banco</label>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <?php echo e($payment->matricula->bank_info); ?>

                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($payment->matricula->valor_pago): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Valor Pago</label>
                                    <p class="mb-0"><strong class="text-success">R$ <?php echo e(number_format($payment->matricula->valor_pago, 2, ',', '.')); ?></strong></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Valor Total</label>
                                    <p class="mb-0"><strong>R$ <?php echo e(number_format($payment->matricula->valor_total_curso, 2, ',', '.')); ?></strong></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Informações do Mercado Pago (apenas para gateway Mercado Pago) -->
            <?php if($payment->mercadopago_id && ($payment->matricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago'): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Mercado Pago
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">ID Mercado Pago</label>
                            <p class="mb-0"><code><?php echo e($payment->mercadopago_id); ?></code></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Status Mercado Pago</label>
                            <p class="mb-0">
                                <span class="badge bg-light text-dark"><?php echo e($payment->mercadopago_status ?? 'N/A'); ?></span>
                            </p>
                        </div>
                        <?php if($payment->mercadopago_updated_at): ?>
                            <div class="mb-0">
                                <label class="form-label text-muted">Última atualização</label>
                                <p class="mb-0"><?php echo e($payment->mercadopago_updated_at->format('d/m/Y H:i')); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function showPixCode(pixCode) {
        document.getElementById('pixCodeContent').textContent = pixCode;
        var modal = new bootstrap.Modal(document.getElementById('pixCodeModal'));
        modal.show();
    }

    function copyPixCode() {
        var pixCode = document.getElementById('pixCodeContent').textContent;
        navigator.clipboard.writeText(pixCode).then(function() {
            var btn = document.getElementById('copyPixBtn');
            var originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        });
    }
</script>

<!-- Modal PIX -->
<div class="modal fade" id="pixCodeModal" tabindex="-1" aria-labelledby="pixCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pixCodeModalLabel">
                    <i class="fas fa-qrcode me-2"></i>
                    Código PIX
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Copie o código PIX abaixo para realizar o pagamento:</p>
                <div class="bg-light p-3 rounded border">
                    <code id="pixCodeContent" class="text-break"></code>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="copyPixBtn" onclick="copyPixCode()">
                    <i class="fas fa-copy me-1"></i>
                    Copiar Código
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/douglas/Downloads/ec-complete-backup-20250728_105142/ec-complete-backup-20250813_144041/resources/views/admin/payments/show.blade.php ENDPATH**/ ?>