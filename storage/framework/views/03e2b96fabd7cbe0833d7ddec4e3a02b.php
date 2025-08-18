<?php $__env->startSection('title', 'Novo Pagamento'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <h3 class="mt-4">
        <i class="fas fa-credit-card me-2"></i>
        Novo Pagamento
    </h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.payments.index')); ?>">Pagamentos</a></li>
        <li class="breadcrumb-item active">Novo Pagamento</li>
    </ol>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('mercadopago_success')): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo e(session('mercadopago_success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('mercadopago_error')): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Aviso:</strong> <?php echo e(session('mercadopago_error')); ?>

            <br><small>O pagamento foi criado no sistema, mas não foi processado pelo Mercado Pago.</small>
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

    <form method="POST" action="<?php echo e(route('admin.payments.store')); ?>">
        <?php echo csrf_field(); ?>

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
                                        required>
                                    <option value="">Selecione uma matrícula</option>
                                    <?php $__currentLoopData = $matriculas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $matricula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($matricula->id); ?>" 
                                                <?php echo e(old('matricula_id', $selectedMatricula?->id) == $matricula->id ? 'selected' : ''); ?>>
                                            <?php echo e($matricula->nome_completo); ?> - <?php echo e($matricula->cpf); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
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
                                <?php if($selectedMatricula): ?>
                                    <small class="form-text text-success">
                                        <i class="fas fa-info-circle"></i>
                                        Matrícula pré-selecionada: <?php echo e($selectedMatricula->nome_completo); ?>

                                    </small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-3">
                                <label for="valor" class="form-label">Valor</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
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
                                           value="<?php echo e(old('valor')); ?>" 
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
                            </div>

                            <div class="col-md-3" id="gateway-info-section">
                                <label for="payment_gateway" class="form-label">Gateway de Pagamento</label>
                                <div class="form-control-plaintext" id="gateway-display">
                                    <?php if($selectedMatricula): ?>
                                        <?php switch($selectedMatricula->payment_gateway ?? 'mercado_pago'):
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
                                                <span class="badge bg-secondary"><?php echo e($selectedMatricula->payment_gateway); ?></span>
                                        <?php endswitch; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Selecione uma matrícula</span>
                                    <?php endif; ?>
                                </div>
                                <small class="form-text text-muted">
                                    Gateway definido na matrícula
                                </small>
                            </div>

                            <div class="col-md-3" id="forma-pagamento-section">
                                <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                                <?php if($selectedMatricula && ($selectedMatricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago'): ?>
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
                                        <option value="">Selecione a forma de pagamento</option>
                                        <option value="pix" <?php echo e(old('forma_pagamento') == 'pix' ? 'selected' : ''); ?>>PIX</option>
                                        <option value="cartao_credito" <?php echo e(old('forma_pagamento') == 'cartao_credito' ? 'selected' : ''); ?>>Cartão de Crédito</option>
                                        <option value="boleto" <?php echo e(old('forma_pagamento') == 'boleto' ? 'selected' : ''); ?>>Boleto</option>
                                    </select>
                                <?php elseif($selectedMatricula): ?>
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-secondary">Pagamento Manual</span>
                                    </div>
                                    <input type="hidden" name="forma_pagamento" value="boleto">
                                <?php else: ?>
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
                                        <option value="">Selecione a forma de pagamento</option>
                                        <option value="pix" <?php echo e(old('forma_pagamento') == 'pix' ? 'selected' : ''); ?>>PIX</option>
                                        <option value="cartao_credito" <?php echo e(old('forma_pagamento') == 'cartao_credito' ? 'selected' : ''); ?>>Cartão de Crédito</option>
                                        <option value="boleto" <?php echo e(old('forma_pagamento') == 'boleto' ? 'selected' : ''); ?>>Boleto</option>
                                    </select>
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
                                       value="<?php echo e(old('data_vencimento')); ?>" 
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

                            <div class="col-md-8">
                                <label for="observacoes" class="form-label">Observações</label>
                                <textarea class="form-control <?php $__errorArgs = ['observacoes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                          id="observacoes" 
                                          name="observacoes" 
                                          rows="2" 
                                          placeholder="Observações sobre o pagamento..."><?php echo e(old('observacoes')); ?></textarea>
                                <?php $__errorArgs = ['observacoes'];
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
            <div class="col-md-12 mb-4" id="parcelamento-section" style="display: block;">
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
                                       value="<?php echo e(old('numero_parcela', 1)); ?>" 
                                       min="1">
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
                                    Número da parcela atual (ex: 1 para primeira parcela)
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
                                       value="<?php echo e(old('total_parcelas', 1)); ?>" 
                                       min="1">
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
                                    Total de parcelas do pagamento (ex: 12 para parcelamento em 12x)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações do Gateway (apenas para outros bancos) -->
            <?php if($selectedMatricula && ($selectedMatricula->payment_gateway ?? 'mercado_pago') !== 'mercado_pago'): ?>
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bank me-2"></i>
                                Informações do <?php echo e($selectedMatricula->payment_gateway === 'asas' ? 'Banco Asas' : 
                                    ($selectedMatricula->payment_gateway === 'infiny_pay' ? 'Infiny Pay' : 
                                    ($selectedMatricula->payment_gateway === 'cora' ? 'Banco Cora' : 'Banco'))); ?>

                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if($selectedMatricula->bank_info): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Informações:</strong> <?php echo e($selectedMatricula->bank_info); ?>

                                </div>
                            <?php endif; ?>
                            
                            <?php if($selectedMatricula->valor_pago): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Valor Pago:</strong></p>
                                        <p class="h5 text-success">R$ <?php echo e(number_format($selectedMatricula->valor_pago, 2, ',', '.')); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Valor Total:</strong></p>
                                        <p class="h5">R$ <?php echo e(number_format($selectedMatricula->valor_total_curso, 2, ',', '.')); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>
                Salvar Pagamento
            </button>
            <a href="<?php echo e(route('admin.payments.index')); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-times me-2"></i>
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos principais
    const matriculaSelect = document.getElementById('matricula_id');
    const formaPagamentoSelect = document.getElementById('forma_pagamento');
    const parcelamentoSection = document.getElementById('parcelamento-section');
    const gatewayDisplay = document.getElementById('gateway-display');
    const formaPagamentoSection = document.getElementById('forma-pagamento-section');
    
    // Dados das matrículas para JavaScript (passado do backend)
    const matriculasData = <?php echo json_encode($matriculas->keyBy('id')->map(function($m) {
        return [
            'payment_gateway' => $m->payment_gateway ?? 'mercado_pago', 'bank_info' => $m->bank_info, 'valor_pago' => $m->valor_pago
        ];
    })) ?>;
    
    function updateGatewayInfo(matriculaId) {
        const matricula = matriculasData[matriculaId];
        
        if (!matricula) {
            gatewayDisplay.innerHTML = '<span class="text-muted">Selecione uma matrícula</span>';
            resetFormaPagamento();
            return;
        }
        
        const gateway = matricula.payment_gateway;
        
        // Atualizar badge do gateway
        let gatewayBadge = '';
        switch(gateway) {
            case 'mercado_pago':
                gatewayBadge = '<span class="badge bg-primary">Mercado Pago</span>';
                break;
            case 'asas':
                gatewayBadge = '<span class="badge bg-info">Banco Asas</span>';
                break;
            case 'infiny_pay':
                gatewayBadge = '<span class="badge bg-warning">Infiny Pay</span>';
                break;
            case 'cora':
                gatewayBadge = '<span class="badge bg-success">Banco Cora</span>';
                break;
            default:
                gatewayBadge = `<span class="badge bg-secondary">${gateway}</span>`;
        }
        
        gatewayDisplay.innerHTML = gatewayBadge;
        
        // Atualizar forma de pagamento baseada no gateway
        updateFormaPagamento(gateway);
    }
    
    function updateFormaPagamento(gateway) {
        if (gateway === 'mercado_pago') {
            // Mercado Pago: mostrar select completo
            formaPagamentoSection.innerHTML = `
                <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                <select class="form-select" id="forma_pagamento" name="forma_pagamento" required>
                    <option value="">Selecione a forma de pagamento</option>
                    <option value="pix">PIX</option>
                    <option value="cartao_credito">Cartão de Crédito</option>
                    <option value="boleto">Boleto</option>
                </select>
            `;
            
            // Re-aplicar listeners para Mercado Pago
            const newFormaPagamentoSelect = document.getElementById('forma_pagamento');
            if (newFormaPagamentoSelect) {
                newFormaPagamentoSelect.addEventListener('change', toggleParcelamento);
                toggleParcelamento();
            }
        } else {
            // Outros bancos: pagamento manual
            formaPagamentoSection.innerHTML = `
                <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                <div class="form-control-plaintext">
                    <span class="badge bg-secondary">Pagamento Manual</span>
                </div>
                <input type="hidden" name="forma_pagamento" value="boleto">
            `;
            
            // Para outros bancos, sempre mostrar parcelamento (é boleto, não cartão)
            parcelamentoSection.style.display = 'block';
        }
    }
    
    function resetFormaPagamento() {
        formaPagamentoSection.innerHTML = `
            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
            <select class="form-select" id="forma_pagamento" name="forma_pagamento" required>
                <option value="">Selecione a forma de pagamento</option>
                <option value="pix">PIX</option>
                <option value="cartao_credito">Cartão de Crédito</option>
                <option value="boleto">Boleto</option>
            </select>
        `;
        
        // Mostrar parcelamento por padrão (será ocultado se cartão de crédito for selecionado)
        parcelamentoSection.style.display = 'block';
        
        const newFormaPagamentoSelect = document.getElementById('forma_pagamento');
        if (newFormaPagamentoSelect) {
            newFormaPagamentoSelect.addEventListener('change', toggleParcelamento);
            toggleParcelamento();
        }
    }
    
    function toggleParcelamento() {
        const formaPagamentoSelect = document.getElementById('forma_pagamento');
        if (!formaPagamentoSelect) return;
        
        const formaPagamento = formaPagamentoSelect.value;
        if (formaPagamento === 'cartao_credito') {
            parcelamentoSection.style.display = 'none';
        } else {
            parcelamentoSection.style.display = 'block';
        }
    }
    
    // Event listeners
    matriculaSelect.addEventListener('change', function() {
        updateGatewayInfo(this.value);
    });
    
    // Inicializar se já tem matrícula selecionada
    if (matriculaSelect.value) {
        updateGatewayInfo(matriculaSelect.value);
    }
    
    // Aplicar lógica no carregamento
    toggleParcelamento();
    if (formaPagamentoSelect) {
        formaPagamentoSelect.addEventListener('change', toggleParcelamento);
    }
    
    // Auto-ajustar campos de parcela quando necessário
    const numeroParcela = document.getElementById('numero_parcela');
    const totalParcelas = document.getElementById('total_parcelas');
    const valorInput = document.getElementById('valor');
    
    // Validar que número da parcela não seja maior que total
    numeroParcela.addEventListener('input', function() {
        const numero = parseInt(this.value);
        const total = parseInt(totalParcelas.value);
        
        if (numero > total) {
            totalParcelas.value = numero;
        }
    });
    
    totalParcelas.addEventListener('input', function() {
        const total = parseInt(this.value);
        const numero = parseInt(numeroParcela.value);
        
        if (numero > total) {
            numeroParcela.value = total;
        }
    });
    
    // Validação de valor mínimo para boletos
    function validarValorMinimo() {
        const valor = parseFloat(valorInput.value);
        const formaPagamento = formaPagamentoSelect.value;
        
        if (formaPagamento === 'boleto' && valor > 0 && valor < 3.00) {
            valorInput.classList.add('is-invalid');
            
            // Remover feedback anterior
            const existingFeedback = valorInput.parentNode.querySelector('.invalid-feedback');
            if (existingFeedback) {
                existingFeedback.remove();
            }
            
            // Adicionar novo feedback
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'O valor mínimo para boletos é R$ 3,00';
            valorInput.parentNode.appendChild(feedback);
        } else {
            valorInput.classList.remove('is-invalid');
            const feedback = valorInput.parentNode.querySelector('.invalid-feedback');
            if (feedback && feedback.textContent.includes('valor mínimo')) {
                feedback.remove();
            }
        }
    }
    
    // Adicionar listeners para validação
    valorInput.addEventListener('input', validarValorMinimo);
    formaPagamentoSelect.addEventListener('change', validarValorMinimo);
    
    // Validar no submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const valor = parseFloat(valorInput.value);
        const formaPagamento = formaPagamentoSelect.value;
        
        if (formaPagamento === 'boleto' && valor > 0 && valor < 3.00) {
            e.preventDefault();
            alert('O valor mínimo para boletos é de R$ 3,00');
            valorInput.focus();
        }
    });
});
</script>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/douglas/Downloads/ec-complete-backup-20250728_105142/ec-complete-backup-20250813_144041/resources/views/admin/payments/create.blade.php ENDPATH**/ ?>