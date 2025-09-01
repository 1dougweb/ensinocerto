<?php $__env->startSection('title', 'Pagamentos'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-credit-card me-1"></i>
                Pagamentos
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pagamentos</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.payments.dashboard')); ?>" class="btn btn-outline-primary me-2">
                <i class="fas fa-chart-bar me-1"></i>
                Dashboard
            </a>
            <a href="<?php echo e(route('admin.payments.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Novo Pagamento
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if($errors && $errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <button class="btn btn-link text-decoration-none p-0 w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosPagamentos" aria-expanded="false">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtros de Busca
                    </h5>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </button>
        </div>
        <div class="collapse" id="filtrosPagamentos">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('admin.payments.index')); ?>" id="filterForm">
                    <div class="row g-3">
                        <!-- Busca geral -->
                        <div class="col-md-6">
                            <label for="search" class="form-label">Busca geral</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="<?php echo e(request('search')); ?>" 
                                   placeholder="Nome do aluno, CPF...">
                        </div>
                        
                        <!-- Status -->
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos os status</option>
                                <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>
                                    🟡 Pendente
                                </option>
                                <option value="processing" <?php echo e(request('status') == 'processing' ? 'selected' : ''); ?>>
                                    🔄 Processando
                                </option>
                                <option value="paid" <?php echo e(request('status') == 'paid' ? 'selected' : ''); ?>>
                                    🟢 Pago
                                </option>
                                <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>
                                    🔴 Falhou
                                </option>
                                <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>
                                    ⚫ Cancelado
                                </option>
                            </select>
                        </div>

                        <!-- Gateway de Pagamento -->
                        <div class="col-md-3">
                            <label for="gateway" class="form-label">Gateway</label>
                            <select class="form-select" id="gateway" name="gateway">
                                <option value="">Todos os Gateways</option>
                                <option value="mercado_pago" <?php echo e(request('gateway') == 'mercado_pago' ? 'selected' : ''); ?>>
                                    Mercado Pago
                                </option>
                                <option value="asas" <?php echo e(request('gateway') == 'asas' ? 'selected' : ''); ?>>
                                    Banco Asas
                                </option>
                                <option value="infiny_pay" <?php echo e(request('gateway') == 'infiny_pay' ? 'selected' : ''); ?>>
                                    Infiny Pay
                                </option>
                                <option value="cora" <?php echo e(request('gateway') == 'cora' ? 'selected' : ''); ?>>
                                    Banco Cora
                                </option>
                            </select>
                        </div>

                        <!-- Forma de Pagamento -->
                        <div class="col-md-3">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                            <select class="form-select" id="forma_pagamento" name="forma_pagamento">
                                <option value="">Todas</option>
                                <option value="pix" <?php echo e(request('forma_pagamento') == 'pix' ? 'selected' : ''); ?>>
                                    PIX
                                </option>
                                <option value="cartao_credito" <?php echo e(request('forma_pagamento') == 'cartao_credito' ? 'selected' : ''); ?>>
                                    Cartão de Crédito
                                </option>
                                <option value="boleto" <?php echo e(request('forma_pagamento') == 'boleto' ? 'selected' : ''); ?>>
                                    Boleto
                                </option>
                                <option value="manual" <?php echo e(request('forma_pagamento') == 'manual' ? 'selected' : ''); ?>>
                                    Pagamento Manual
                                </option>
                            </select>
                        </div>

                        <!-- Data início -->
                        <div class="col-md-3">
                            <label for="data_inicio" class="form-label">Data Início</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_inicio" 
                                   name="data_inicio" 
                                   value="<?php echo e(request('data_inicio')); ?>">
                        </div>
                        
                        <!-- Data fim -->
                        <div class="col-md-3">
                            <label for="data_fim" class="form-label">Data Fim</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_fim" 
                                   name="data_fim" 
                                   value="<?php echo e(request('data_fim')); ?>">
                        </div>

                        <!-- Vencidos -->
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="overdue" name="overdue" value="1" <?php echo e(request('overdue') ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="overdue">
                                    Apenas vencidos
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>
                            Filtrar
                        </button>
                        <a href="<?php echo e(route('admin.payments.index')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-1"></i>
                        Lista de Pagamentos
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="text-muted">
                        Total: <?php echo e($payments->total()); ?> pagamentos
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Aluno</th>
                            <th>Valor</th>
                            <th>Gateway</th>
                            <th>Forma</th>
                            <th>Status</th>
                            <th>Vencimento</th>
                            <th>Parcela</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <small class="text-muted">#<?php echo e($payment->id); ?></small>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo e($payment->matricula->nome_completo); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo e($payment->matricula->cpf); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                        $matricula = $payment->matricula;
                                        $gateway = $matricula->payment_gateway ?? 'mercado_pago';
                                        $valorExibir = $payment->valor;
                                        
                                        // Para outros bancos, priorizar valor_pago se disponível
                                        if ($gateway !== 'mercado_pago') {
                                            if ($matricula->valor_pago && $matricula->valor_pago > 0) {
                                                $valorExibir = $matricula->valor_pago;
                                            } elseif ($matricula->valor_total_curso && $matricula->valor_total_curso > 0) {
                                                $valorExibir = $matricula->valor_total_curso;
                                            }
                                        }
                                    ?>
                                    <strong>R$ <?php echo e(number_format($valorExibir, 2, ',', '.')); ?></strong>
                                </td>
                                <td>
                                    <?php
                                        $gateway = $payment->matricula->payment_gateway ?? 'mercado_pago';
                                    ?>
                                    <?php switch($gateway):
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
                                            <span class="badge bg-secondary"><?php echo e($gateway); ?></span>
                                    <?php endswitch; ?>
                                </td>
                                <td>
                                    <?php
                                        $gateway = $payment->matricula->payment_gateway ?? 'mercado_pago';
                                    ?>
                                    <?php if($gateway === 'mercado_pago'): ?>
                                        <?php switch($payment->forma_pagamento):
                                            case ('pix'): ?>
                                                <span class="badge bg-info">PIX</span>
                                                <?php break; ?>
                                            <?php case ('cartao_credito'): ?>
                                                <span class="badge bg-primary">Cartão</span>
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
                                </td>
                                <td>
                                    <?php switch($payment->status):
                                        case ('pending'): ?>
                                            <span class="badge bg-warning">🟡 Pendente</span>
                                            <?php break; ?>
                                        <?php case ('processing'): ?>
                                            <span class="badge bg-info">🔄 Processando</span>
                                            <?php break; ?>
                                        <?php case ('paid'): ?>
                                            <span class="badge bg-success">🟢 Pago</span>
                                            <?php break; ?>
                                        <?php case ('failed'): ?>
                                            <span class="badge bg-danger">🔴 Falhou</span>
                                            <?php break; ?>
                                        <?php case ('cancelled'): ?>
                                            <span class="badge bg-dark">⚫ Cancelado</span>
                                            <?php break; ?>
                                        <?php default: ?>
                                            <span class="badge bg-light text-dark"><?php echo e($payment->status); ?></span>
                                    <?php endswitch; ?>
                                </td>
                                <td>
                                    <div>
                                        <?php echo e($payment->data_vencimento->format('d/m/Y')); ?>

                                        <?php if($payment->data_vencimento->isPast() && in_array($payment->status, ['pending', 'processing'])): ?>
                                            <br><small class="text-danger">Vencido</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($payment->total_parcelas > 1): ?>
                                        <small><?php echo e($payment->numero_parcela); ?>/<?php echo e($payment->total_parcelas); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">À vista</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo e(route('admin.payments.show', $payment)); ?>" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if($payment->hasBoleto()): ?>
                                            <a href="<?php echo e(route('admin.payments.download-boleto', $payment)); ?>" 
                                               class="btn btn-outline-success btn-sm" 
                                               title="Download Boleto">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?php echo e(route('admin.payments.edit', $payment)); ?>" 
                                           class="btn btn-outline-warning btn-sm" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" 
                                              action="<?php echo e(route('admin.payments.destroy', $payment)); ?>" 
                                              class="d-inline"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este pagamento?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" 
                                                    class="btn btn-outline-danger btn-sm" 
                                                    title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-credit-card fa-2x mb-3"></i>
                                        <p>Nenhum pagamento encontrado.</p>
                                        <a href="<?php echo e(route('admin.payments.create')); ?>" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>
                                            Criar Primeiro Pagamento
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if($payments->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($payments->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u760830176/domains/ensinocerto.com.br/public_html/resources/views/admin/payments/index.blade.php ENDPATH**/ ?>