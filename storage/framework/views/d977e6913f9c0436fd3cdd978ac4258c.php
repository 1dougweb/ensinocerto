<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['progress']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['progress']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="profile-progress-container mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php if($progress['percentage'] >= 80): ?>
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            <?php elseif($progress['percentage'] >= 50): ?>
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            <?php else: ?>
                                <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                Completar Perfil 
                                <span class="badge 
                                    <?php if($progress['percentage'] >= 80): ?> bg-success 
                                    <?php elseif($progress['percentage'] >= 50): ?> bg-warning 
                                    <?php else: ?> bg-danger 
                                    <?php endif; ?>">
                                    <?php echo e($progress['percentage']); ?>%
                                </span>
                            </h6>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar 
                                    <?php if($progress['percentage'] >= 80): ?> bg-success 
                                    <?php elseif($progress['percentage'] >= 50): ?> bg-warning 
                                    <?php else: ?> bg-danger 
                                    <?php endif; ?>" 
                                    role="progressbar" 
                                    style="width: <?php echo e($progress['percentage']); ?>%"
                                    aria-valuenow="<?php echo e($progress['percentage']); ?>" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php echo e($progress['completed']); ?> de <?php echo e($progress['total']); ?> campos preenchidos
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <?php if($progress['percentage'] < 100): ?>
                        <button type="button" 
                                class="btn btn-outline-primary btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#missingFieldsModal">
                            <i class="fas fa-list me-1"></i>
                            Ver Campos Faltantes
                        </button>
                    <?php else: ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>
                            Perfil Completo
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal com campos faltantes -->
<div class="modal fade" id="missingFieldsModal" tabindex="-1" aria-labelledby="missingFieldsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="missingFieldsModalLabel">
                    <i class="fas fa-list me-2"></i>
                    Campos Faltantes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if(count($progress['missing_fields']) > 0): ?>
                    <p class="text-muted mb-3">
                        Complete os campos abaixo para melhorar seu perfil:
                    </p>
                    <div class="row">
                        <?php $__currentLoopData = $progress['missing_fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-circle text-danger me-2" style="font-size: 8px;"></i>
                                    <span><?php echo e($field); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h6>Perfil Completo!</h6>
                        <p class="text-muted">Todos os campos foram preenchidos.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <?php if(count($progress['missing_fields']) > 0): ?>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="scrollToFirstMissingField()">
                        <i class="fas fa-edit me-1"></i>
                        Completar Agora
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function scrollToFirstMissingField() {
    // Lista de campos faltantes para tentar encontrar no formulário
    const missingFields = <?php echo json_encode($progress['missing_fields'], 15, 512) ?>;
    const fieldMapping = {
        'Data de Nascimento': 'data_nascimento',
        'RG': 'rg',
        'Órgão Emissor': 'orgao_emissor',
        'Sexo': 'sexo',
        'Estado Civil': 'estado_civil',
        'Nacionalidade': 'nacionalidade',
        'Naturalidade': 'naturalidade',
        'Nome da Mãe': 'nome_mae',
        'Nome do Pai': 'nome_pai',
        'CEP': 'cep',
        'Logradouro': 'logradouro',
        'Número': 'numero',
        'Bairro': 'bairro',
        'Cidade': 'cidade',
        'Estado': 'estado',
        'Última Série': 'ultima_serie',
        'Ano de Conclusão': 'ano_conclusao',
        'Escola de Origem': 'escola_origem',
        'Telefone Fixo': 'telefone_fixo'
    };
    
    // Encontrar o primeiro campo faltante no formulário
    for (let fieldLabel of missingFields) {
        const fieldId = fieldMapping[fieldLabel];
        if (fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                field.focus();
                
                // Destacar o campo temporariamente
                field.classList.add('border-warning');
                setTimeout(() => {
                    field.classList.remove('border-warning');
                }, 3000);
                
                break;
            }
        }
    }
}
</script>

<style>
.profile-progress-container .card {
    border-left: 4px solid #007bff;
}

.profile-progress-container .progress {
    border-radius: 10px;
}

.profile-progress-container .progress-bar {
    border-radius: 10px;
}

.border-warning {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}
</style> <?php /**PATH /home/douglas/Downloads/ec-complete-backup-20250728_105142/ec-complete-backup-20250813_144041/resources/views/components/profile-progress.blade.php ENDPATH**/ ?>