<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('home'); ?>">Home</a></li>
            <li class="breadcrumb-item active">Contato</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <h1 class="mb-4">
                <i class="bi bi-envelope-open"></i> Fale Conosco
            </h1>

            <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo e($message['type']); ?> alert-dismissible fade show" role="alert">
                <?php echo e($message['text']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo url('contato'); ?>" novalidate class="needs-validation">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token'] ?? ''); ?>">

                <!-- Honeypot (campo oculto para bots) -->
                <div style="display: none;">
                    <label for="website">Website (não preencher):</label>
                    <input type="text" id="website" name="website" style="display: none;">
                </div>

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome *</label>
                    <input type="text" class="form-control" id="nome" name="nome" required 
                           value="<?php echo isset($_POST['nome']) ? e($_POST['nome']) : ''; ?>"
                           placeholder="Seu nome">
                    <div class="invalid-feedback">Campo obrigatório.</div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>"
                           placeholder="seu@email.com">
                    <div class="invalid-feedback">Email válido é obrigatório.</div>
                </div>

                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone (opcional)</label>
                    <input type="tel" class="form-control" id="telefone" name="telefone" 
                           value="<?php echo isset($_POST['telefone']) ? e($_POST['telefone']) : ''; ?>"
                           placeholder="(11) 99999-9999">
                </div>

                <div class="mb-3">
                    <label for="assunto" class="form-label">Assunto *</label>
                    <select class="form-select" id="assunto" name="assunto" required>
                        <option value="">-- Selecione um assunto --</option>
                        <option value="duvida" <?php echo isset($_POST['assunto']) && $_POST['assunto'] === 'duvida' ? 'selected' : ''; ?>>Dúvida</option>
                        <option value="apresentacao" <?php echo isset($_POST['assunto']) && $_POST['assunto'] === 'apresentacao' ? 'selected' : ''; ?>>Solicitação de Apresentação</option>
                        <option value="colaboracao" <?php echo isset($_POST['assunto']) && $_POST['assunto'] === 'colaboracao' ? 'selected' : ''; ?>>Proposta de Colaboração</option>
                        <option value="outro" <?php echo isset($_POST['assunto']) && $_POST['assunto'] === 'outro' ? 'selected' : ''; ?>>Outro</option>
                    </select>
                    <div class="invalid-feedback">Selecione um assunto.</div>
                </div>

                <div class="mb-3">
                    <label for="mensagem" class="form-label">Mensagem *</label>
                    <textarea class="form-control" id="mensagem" name="mensagem" rows="5" required 
                              placeholder="Sua mensagem..."><?php echo isset($_POST['mensagem']) ? e($_POST['mensagem']) : ''; ?></textarea>
                    <div class="invalid-feedback">Mensagem obrigatória.</div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-send"></i> Enviar Mensagem
                </button>
            </form>

            <div class="mt-4 p-3 bg-light rounded">
                <h5 class="mb-2">
                    <i class="bi bi-telephone"></i> Outras Formas de Contato
                </h5>
                <p class="mb-1">
                    <strong>Email:</strong><br>
                    <a href="mailto:<?php echo e(CONTACT_EMAIL); ?>"><?php echo e(CONTACT_EMAIL); ?></a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Validation JS -->
<script>
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
