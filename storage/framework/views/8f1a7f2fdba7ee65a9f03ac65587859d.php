<?php $__env->startSection('title', 'Detalhes da Matrícula'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <h3 class="mt-4">
        <i class="fas fa-graduation-cap me-2"></i>
        Detalhes da Matrícula
    </h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.matriculas.index')); ?>">Matrículas</a></li>
        <li class="breadcrumb-item active">Detalhes</li>
    </ol>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Barra de Progresso do Perfil -->
    <?php if (isset($component)) { $__componentOriginald126f2e885c2241e41b9de752d9c8738 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald126f2e885c2241e41b9de752d9c8738 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-progress','data' => ['progress' => $matricula->getProfileProgress()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-progress'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['progress' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($matricula->getProfileProgress())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald126f2e885c2241e41b9de752d9c8738)): ?>
<?php $attributes = $__attributesOriginald126f2e885c2241e41b9de752d9c8738; ?>
<?php unset($__attributesOriginald126f2e885c2241e41b9de752d9c8738); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald126f2e885c2241e41b9de752d9c8738)): ?>
<?php $component = $__componentOriginald126f2e885c2241e41b9de752d9c8738; ?>
<?php unset($__componentOriginald126f2e885c2241e41b9de752d9c8738); ?>
<?php endif; ?>

    <div class="row">
        <!-- Cabeçalho -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Informações Principais
                        </h5>
                        <div>
                            <a href="<?php echo e(route('admin.matriculas.edit', $matricula)); ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i>
                                Editar
                            </a>
                            <button type="button" 
                                    class="btn btn-danger btn-sm" 
                                    onclick="confirmarExclusao()">
                                <i class="fas fa-trash me-1"></i>
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Número da Matrícula:</strong></p>
                            <p class="h4">
                                <span class="badge bg-primary"><?php echo e($matricula->numero_matricula); ?></span>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Status:</strong></p>
                            <p class="h4"><?php echo e($matricula->status_formatado); ?></p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Data da Matrícula:</strong></p>
                            <p><?php echo e($matricula->created_at->format('d/m/Y H:i')); ?></p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Última Atualização:</strong></p>
                            <p><?php echo e($matricula->updated_at->format('d/m/Y H:i')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados Pessoais -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Dados Pessoais
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Nome Completo:</strong></p>
                            <p class="h5"><?php echo e($matricula->nome_completo_upper); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Data de Nascimento:</strong></p>
                            <p><?php echo e($matricula->data_nascimento->format('d/m/Y')); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Idade:</strong></p>
                            <p><?php echo e($matricula->idade); ?> anos</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Sexo:</strong></p>
                            <p><?php echo e($matricula->sexo == 'M' ? 'Masculino' : ($matricula->sexo == 'F' ? 'Feminino' : 'Outro')); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>CPF:</strong></p>
                            <p><?php echo e($matricula->cpf_formatado); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>RG:</strong></p>
                            <p><?php echo e($matricula->rg); ?> (<?php echo e($matricula->orgao_emissor); ?>)</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Estado Civil:</strong></p>
                            <p><?php echo e(ucfirst($matricula->estado_civil)); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Nacionalidade:</strong></p>
                            <p><?php echo e($matricula->nacionalidade); ?></p>
                        </div>
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Naturalidade:</strong></p>
                            <p><?php echo e($matricula->naturalidade); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contato -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-phone me-2"></i>
                        Contato
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Endereço:</strong></p>
                            <p><?php echo e($matricula->endereco_completo); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Celular (Opcional):</strong></p>
                            <p><?php echo e($matricula->telefone_fixo_formatado ?? 'Não informado'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Celular:</strong></p>
                            <p><?php echo e($matricula->telefone_celular_formatado); ?></p>
                        </div>
                        <div class="col-md-12">
                            <p class="mb-1"><strong>E-mail:</strong></p>
                            <p><?php echo e($matricula->email); ?></p>
                        </div>
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Nome da Mãe:</strong></p>
                            <p><?php echo e($matricula->nome_mae); ?></p>
                        </div>
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Nome do Pai:</strong></p>
                            <p><?php echo e($matricula->nome_pai ?? 'Não informado'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados Acadêmicos -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Dados Acadêmicos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Modalidade:</strong></p>
                            <p><?php echo e($matricula->modalidade_formatada); ?></p>
                        </div>
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Curso:</strong></p>
                            <p><?php echo e($matricula->curso); ?></p>
                        </div>

                        <div class="col-md-6">
                            <p class="mb-1"><strong>Última Série Cursada:</strong></p>
                            <p><?php echo e($matricula->ultima_serie); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Ano de Conclusão:</strong></p>
                            <p><?php echo e($matricula->ano_conclusao); ?></p>
                        </div>
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Escola de Origem:</strong></p>
                            <p><?php echo e($matricula->escola_origem); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados Financeiros -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        Dados Financeiros
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Gateway de Pagamento:</strong></p>
                            <p>
                                <?php switch($matricula->payment_gateway ?? 'mercado_pago'):
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
                                        <span class="badge bg-secondary"><?php echo e($matricula->payment_gateway); ?></span>
                                <?php endswitch; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Forma de Pagamento:</strong></p>
                            <p><?php echo e($matricula->forma_pagamento_formatada); ?></p>
                        </div>
                        
                        <?php if(($matricula->payment_gateway ?? 'mercado_pago') !== 'mercado_pago'): ?>
                            <!-- Campos para outros bancos -->
                            <?php if($matricula->valor_pago): ?>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Valor Pago:</strong></p>
                                    <p class="h5 text-success">R$ <?php echo e(number_format($matricula->valor_pago, 2, ',', '.')); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Valor Total:</strong></p>
                                    <p class="h5">R$ <?php echo e(number_format($matricula->valor_total_curso, 2, ',', '.')); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="col-md-12">
                                    <p class="mb-1"><strong>Valor Total do Curso:</strong></p>
                                    <p class="h5">R$ <?php echo e(number_format($matricula->valor_total_curso, 2, ',', '.')); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($matricula->bank_info): ?>
                                <div class="col-md-12">
                                    <p class="mb-1"><strong>Informações do Banco:</strong></p>
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <?php echo e($matricula->bank_info); ?>

                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Campos para Mercado Pago -->
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Valor Total do Curso:</strong></p>
                                <p class="h5 text-primary">R$ <?php echo e(number_format((float)($matricula->valor_total_curso ?? 0), 2, ',', '.')); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Tipo de Pagamento:</strong></p>
                                <p class="h6">
                                    <?php if($matricula->tipo_boleto === 'avista'): ?>
                                        <span class="badge bg-success">À Vista</span>
                                    <?php elseif($matricula->numero_parcelas > 1): ?>
                                        <span class="badge bg-info"><?php echo e($matricula->numero_parcelas); ?>x Parcelas</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pagamento Único</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <?php if($matricula->valor_matricula > 0): ?>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Valor da Matrícula:</strong></p>
                                    <p class="h6 text-success">R$ <?php echo e(number_format((float)$matricula->valor_matricula, 2, ',', '.')); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($matricula->numero_parcelas > 1): ?>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Valor da Mensalidade:</strong></p>
                                    <?php
                                        $valorMensalidade = (float)($matricula->valor_mensalidade ?? 0);
                                        if ($valorMensalidade == 0 && $matricula->numero_parcelas > 0) {
                                            // Calcular se não estiver definido
                                            $valorParaParcelar = (float)$matricula->valor_total_curso - (float)($matricula->valor_matricula ?? 0);
                                            $valorMensalidade = $valorParaParcelar / $matricula->numero_parcelas;
                                        }
                                    ?>
                                    <p class="h6">R$ <?php echo e(number_format($valorMensalidade, 2, ',', '.')); ?></p>
                                </div>
                                <div class="col-md-12">
                                    <p class="mb-1"><strong>Dia do Vencimento:</strong></p>
                                    <p><i class="fas fa-calendar me-1"></i>Todo dia <?php echo e($matricula->dia_vencimento); ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagamentos -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Pagamentos
                        </h5>
                        <div class="btn-group">
                            <a href="<?php echo e(route('admin.payments.create', ['matricula_id' => $matricula->id])); ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-1"></i>
                                Novo Pagamento
                            </a>
                            <?php if($matricula->numero_parcelas > 1 && $matricula->tipo_boleto === 'parcelado'): ?>
                                <button type="button" class="btn btn-warning btn-sm" onclick="regeneratePayments(<?php echo e($matricula->id); ?>)">
                                    <i class="fas fa-sync me-1"></i>
                                    Regenerar Parcelas
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($matricula->payments->count() > 0 || ($matricula->numero_parcelas && $matricula->numero_parcelas > 1 && $matricula->tipo_boleto === 'parcelado')): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Parcela</th>
                                        <th>Valor</th>
                                        <th>Vencimento</th>
                                        <th>Status</th>
                                        <th>Pagamento</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    <?php if($matricula->valor_matricula > 0): ?>
                                        <?php
                                            $paymentMatricula = $matricula->payments->where('numero_parcela', 0)->first();
                                            if (!$paymentMatricula) {
                                                $paymentMatricula = $matricula->payments->filter(function($payment) {
                                                    return str_contains(strtolower($payment->descricao), 'matrícula');
                                                })->first();
                                            }
                                            // Matrícula sempre vence em 7 dias
                                            $dueDate = \Carbon\Carbon::now()->addDays(7);
                                        ?>
                                        <tr class="<?php echo e($paymentMatricula ? ($paymentMatricula->status === 'paid' ? 'table-success' : 'table-warning') : 'table-light'); ?>">
                                            <td>
                                                <span class="badge bg-<?php echo e($paymentMatricula ? ($paymentMatricula->status === 'paid' ? 'success' : 'info') : 'info'); ?>">
                                                    <i class="fas fa-graduation-cap me-1"></i>
                                                    Matrícula
                                                </span>
                                            </td>
                                            <td class="fw-bold">
                                                R$ <?php echo e(number_format($matricula->valor_matricula, 2, ',', '.')); ?>

                                            </td>
                                            <td>
                                                <?php if($paymentMatricula): ?>
                                                    <?php echo e($paymentMatricula->getFormattedDueDate()); ?>

                                                    <?php if($paymentMatricula->isOverdue()): ?>
                                                        <small class="text-danger d-block">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            <?php echo e($paymentMatricula->getFormattedDaysOverdue()); ?> em atraso
                                                        </small>
                                                    <?php elseif($paymentMatricula->isDueSoon()): ?>
                                                        <small class="text-warning d-block">
                                                            <i class="fas fa-clock"></i>
                                                            Vence em <?php echo e($paymentMatricula->getDaysUntilDue()); ?> dias
                                                        </small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Aguardando geração</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($paymentMatricula): ?>
                                                    <span class="badge bg-<?php echo e($paymentMatricula->getStatusColor()); ?>">
                                                        <?php echo e($paymentMatricula->getStatusLabel()); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Pendente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($paymentMatricula && $paymentMatricula->isPaid()): ?>
                                                    <small class="text-success">
                                                        <i class="fas fa-check"></i>
                                                        <?php echo e($paymentMatricula->getFormattedPaidDate()); ?>

                                                    </small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($paymentMatricula): ?>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?php echo e(route('admin.payments.show', $paymentMatricula)); ?>" class="btn btn-outline-primary btn-sm" title="Ver Detalhes">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        
                                                        <?php if($paymentMatricula->hasBoleto()): ?>
                                                            <a href="<?php echo e(route('admin.payments.download-boleto', $paymentMatricula)); ?>" 
                                                               class="btn btn-outline-success btn-sm" 
                                                               title="Download Boleto">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($paymentMatricula->hasPixCode()): ?>
                                                            <button type="button" 
                                                                    class="btn btn-outline-info btn-sm" 
                                                                    title="Código PIX"
                                                                    onclick="showPixCode('<?php echo e($paymentMatricula->codigo_pix); ?>')">
                                                                <i class="fas fa-qrcode"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if(!$paymentMatricula->isPaid()): ?>
                                                            <a href="<?php echo e(route('admin.payments.edit', $paymentMatricula)); ?>" class="btn btn-outline-warning btn-sm" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    
                                    <?php if($matricula->tipo_boleto === 'parcelado' && $matricula->numero_parcelas > 1): ?>
                                        <?php for($i = 1; $i <= $matricula->numero_parcelas; $i++): ?>
                                            <?php
                                                $payment = $matricula->payments->where('numero_parcela', $i)->first();
                                                $dueDate = null;
                                                if ($matricula->dia_vencimento) {
                                                    // Mensalidades começam sempre no próximo mês
                                                    $dueDate = \Carbon\Carbon::now()->startOfMonth()->addMonths($i)->setDay((int) $matricula->dia_vencimento);
                                                    // Se a data calculada for antes de hoje, adicionar mais um mês
                                                    if ($dueDate->isPast()) {
                                                        $dueDate = $dueDate->addMonths(1);
                                                    }
                                                }
                                            ?>
                                            <tr class="<?php echo e($payment ? ($payment->status === 'paid' ? 'table-success' : 'table-warning') : 'table-light'); ?>">
                                                <td>
                                                    <?php
                                                        $gateway = $matricula->payment_gateway ?? 'mercado_pago';
                                                    ?>
                                                    <span class="badge bg-<?php echo e($payment ? ($payment->status === 'paid' ? 'success' : 'warning') : 'secondary'); ?>">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        Mensalidade <?php echo e($i); ?>

                                                    </span>
                                                </td>
                                                <td class="fw-bold">
                                                    <?php
                                                        $gateway = $matricula->payment_gateway ?? 'mercado_pago';
                                                    ?>
                                                    
                                                    <?php if($gateway === 'mercado_pago'): ?>
                                                        
                                                        <?php
                                                            $valorMP = $matricula->valor_mensalidade;
                                                            // Se valor_mensalidade for 0 ou nulo, calcular baseado no valor total
                                                            if ((!$valorMP || $valorMP == 0) && $matricula->numero_parcelas > 0 && $matricula->valor_total_curso > 0) {
                                                                // Para parcelado, descontar o valor da matrícula e dividir pelas parcelas
                                                                $valorParaParcelar = $matricula->valor_total_curso - ($matricula->valor_matricula ?? 0);
                                                                $valorMP = $valorParaParcelar / $matricula->numero_parcelas;
                                                            }
                                                            $valorMP = $valorMP ?? 0;
                                                        ?>
                                                        R$ <?php echo e(number_format($valorMP, 2, ',', '.')); ?>

                                                    <?php else: ?>
                                                        
                                                        <?php if($matricula->valor_pago && $matricula->numero_parcelas > 1): ?>
                                                            R$ <?php echo e(number_format($matricula->valor_pago / $matricula->numero_parcelas, 2, ',', '.')); ?>

                                                        <?php elseif($matricula->valor_pago): ?>
                                                            R$ <?php echo e(number_format($matricula->valor_pago, 2, ',', '.')); ?>

                                                        <?php elseif($matricula->numero_parcelas > 1): ?>
                                                            <?php
                                                                // Para parcelado, descontar o valor da matrícula e dividir pelas parcelas
                                                                $valorParaParcelar = $matricula->valor_total_curso - ($matricula->valor_matricula ?? 0);
                                                                $valorParcela = $valorParaParcelar / $matricula->numero_parcelas;
                                                            ?>
                                                            R$ <?php echo e(number_format($valorParcela, 2, ',', '.')); ?>

                                                        <?php else: ?>
                                                            R$ <?php echo e(number_format($matricula->valor_total_curso, 2, ',', '.')); ?>

                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($payment): ?>
                                                        <?php echo e($payment->getFormattedDueDate()); ?>

                                                        <?php if($payment->isOverdue()): ?>
                                                            <small class="text-danger d-block">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                <?php echo e($payment->getFormattedDaysOverdue()); ?> em atraso
                                                            </small>
                                                        <?php elseif($payment->isDueSoon()): ?>
                                                            <small class="text-warning d-block">
                                                                <i class="fas fa-clock"></i>
                                                                Vence em <?php echo e($payment->getDaysUntilDue()); ?> dias
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php elseif($dueDate): ?>
                                                        <strong><?php echo e($dueDate->format('d/m/Y')); ?></strong>
                                                        <?php if($dueDate->isPast()): ?>
                                                            <small class="text-danger d-block">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                Vencida
                                                            </small>
                                                        <?php else: ?>
                                                            <?php
                                                                $daysUntilDue = $dueDate->diffInDays(now(), false);
                                                            ?>
                                                            <?php if($daysUntilDue <= 7 && $daysUntilDue > 0): ?>
                                                                <small class="text-warning d-block">
                                                                    <i class="fas fa-clock"></i>
                                                                    Vence em <?php echo e($daysUntilDue); ?> dias
                                                                </small>
                                                            <?php elseif($daysUntilDue > 7): ?>
                                                                <small class="text-info d-block">
                                                                    <i class="fas fa-calendar"></i>
                                                                    Vence em <?php echo e($daysUntilDue); ?> dias
                                                                </small>
                                                            <?php elseif($daysUntilDue === 0): ?>
                                                                <small class="text-warning d-block">
                                                                    <i class="fas fa-exclamation-circle"></i>
                                                                    Vence hoje!
                                                                </small>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($payment): ?>
                                                        <span class="badge bg-<?php echo e($payment->getStatusColor()); ?>">
                                                            <?php echo e($payment->getStatusLabel()); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        <?php
                                                            // Mensalidades futuras são "Não Cobrada"
                                                            // Mensalidades que já chegaram no período de cobrança são "Pendente"
                                                            $isFuture = $dueDate && $dueDate > now();
                                                            $statusText = $isFuture ? 'Não Cobrada' : 'Pendente';
                                                            $statusColor = $isFuture ? 'secondary' : 'warning';
                                                        ?>
                                                        <span class="badge bg-<?php echo e($statusColor); ?>"><?php echo e($statusText); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($payment && $payment->isPaid()): ?>
                                                        <small class="text-success">
                                                            <i class="fas fa-check"></i>
                                                            <?php echo e($payment->getFormattedPaidDate()); ?>

                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">-</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($payment): ?>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?php echo e(route('admin.payments.show', $payment)); ?>" 
                                                               class="btn btn-outline-secondary btn-sm" 
                                                               title="Detalhes">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            
                                                            <?php if($payment->hasBoleto()): ?>
                                                                <a href="<?php echo e(route('admin.payments.download-boleto', $payment)); ?>" 
                                                                   class="btn btn-outline-success btn-sm" 
                                                                   title="Download Boleto">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            
                                                            <?php if($payment->hasPixCode()): ?>
                                                                <button type="button" 
                                                                        class="btn btn-outline-info btn-sm" 
                                                                        title="Código PIX"
                                                                        onclick="showPixCode('<?php echo e($payment->codigo_pix); ?>')">
                                                                    <i class="fas fa-qrcode"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            
                                                            <a href="<?php echo e(route('admin.payments.edit', $payment)); ?>" 
                                                               class="btn btn-outline-secondary btn-sm" 
                                                               title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if($payment->isPending() && ($matricula->payment_gateway ?? 'mercado_pago') !== 'mercado_pago'): ?>
                                                                <button type="button" 
                                                                        class="btn btn-outline-success btn-sm" 
                                                                        title="Marcar como Pago"
                                                                        onclick="markAsPaid(<?php echo e($payment->id); ?>)">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            <?php elseif($payment->isPending() && ($matricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago'): ?>
                                                                <!-- <small class="text-info">
                                                                    <i class="fas fa-info-circle"></i>
                                                                    Status via webhook
                                                                </small> -->
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Aguardando cobrança</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endfor; ?>
                                    <?php else: ?>
                                        <?php $__currentLoopData = $matricula->payments->sortBy('numero_parcela'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td>
                                                    <?php
                                                        $gateway = $matricula->payment_gateway ?? 'mercado_pago';
                                                    ?>
                                                    <?php if($gateway === 'mercado_pago'): ?>
                                                        
                                                        <?php if($matricula->forma_pagamento === 'boleto' && $matricula->numero_parcelas > 1): ?>
                                                            <span class="badge bg-secondary">
                                                                <?php echo e($payment->getInstallmentLabel()); ?>

                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-primary">
                                                                À Vista
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        
                                                        <?php if($matricula->numero_parcelas > 1): ?>
                                                            <span class="badge bg-secondary">
                                                                <?php echo e($payment->getInstallmentLabel()); ?>

                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-primary">
                                                                À Vista/Manual
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold">
                                                    <?php
                                                        $gateway = $matricula->payment_gateway ?? 'mercado_pago';
                                                    ?>
                                                    <?php if($gateway !== 'mercado_pago'): ?>
                                                        
                                                        <?php if($matricula->valor_pago && $matricula->numero_parcelas > 1): ?>
                                                            R$ <?php echo e(number_format($matricula->valor_pago / $matricula->numero_parcelas, 2, ',', '.')); ?>

                                                        <?php elseif($matricula->valor_pago): ?>
                                                            R$ <?php echo e(number_format($matricula->valor_pago, 2, ',', '.')); ?>

                                                        <?php elseif($matricula->numero_parcelas > 1): ?>
                                                            <?php
                                                                // Para parcelado, descontar o valor da matrícula e dividir pelas parcelas
                                                                $valorParaParcelar = $matricula->valor_total_curso - ($matricula->valor_matricula ?? 0);
                                                                $valorParcela = $valorParaParcelar / $matricula->numero_parcelas;
                                                            ?>
                                                            R$ <?php echo e(number_format($valorParcela, 2, ',', '.')); ?>

                                                        <?php else: ?>
                                                            R$ <?php echo e(number_format($matricula->valor_total_curso, 2, ',', '.')); ?>

                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        
                                                        <?php
                                                            $valorPayment = $payment->valor;
                                                            // Se valor do payment for 0, calcular baseado na matrícula
                                                            if (!$valorPayment || $valorPayment == 0) {
                                                                $valorParaParcelar = $matricula->valor_total_curso - ($matricula->valor_matricula ?? 0);
                                                                $valorPayment = $valorParaParcelar / $matricula->numero_parcelas;
                                                            }
                                                        ?>
                                                        R$ <?php echo e(number_format($valorPayment, 2, ',', '.')); ?>

                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo e($payment->getFormattedDueDate()); ?>

                                                    <?php if($payment->isOverdue()): ?>
                                                        <small class="text-danger d-block">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            <?php echo e($payment->getFormattedDaysOverdue()); ?> em atraso
                                                        </small>
                                                    <?php elseif($payment->isDueSoon()): ?>
                                                        <small class="text-warning d-block">
                                                            <i class="fas fa-clock"></i>
                                                            Vence em <?php echo e($payment->getDaysUntilDue()); ?> dias
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo e($payment->getStatusColor()); ?>">
                                                        <?php echo e($payment->getStatusLabel()); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($payment->isPaid()): ?>
                                                        <small class="text-success">
                                                            <i class="fas fa-check"></i>
                                                            <?php echo e($payment->getFormattedPaidDate()); ?>

                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">-</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?php echo e(route('admin.payments.show', $payment)); ?>" 
                                                           class="btn btn-outline-secondary btn-sm" 
                                                           title="Detalhes">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        
                                                        <?php if($payment->hasBoleto()): ?>
                                                            <a href="<?php echo e(route('admin.payments.download-boleto', $payment)); ?>" 
                                                               class="btn btn-outline-success btn-sm" 
                                                               title="Download Boleto">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($payment->hasPixCode()): ?>
                                                            <button type="button" 
                                                                    class="btn btn-outline-info btn-sm" 
                                                                    title="Código PIX"
                                                                    onclick="showPixCode('<?php echo e($payment->codigo_pix); ?>')">
                                                                <i class="fas fa-qrcode"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <a href="<?php echo e(route('admin.payments.edit', $payment)); ?>" 
                                                           class="btn btn-outline-secondary btn-sm" 
                                                           title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if($payment->isPending() && ($matricula->payment_gateway ?? 'mercado_pago') !== 'mercado_pago'): ?>
                                                            <button type="button" 
                                                                    class="btn btn-outline-success btn-sm" 
                                                                    title="Marcar como Pago"
                                                                    onclick="markAsPaid(<?php echo e($payment->id); ?>)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <?php elseif($payment->isPending() && ($matricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago'): ?>
                                                            <!-- <small class="text-info">
                                                                <i class="fas fa-info-circle"></i>
                                                                Status via webhook
                                                            </small> -->
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Resumo dos Pagamentos -->
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <small class="text-muted">Total de Pagamentos</small>
                                    <div class="h5 mb-0"><?php echo e($matricula->payments->count()); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <small class="text-muted">Pagos</small>
                                    <div class="h5 mb-0 text-success"><?php echo e($matricula->payments->where('status', 'paid')->count()); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <small class="text-muted">Pendentes</small>
                                    <div class="h5 mb-0 text-warning"><?php echo e($matricula->payments->where('status', 'pending')->count()); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <small class="text-muted">Valor Total</small>
                                    <div class="h5 mb-0 text-primary">
                                        <?php if(($matricula->payment_gateway ?? 'mercado_pago') !== 'mercado_pago'): ?>
                                            
                                            <?php if($matricula->valor_pago): ?>
                                                R$ <?php echo e(number_format($matricula->valor_pago, 2, ',', '.')); ?>

                                            <?php else: ?>
                                                R$ <?php echo e(number_format($matricula->valor_total_curso, 2, ',', '.')); ?>

                                            <?php endif; ?>
                                        <?php else: ?>
                                            
                                            <?php
                                                $valorTotalReal = 0;
                                                
                                                // Somar valor da matrícula se houver
                                                if ($matricula->valor_matricula > 0) {
                                                    $valorTotalReal += $matricula->valor_matricula;
                                                }
                                                
                                                // Somar valor das mensalidades
                                                if ($matricula->numero_parcelas > 1 && $matricula->tipo_boleto === 'parcelado') {
                                                    $valorMensalidade = $matricula->valor_mensalidade;
                                                    if (!$valorMensalidade || $valorMensalidade == 0) {
                                                        // Calcular se não estiver definido
                                                        $valorParaParcelar = $matricula->valor_total_curso - ($matricula->valor_matricula ?? 0);
                                                        $valorMensalidade = $valorParaParcelar / $matricula->numero_parcelas;
                                                    }
                                                    $valorTotalReal += ($valorMensalidade * $matricula->numero_parcelas);
                                                } elseif ($matricula->numero_parcelas == 1) {
                                                    // Pagamento à vista
                                                    $valorTotalReal += ($matricula->valor_total_curso - ($matricula->valor_matricula ?? 0));
                                                }
                                            ?>
                                            R$ <?php echo e(number_format($valorTotalReal, 2, ',', '.')); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-credit-card fa-3x mb-3 d-block"></i>
                            <p>Nenhum pagamento criado ainda.</p>
                            <a href="<?php echo e(route('admin.payments.create', ['matricula_id' => $matricula->id])); ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Criar Primeiro Pagamento
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Documentos -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Documentos Entregues
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       disabled 
                                       <?php echo e($matricula->doc_rg ? 'checked' : ''); ?>>
                                <label class="form-check-label">
                                    RG (cópia)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       disabled 
                                       <?php echo e($matricula->doc_cpf ? 'checked' : ''); ?>>
                                <label class="form-check-label">
                                    CPF (cópia)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       disabled 
                                       <?php echo e($matricula->doc_comprovante_residencia ? 'checked' : ''); ?>>
                                <label class="form-check-label">
                                    Comprovante de Residência
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       disabled 
                                       <?php echo e($matricula->doc_historico ? 'checked' : ''); ?>>
                                <label class="form-check-label">
                                    Histórico Escolar
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       disabled 
                                       <?php echo e($matricula->doc_certificado ? 'checked' : ''); ?>>
                                <label class="form-check-label">
                                    Certificado de Conclusão
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       disabled 
                                       <?php echo e($matricula->doc_certidao ? 'checked' : ''); ?>>
                                <label class="form-check-label">
                                    Certidão de Nascimento/Casamento
                                </label>
                            </div>
                        </div>
                        <?php if($matricula->menor_de_idade): ?>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           disabled 
                                           <?php echo e($matricula->doc_responsavel ? 'checked' : ''); ?>>
                                    <label class="form-check-label">
                                        Documentos do Responsável
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Observações -->
        <?php if($matricula->observacoes): ?>
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-sticky-note me-2"></i>
                            Observações
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php echo e($matricula->observacoes); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Contratos -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-contract me-2"></i>
                            Contratos
                        </h5>
                        <button type="button" class="btn btn-success btn-sm" onclick="generateContract()">
                            <i class="fas fa-plus me-1"></i>
                            Gerar Contrato
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="contracts-list">
                        <?php if($matricula->contracts->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Número</th>
                                            <th>Título</th>
                                            <th>Status</th>
                                            <th>Criado em</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $matricula->contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($contract->contract_number); ?></td>
                                                <td><?php echo e($contract->title); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo e($contract->status_color); ?>">
                                                        <?php echo e($contract->status_formatted); ?>

                                                    </span>
                                                </td>
                                                <td><?php echo e($contract->created_at->format('d/m/Y H:i')); ?></td>
                                                <td>
                                                    <?php if($contract->status === 'signed'): ?>
                                                        <a href="<?php echo e(route('admin.contracts.view-signed', $contract)); ?>" 
                                                           class="btn btn-info btn-xs" title="Ver Contrato Assinado">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="<?php echo e(route('admin.contracts.download-pdf', $contract)); ?>" 
                                                           class="btn btn-success btn-xs" title="Download PDF">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php elseif(in_array($contract->status, ['draft', 'expired'])): ?>
                                                        <button type="button" class="btn btn-primary btn-xs" 
                                                                onclick="sendContract(<?php echo e($contract->id); ?>)" title="Enviar por Email">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(in_array($contract->status, ['draft', 'sent', 'viewed', 'expired'])): ?>
                                                        <button type="button" class="btn btn-success btn-xs" 
                                                                onclick="sendContractWhatsApp(<?php echo e($contract->id); ?>)" title="Enviar via WhatsApp">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($contract->status !== 'signed'): ?>
                                                        <button type="button" class="btn btn-secondary btn-xs" 
                                                                onclick="regenerateLink(<?php echo e($contract->id); ?>)" title="Novo Link">
                                                            <i class="fas fa-refresh"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-xs" 
                                                                onclick="cancelContract(<?php echo e($contract->id); ?>)" title="Cancelar">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhum contrato gerado ainda.</p>
                                <button type="button" class="btn btn-success" onclick="generateContract()">
                                    <i class="fas fa-plus me-1"></i>
                                    Gerar Primeiro Contrato
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações do Sistema -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informações do Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Criado por:</strong></p>
                            <p><?php echo e($matricula->createdBy->name); ?> em <?php echo e($matricula->created_at->format('d/m/Y H:i:s')); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Última atualização por:</strong></p>
                            <p><?php echo e($matricula->updatedBy->name); ?> em <?php echo e($matricula->updated_at->format('d/m/Y H:i:s')); ?></p>
                        </div>
                        <?php if($matricula->inscricao): ?>
                            <div class="col-md-12">
                                <p class="mb-1"><strong>Inscrição vinculada:</strong></p>
                                <p>
                                    <a href="<?php echo e(route('admin.inscricoes.show', $matricula->inscricao)); ?>" class="text-decoration-none">
                                        #<?php echo e($matricula->inscricao->id); ?> - <?php echo e($matricula->inscricao->nome); ?>

                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a matrícula do aluno <strong><?php echo e($matricula->nome_completo); ?></strong>?</p>
                <p class="text-danger mb-0">Esta ação não poderá ser desfeita!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="<?php echo e(route('admin.matriculas.destroy', $matricula)); ?>" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function confirmarExclusao() {
        const modal = new bootstrap.Modal(document.getElementById('modalExclusao'));
        modal.show();
    }

    function markAsPaid(paymentId) {
        if (confirm('Tem certeza que deseja marcar este pagamento como pago?')) {
            // Criar formulário temporário para submissão
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/dashboard/pagamentos/${paymentId}/mark-paid`;
            
            // Token CSRF
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '<?php echo e(csrf_token()); ?>';
            form.appendChild(csrfToken);
            
            // Submeter formulário
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Funções para contratos
    function generateContract() {
        if (confirm('Deseja gerar um novo contrato para este aluno?')) {
            fetch(`/admin/contracts/matricula/<?php echo e($matricula->id); ?>/generate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Contrato gerado com sucesso!\n\nLink de acesso: ' + data.access_link);
                    // 🚨 PROTEÇÃO: Evitar reload em loop
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Erro ao gerar contrato: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro de conexão. Tente novamente.');
            });
        }
    }

    function sendContract(contractId) {
        if (confirm('Deseja enviar este contrato por email para o aluno?')) {
            fetch(`/admin/contracts/${contractId}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Contrato enviado por email com sucesso!\n\nLink de acesso: ' + data.access_link);
                    location.reload();
                } else {
                    alert('Erro ao enviar contrato: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro de conexão. Tente novamente.');
            });
        }
    }

    function sendContractWhatsApp(contractId) {
        if (confirm('Deseja enviar este contrato via WhatsApp para o aluno?')) {
            fetch(`/admin/contracts/${contractId}/send-whatsapp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Contrato enviado via WhatsApp com sucesso!\n\nLink de acesso: ' + data.access_link);
                    location.reload();
                } else {
                    alert('Erro ao enviar contrato via WhatsApp: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro de conexão. Tente novamente.');
            });
        }
    }

    function regenerateLink(contractId) {
        if (confirm('Deseja gerar um novo link de acesso para este contrato?')) {
            fetch(`/admin/contracts/${contractId}/regenerate-link`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Novo link gerado com sucesso!\n\nLink de acesso: ' + data.access_link);
                    location.reload();
                } else {
                    alert('Erro ao gerar novo link: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro de conexão. Tente novamente.');
            });
        }
    }

    function cancelContract(contractId) {
        if (confirm('Deseja cancelar este contrato? Esta ação não poderá ser desfeita.')) {
            fetch(`/admin/contracts/${contractId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Contrato cancelado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao cancelar contrato: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro de conexão. Tente novamente.');
            });
    }

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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function regeneratePayments(matriculaId) {
    if (!confirm('Isso irá recriar todas as parcelas pendentes. Os valores de mensalidade serão recalculados automaticamente. Deseja continuar?')) {
        return;
    }
    
    // Mostrar loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Regenerando...';
    btn.disabled = true;
    
    fetch(`<?php echo e(url('dashboard/matriculas')); ?>/${matriculaId}/regenerate-payments`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Parcelas regeneradas com sucesso!');
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.location.reload();
            }
        } else {
            alert('Erro: ' + data.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao regenerar parcelas. Verifique o console para mais detalhes.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u760830176/domains/ensinocerto.com.br/public_html/resources/views/admin/matriculas/show.blade.php ENDPATH**/ ?>