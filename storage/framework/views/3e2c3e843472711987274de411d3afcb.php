<?php $__env->startSection('title', 'Editar Pagamento'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <h3 class="mt-4">
        <i class="fas fa-edit me-2"></i>
        Editar Pagamento #<?php echo e($payment->id); ?>

    </h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.payments.index')); ?>">Pagamentos</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.payments.show', $payment)); ?>">Pagamento #<?php echo e($payment->id); ?></a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors && $errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('admin.payments.update', $payment)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="row">
            <!-- Dados do Pagamento -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Dados do Pagamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="matricula_id" class="form-label">Matrícula</label>
                                <select class="form-select <?php $__errorArgs = ['matricula_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="matricula_id" 
                                        name="matricula_id" 
                                        disabled>
                                    <option value="<?php echo e($payment->matricula->id); ?>" selected>
                                        <?php echo e($payment->matricula->nome_completo); ?> - <?php echo e($payment->matricula->cpf); ?>

                                    </option>
                                </select>
                                <input type="hidden" name="matricula_id" value="<?php echo e($payment->matricula->id); ?>">
                                <small class="form-text text-muted">
                                    A matrícula não pode ser alterada após a criação do pagamento.
                                </small>
                                <?php $__errorArgs = ['matricula_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label for="valor" class="form-label">Valor</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <?php
                                        $gateway = $payment->matricula->payment_gateway ?? 'mercado_pago';
                                        $valorPadrao = $payment->valor;
                                        
                                        // Para outros bancos, usar valores da matrícula
                                        if ($gateway !== 'mercado_pago') {
                                            if ($payment->matricula->valor_pago && $payment->matricula->numero_parcelas > 1) {
                                                $valorPadrao = $payment->matricula->valor_pago / $payment->matricula->numero_parcelas;
                                            } elseif ($payment->matricula->valor_pago) {
                                                $valorPadrao = $payment->matricula->valor_pago;
                                            } elseif ($payment->matricula->numero_parcelas > 1) {
                                                $valorPadrao = $payment->matricula->valor_total_curso / $payment->matricula->numero_parcelas;
                                            } else {
                                                $valorPadrao = $payment->matricula->valor_total_curso;
                                            }
                                        }
                                    ?>
                                    <input type="number" 
                                           class="form-control <?php $__errorArgs = ['valor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="valor" 
                                           name="valor" 
                                           value="<?php echo e(old('valor', $valorPadrao)); ?>" 
                                           step="0.01"
                                           min="0.01"
                                           required>
                                    <?php $__errorArgs = ['valor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <?php if($payment->isOverdue() && $payment->status === 'pending'): ?>
                                <div class="alert alert-warning mt-2 p-2 small">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Pagamento vencido há <?php echo e($payment->getFormattedDaysOverdue()); ?>.<br>
                                    Valor com juros: <strong><?php echo e($payment->getFormattedValorAtualizado()); ?></strong><br>
                                    <small>(+ <?php echo e($payment->getFormattedValorJurosMora()); ?> de juros)</small>
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-sm btn-warning" onclick="aplicarJuros()">
                                            Aplicar Juros
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-3">
                                <label for="payment_gateway" class="form-label">Gateway de Pagamento</label>
                                <div class="form-control-plaintext">
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
                                </div>
                                <small class="form-text text-muted">
                                    Gateway definido na matrícula
                                </small>
                            </div>

                            <div class="col-md-3">
                                <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                                <?php if(($payment->matricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago'): ?>
                                    <select class="form-select <?php $__errorArgs = ['forma_pagamento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="forma_pagamento" 
                                            name="forma_pagamento" 
                                            required>
                                        <option value="">Selecione</option>
                                        <option value="pix" <?php echo e(old('forma_pagamento', $payment->forma_pagamento) == 'pix' ? 'selected' : ''); ?>>PIX</option>
                                        <option value="cartao_credito" <?php echo e(old('forma_pagamento', $payment->forma_pagamento) == 'cartao_credito' ? 'selected' : ''); ?>>Cartão de Crédito</option>
                                        <option value="boleto" <?php echo e(old('forma_pagamento', $payment->forma_pagamento) == 'boleto' ? 'selected' : ''); ?>>Boleto</option>
                                    </select>
                                <?php else: ?>
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-secondary">Pagamento Manual</span>
                                    </div>
                                    <input type="hidden" name="forma_pagamento" value="<?php echo e($payment->forma_pagamento ?? 'boleto'); ?>">
                                <?php endif; ?>
                                <?php $__errorArgs = ['forma_pagamento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-4">
                                <label for="data_vencimento" class="form-label">Data de Vencimento</label>
                                <input type="date" 
                                       class="form-control <?php $__errorArgs = ['data_vencimento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="data_vencimento" 
                                       name="data_vencimento" 
                                       value="<?php echo e(old('data_vencimento', $payment->data_vencimento->format('Y-m-d'))); ?>" 
                                       required>
                                <?php $__errorArgs = ['data_vencimento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <?php
                                    $isManualGateway = $payment->matricula && 
                                        in_array($payment->matricula->payment_gateway ?? 'mercado_pago', ['asas', 'infiny_pay', 'cora']);
                                ?>
                                
                                <?php if($isManualGateway): ?>
                                    <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="pending" <?php echo e(old('status', $payment->status) == 'pending' ? 'selected' : ''); ?>>🟡 Pendente</option>
                                        <option value="processing" <?php echo e(old('status', $payment->status) == 'processing' ? 'selected' : ''); ?>>🔵 Processando</option>
                                        <option value="paid" <?php echo e(old('status', $payment->status) == 'paid' ? 'selected' : ''); ?>>🟢 Pago</option>
                                        <option value="failed" <?php echo e(old('status', $payment->status) == 'failed' ? 'selected' : ''); ?>>🔴 Falhou</option>
                                        <option value="cancelled" <?php echo e(old('status', $payment->status) == 'cancelled' ? 'selected' : ''); ?>>⚫ Cancelado</option>
                                    </select>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="<?php echo e($payment->status); ?>">
                                    <div class="form-control bg-light">
                                        <?php switch($payment->status):
                                            case ('pending'): ?>
                                                🟡 Pendente
                                                <?php break; ?>
                                            <?php case ('processing'): ?>
                                                🔵 Processando
                                                <?php break; ?>
                                            <?php case ('paid'): ?>
                                                🟢 Pago
                                                <?php break; ?>
                                            <?php case ('failed'): ?>
                                                🔴 Falhou
                                                <?php break; ?>
                                            <?php case ('cancelled'): ?>
                                                ⚫ Cancelado
                                                <?php break; ?>
                                            <?php default: ?>
                                                <?php echo e($payment->status); ?>

                                        <?php endswitch; ?>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-robot me-1"></i>
                                        Status controlado automaticamente pelo Mercado Pago via webhook.
                                    </small>
                                <?php endif; ?>
                                <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações de Parcelamento (oculto para cartão de crédito) -->
            <div class="col-md-12 mb-4" id="parcelamento-section" style="display: <?php echo e(old('forma_pagamento', $payment->forma_pagamento) === 'cartao_credito' ? 'none' : 'block'); ?>;">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Informações de Parcelamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="numero_parcela" class="form-label">Número da Parcela</label>
                                <input type="number" 
                                       class="form-control <?php $__errorArgs = ['numero_parcela'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="numero_parcela" 
                                       name="numero_parcela" 
                                       value="<?php echo e(old('numero_parcela', $payment->numero_parcela)); ?>" 
                                       min="1"
                                       readonly>
                                <?php $__errorArgs = ['numero_parcela'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="form-text text-muted">
                                    Número da parcela não pode ser alterado após a criação.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="total_parcelas" class="form-label">Total de Parcelas</label>
                                <input type="number" 
                                       class="form-control <?php $__errorArgs = ['total_parcelas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="total_parcelas" 
                                       name="total_parcelas" 
                                       value="<?php echo e(old('total_parcelas', $payment->total_parcelas)); ?>" 
                                       min="1"
                                       readonly>
                                <?php $__errorArgs = ['total_parcelas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="form-text text-muted">
                                    Total de parcelas não pode ser alterado após a criação.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações do Gateway -->
            <?php if(($payment->matricula->payment_gateway ?? 'mercado_pago') !== 'mercado_pago'): ?>
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bank me-2"></i>
                                Informações do <?php echo e($payment->matricula->payment_gateway === 'asas' ? 'Banco Asas' : 
                                    ($payment->matricula->payment_gateway === 'infiny_pay' ? 'Infiny Pay' : 
                                    ($payment->matricula->payment_gateway === 'cora' ? 'Banco Cora' : 'Banco'))); ?>

                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if($payment->matricula->bank_info): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Informações:</strong> <?php echo e($payment->matricula->bank_info); ?>

                                </div>
                            <?php endif; ?>
                            
                            <?php if($payment->matricula->valor_pago): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Valor Pago:</strong></p>
                                        <p class="h5 text-success">R$ <?php echo e(number_format($payment->matricula->valor_pago, 2, ',', '.')); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Valor Total:</strong></p>
                                        <p class="h5">R$ <?php echo e(number_format($payment->matricula->valor_total_curso, 2, ',', '.')); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Informações do Mercado Pago (apenas para gateway Mercado Pago) -->
            <?php if($payment->mercadopago_id && ($payment->matricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago'): ?>
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>
                                Informações do Mercado Pago
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">ID Mercado Pago</label>
                                    <p class="mb-0"><code><?php echo e($payment->mercadopago_id); ?></code></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Status Mercado Pago</label>
                                    <p class="mb-0">
                                        <span class="badge bg-light text-dark"><?php echo e($payment->mercadopago_status ?? 'N/A'); ?></span>
                                    </p>
                                </div>
                                <?php if($payment->mercadopago_updated_at): ?>
                                    <div class="col-md-12">
                                        <label class="form-label text-muted">Última atualização</label>
                                        <p class="mb-0"><?php echo e($payment->mercadopago_updated_at->format('d/m/Y H:i')); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>
                Salvar Alterações
            </button>
            <a href="<?php echo e(route('admin.payments.show', $payment)); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-times me-2"></i>
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Controlar visibilidade da seção de parcelamento
    const formaPagamentoSelect = document.getElementById('forma_pagamento');
    const parcelamentoSection = document.getElementById('parcelamento-section');
    
    function toggleParcelamento() {
        const formaPagamento = formaPagamentoSelect.value;
        if (formaPagamento === 'cartao_credito') {
            parcelamentoSection.style.display = 'none';
        } else {
            parcelamentoSection.style.display = 'block';
        }
    }
    
    // Aplicar lógica no carregamento e quando mudar
    toggleParcelamento();
    formaPagamentoSelect.addEventListener('change', toggleParcelamento);
    
    // Atualizar data de pagamento automaticamente quando status muda para 'paid'
    const statusSelect = document.getElementById('status');
    
    statusSelect.addEventListener('change', function() {
        if (this.value === 'paid') {
            // Adicionar campo oculto para data de pagamento
            let dataPagamentoInput = document.getElementById('data_pagamento_hidden');
            if (!dataPagamentoInput) {
                dataPagamentoInput = document.createElement('input');
                dataPagamentoInput.type = 'hidden';
                dataPagamentoInput.name = 'data_pagamento';
                dataPagamentoInput.id = 'data_pagamento_hidden';
                this.form.appendChild(dataPagamentoInput);
            }
            dataPagamentoInput.value = new Date().toISOString().slice(0, 19).replace('T', ' ');
        }
    });
});

function aplicarJuros() {
    // Obter o valor atualizado com juros
    const valorAtualizado = <?php echo e($payment->getValorAtualizado()); ?>;
    
    // Atualizar o campo de valor
    document.getElementById('valor').value = valorAtualizado.toFixed(2);
    
    // Exibir alerta
    alert('Valor atualizado com juros aplicado: R$ ' + valorAtualizado.toFixed(2).replace('.', ','));
}
</script>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/douglas/Downloads/ec-complete-backup-20250728_105142/ec-complete-backup-20250813_144041/resources/views/admin/payments/edit.blade.php ENDPATH**/ ?>