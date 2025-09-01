<!DOCTYPE html>
<html lang="pt-br">
    <head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Essentials -->
    <title>Fale Conosco - Ensino Certo</title>
    <meta name="description" content="Entre em contato com o Ensino Certo. Tire suas dúvidas sobre nossos cursos EJA, horários de atendimento e muito mais. Estamos aqui para ajudar!">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Open Graph / Facebook -->
    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="article">
    <meta property="og:title" content="Fale Conosco - Ensino Certo">
    <meta property="og:description" content="Entre em contato com o Ensino Certo. Tire suas dúvidas sobre nossos cursos EJA, horários de atendimento e muito mais. Estamos aqui para ajudar!">
    <meta property="og:url" content="https://ensinocerto.com.br/contato/">
    <meta property="og:site_name" content="Ensino Certo">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:label1" content="Est. tempo de leitura">
    <meta name="twitter:data1" content="5 minutos">

    <!-- Canonical -->
    <link rel="canonical" href="https://ensinocerto.com.br/contato/">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <!-- Cleave.js para máscaras de input -->
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/addons/cleave-phone.br.js"></script>
    <!-- Intl-Tel-Input para seletor de país com bandeiras -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/styles.css')); ?>?v=<?php echo e(time()); ?>">
    <!-- Font Rubik -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tracking Scripts -->
    <?php echo $__env->make('components.tracking-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NPXJKW38');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NPXJKW38"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php echo $__env->make('components.tracking-noscript', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <!-- Admin Access (apenas se logado) -->
    <?php if(session('admin_logged_in')): ?>
        <div class="admin-access-bar">
            <div class="container">
                <div class="admin-info">
                    <span>Logado como administrador: <?php echo e(session('admin_email')); ?></span>
                    <div class="admin-actions">
                        <?php if(session('admin_id')): ?>
                            <a href="<?php echo e(route('dashboard')); ?>" class="btn-admin">
                                <i class="fas fa-user-shield"></i>
                                Área Administrativa
                            </a>
                        <?php endif; ?>
                        <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn-admin btn-logout">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Seção 1: Header/Hero -->
    <header class="hero">
        <div class="hero-content container">
            <div class="hero-text">
                <div class="logo">
                    <img src="<?php echo e(asset('assets/images/anhangue-vip.svg')); ?>" alt="Logo Anhanguera VIP">
                    <img src="<?php echo e(asset('assets/images/logotipo-dark.svg')); ?>" alt="Logo Ensino Certo">
                </div>
                
                <h1>Entre em contato conosco</h1>
                <div class="main-title">
                    <h2>FALE <span class="highlight">CONOSCO</span></h2>
                </div>
                
                <div class="benefits-list">
                    <div class="benefit">
                        <i class="fas fa-check-circle"></i>
                        <p>Atendimento especializado para suas dúvidas</p>
                    </div>
                    
                    <div class="benefit">
                        <i class="fas fa-check-circle"></i>
                        <p>Resposta em até 24 horas úteis</p>
                    </div>
                    
                    <div class="benefit">
                        <i class="fas fa-check-circle"></i>
                        <p>Suporte completo durante todo o processo</p>
                    </div>
                </div>
            </div>
            
            <div class="hero-offer">
                <div class="form-container">
                    <p class="form-header">Envie sua mensagem <span class="vagas-info">resposta garantida</span></p>
                    <h3>Formulário de Contato</h3>
                    
                    <form method="POST" action="<?php echo e(route('contato.store')); ?>" id="formulario-contato">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <input type="text" name="nome" placeholder="Nome completo" value="<?php echo e(old('nome')); ?>" required>
                            <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Seu email" value="<?php echo e(old('email')); ?>" required>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group phone-input-container">
                            <input type="tel" id="phone" name="telefone" placeholder="Telefone" value="<?php echo e(old('telefone')); ?>" required>
                            <?php $__errorArgs = ['telefone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group">
                            <input type="text" name="assunto" placeholder="Assunto da mensagem" value="<?php echo e(old('assunto')); ?>" required>
                            <?php $__errorArgs = ['assunto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group">
                            <textarea name="mensagem" placeholder="Digite sua mensagem aqui..." rows="5" required><?php echo e(old('mensagem')); ?></textarea>
                            <?php $__errorArgs = ['mensagem'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group checkbox">
                            <input type="checkbox" id="termos" name="termos" <?php echo e(old('termos') ? 'checked' : ''); ?> required>
                            <label for="termos">Eu li e aceito os termos e condições da Política de Privacidade</label>
                            <?php $__errorArgs = ['termos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <?php if($errors->has('error')): ?>
                            <div class="form-message error">
                                <?php echo e($errors->first('error')); ?>

                            </div>
                        <?php endif; ?>
                        
                        <?php if(session('success')): ?>
                            <div class="form-message success">
                                <?php echo e(session('success')); ?>

                            </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-paper-plane me-2"></i>
                            Enviar mensagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Seção 2: Informações de Contato -->
    <section id="informacoes-contato" class="section bg-light">
        <div class="container">
            <div class="section-header text-center">
                <h2>Como Entrar em Contato</h2>
                <p class="section-subtitle">Escolha a forma mais conveniente para falar conosco</p>
            </div>
            
            <!-- Cards de Contato -->
            <div class="contact-cards-grid">
                <div class="contact-card whatsapp-card">
                    <div class="contact-card-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="contact-card-content">
                        <h3>WhatsApp</h3>
                        <p class="contact-number">(11) 91701-2033</p>
                        <p class="contact-description">Atendimento rápido e direto</p>
                        <a href="https://wa.me/5511917012033?text=Olá! Gostaria de mais informações sobre os cursos EJA." target="_blank" class="btn-contact whatsapp-btn">
                            <i class="fab fa-whatsapp"></i>
                            Conversar agora
                        </a>
                    </div>
                </div>

                <div class="contact-card phone-card">
                    <div class="contact-card-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-card-content">
                        <h3>Telefone</h3>
                        <p class="contact-number">(11) 4210-3596</p>
                        <p class="contact-description">Seg. à Sex.: 8h às 18h</p>
                        <a href="tel:1142103596" class="btn-contact phone-btn">
                            <i class="fas fa-phone"></i>
                            Ligar agora
                        </a>
                    </div>
                </div>

                <div class="contact-card email-card">
                    <div class="contact-card-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-card-content">
                        <h3>Email</h3>
                        <p class="contact-number">contato@ensinocerto.com.br</p>
                        <p class="contact-description">Resposta em até 24h</p>
                        <a href="mailto:contato@ensinocerto.com.br?subject=Informações sobre EJA" class="btn-contact email-btn">
                            <i class="fas fa-envelope"></i>
                            Enviar email
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="contact-info-section">
                <div class="contact-info-grid">
                    <div class="info-card location-card">
                        <div class="info-card-header">
                            <i class="fas fa-map-marker-alt"></i>
                            <h3>Nossa Localização</h3>
                        </div>
                        <div class="info-card-content">
                            <p><strong>Av. José Caballero, 231</strong></p>
                            <p>Vila Bastos - Santo André - SP</p>
                            <p>CEP: 09040-210</p>
                            <div class="location-actions">
                                <a href="https://maps.google.com/?q=Av.+José+Caballero,+231+-+Vila+Bastos,+Santo+André+-+SP" target="_blank" class="btn-location">
                                    <i class="fas fa-map-marked-alt"></i>
                                    Ver no mapa
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="info-card schedule-card">
                        <div class="info-card-header">
                            <i class="fas fa-clock"></i>
                            <h3>Horário de Atendimento</h3>
                        </div>
                        <div class="info-card-content">
                            <div class="schedule-item">
                                <span class="day">Segunda à Sexta</span>
                                <span class="time">8h às 18h</span>
                            </div>
                            <div class="schedule-item">
                                <span class="day">Sábado</span>
                                <span class="time">8h às 12h</span>
                            </div>
                            <div class="schedule-item closed">
                                <span class="day">Domingo</span>
                                <span class="time">Fechado</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Principal -->
                <div class="contact-cta-section">
                    <div class="contact-cta-content">
                        <h3>Ainda tem dúvidas?</h3>
                        <p>Use o formulário abaixo para enviar sua mensagem detalhada</p>
                        <a href="#formulario-contato" class="btn btn-lg btn-primary-cta">
                            <i class="fas fa-paper-plane"></i>
                            Enviar mensagem detalhada
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção 3: Perguntas Frequentes -->
    <section id="perguntas-frequentes" class="section bg-dark">
        <div class="container">
            <div class="section-header text-center">
                <h2>Perguntas Frequentes sobre Contato</h2>
                <p class="section-subtitle">Tire suas dúvidas mais comuns antes de entrar em contato</p>
            </div>
            
            <div class="faq-content">
                <div class="faq-accordion">
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-clock"></i>
                            <span>Qual o prazo de resposta para mensagens?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Nosso compromisso é responder todas as mensagens em até 24 horas úteis. Para urgências, recomendamos o contato via WhatsApp para resposta mais rápida.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-headset"></i>
                            <span>Qual o melhor horário para entrar em contato?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Nosso atendimento funciona de segunda à sexta, das 8h às 18h, e aos sábados das 8h às 12h. Para melhor atendimento, sugerimos contato durante os dias úteis.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-phone-alt"></i>
                            <span>Posso ligar diretamente para tirar dúvidas?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Sim! Você pode ligar para (11) 4210-3596 durante nosso horário de atendimento. Nossa equipe estará pronta para esclarecer todas suas dúvidas sobre nossos cursos.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-file-alt"></i>
                            <span>Preciso de documentos específicos para contatar vocês?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Para contato inicial, não é necessário nenhum documento. Caso seja sobre matrícula ou processos específicos, nossa equipe irá orientar quais documentos são necessários.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Posso visitar pessoalmente a instituição?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Sim! Estamos localizados na Av. José Caballero, 231 - Vila Bastos, Santo André - SP. Recomendamos agendar uma visita previamente para garantir o melhor atendimento.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WhatsApp Floating Button -->
    <?php if($whatsappSettings['whatsapp_enabled'] ?? true): ?>
        <div id="whatsapp-float" class="whatsapp-float <?php echo e($whatsappSettings['whatsapp_button_position'] ?? 'bottom-right'); ?>" 
             style="background-color: <?php echo e($whatsappSettings['whatsapp_button_color'] ?? '#25d366'); ?>">
            <a href="https://wa.me/<?php echo e($whatsappSettings['whatsapp_number'] ?? '5511917012033'); ?>?text=<?php echo e(urlencode($whatsappSettings['whatsapp_message'] ?? 'Olá! Gostaria de mais informações sobre os cursos. Podem me ajudar?')); ?>" 
               target="_blank" 
               rel="noopener noreferrer"
               aria-label="Conversar no WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
        </div>
    <?php endif; ?>

    </main>

    <!-- Incluir o componente footer -->
    <?php echo $__env->make('components.footer', ['landingSettings' => $landingSettings], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializar Intl-Tel-Input
        var input = document.querySelector("#phone");
        var iti = window.intlTelInput(input, {
            initialCountry: "br",
            preferredCountries: ["br"],
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });
        
        // Aplicar máscara ao telefone
        var cleave = new Cleave('#phone', {
            phone: true,
            phoneRegionCode: 'BR'
        });
        
        // Validação do formulário
        document.getElementById('formulario-contato').addEventListener('submit', function(e) {
            var phone = document.getElementById('phone');
            var fullNumber = iti.getNumber();
            
            if (!iti.isValidNumber()) {
                e.preventDefault();
                phone.classList.add('is-invalid');
                
                // Criar mensagem de erro
                var errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = 'Por favor, insira um número de telefone válido.';
                
                // Remover mensagens de erro anteriores
                var existingErrors = phone.parentNode.querySelectorAll('.error-message');
                existingErrors.forEach(function(el) {
                    el.remove();
                });
                
                // Adicionar nova mensagem de erro
                phone.parentNode.appendChild(errorDiv);
            } else {
                // Atualizar o valor do campo com o número completo
                phone.value = fullNumber;
            }
        });
        
        // FAQ Accordion Functionality
        function initFAQAccordion() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            if (faqItems.length === 0) {
                return;
            }
            
            faqItems.forEach((item, index) => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');
                
                if (!question || !answer) {
                    return;
                }
                
                // Remover listeners anteriores para evitar duplicação
                const newQuestion = question.cloneNode(true);
                question.parentNode.replaceChild(newQuestion, question);
                
                newQuestion.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const isActive = item.classList.contains('active');
                    
                    // Fechar todos os outros itens
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                            const otherAnswer = otherItem.querySelector('.faq-answer');
                            if (otherAnswer) {
                                otherAnswer.classList.remove('show');
                            }
                        }
                    });
                    
                    // Toggle do item atual
                    if (isActive) {
                        item.classList.remove('active');
                        answer.classList.remove('show');
                    } else {
                        item.classList.add('active');
                        answer.classList.add('show');
                    }
                });
                
                // Adicionar cursor pointer
                newQuestion.style.cursor = 'pointer';
            });
        }
        
        // Tracking de formulários
        document.getElementById('formulario-contato').addEventListener('submit', function() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'form_submit', {
                    'event_category': 'formulario',
                    'event_label': 'contato'
                });
            }
        });
        
        // Tracking de campos individuais
        document.querySelectorAll('#formulario-contato input, #formulario-contato textarea').forEach(function(el) {
            el.addEventListener('change', function() {
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'field_interaction', {
                        'event_category': 'formulario_contato',
                        'event_label': el.name
                    });
                }
            });
        });
        
        // Chamar a inicialização quando DOM estiver pronto
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initFAQAccordion, 100);
        });
    </script>
    </body>
</html> <?php /**PATH /home/u760830176/domains/ensinocerto.com.br/public_html/resources/views/contato.blade.php ENDPATH**/ ?>