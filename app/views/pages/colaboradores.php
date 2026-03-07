<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('home'); ?>">Home</a></li>
            <li class="breadcrumb-item active">Colaboradores</li>
        </ol>
    </nav>

    <h1 class="mb-4">
        <i class="bi bi-person-lines-fill"></i> Colaboradores do vip
    </h1>

    <?php if (!empty($participantesCantores)): ?>
    <h3 class="h4 mb-3">Cantores</h3>
    <div class="row g-4">
        <?php foreach ($participantesCantores as $cantor): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                            <?php $fotoUrl = cantor_photo_url($cantor); ?>
                            <a href="<?php echo url('cantor', ['id' => $cantor['id']]); ?>" aria-label="Ver perfil de <?php echo e($cantor['nome']); ?>">
                            <?php if (!empty($fotoUrl)): ?>
                                <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($cantor['nome']); ?>" class="img-fluid rounded-circle" style="width: 96px; height: 96px; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-person-circle" style="font-size: 4rem; color: #667eea;"></i>
                            <?php endif; ?>
                            </a>
                    </div>
                    <h5 class="card-title">
                        <a href="<?php echo url('cantor', ['id' => $cantor['id']]); ?>" class="text-decoration-none">
                            <?php echo e($cantor['nome']); ?>
                        </a>
                    </h5>
                    <p class="badge bg-primary mb-3"><?php echo e($cantor['voz']); ?></p>
                    <p class="card-text text-muted small"><?php echo e($cantor['bioCurta']); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Nenhum participante cantor cadastrado.
    </div>
    <?php endif; ?>

    <?php if (!empty($participantesApoio)): ?>
    <h3 class="h4 mt-4 mb-3">Apoio</h3>
    <div class="row g-4">
        <?php foreach ($participantesApoio as $cantor): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php $fotoUrl = cantor_photo_url($cantor); ?>
                        <a href="<?php echo url('cantor', ['id' => $cantor['id']]); ?>" aria-label="Ver perfil de <?php echo e($cantor['nome']); ?>">
                        <?php if (!empty($fotoUrl)): ?>
                            <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($cantor['nome']); ?>" class="img-fluid rounded-circle" style="width: 96px; height: 96px; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: #667eea;"></i>
                        <?php endif; ?>
                        </a>
                    </div>
                    <h5 class="card-title">
                        <a href="<?php echo url('cantor', ['id' => $cantor['id']]); ?>" class="text-decoration-none">
                            <?php echo e($cantor['nome']); ?>
                        </a>
                    </h5>
                    <p class="badge bg-info mb-2"><?php echo e($cantor['voz']); ?></p>
                    <p class="card-text text-muted small mb-0"><?php echo e($cantor['bioCurta']); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($colaboradoresEventuais)): ?>
    <hr class="my-5">
    <h2 class="mb-4">
        <i class="bi bi-people"></i> Colaboradores 
    </h2>

    <div class="row g-4">
        <?php foreach ($colaboradoresEventuais as $cantor): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php $fotoUrl = cantor_photo_url($cantor); ?>
                        <a href="<?php echo url('cantor', ['id' => $cantor['id']]); ?>" aria-label="Ver perfil de <?php echo e($cantor['nome']); ?>">
                        <?php if (!empty($fotoUrl)): ?>
                            <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($cantor['nome']); ?>" class="img-fluid rounded-circle" style="width: 96px; height: 96px; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: #667eea;"></i>
                        <?php endif; ?>
                        </a>
                    </div>
                    <h5 class="card-title">
                        <a href="<?php echo url('cantor', ['id' => $cantor['id']]); ?>" class="text-decoration-none">
                            <?php echo e($cantor['nome']); ?>
                        </a>
                    </h5>
                    <p class="badge bg-secondary mb-2"><?php echo e($cantor['voz']); ?></p>
                    <p class="card-text text-muted small mb-2"><?php echo e($cantor['bioCurta']); ?></p>
                    <p class="text-muted small mb-0">
                        <?php
                        $entrada = trim((string) ($cantor['entrada'] ?? ''));
                        $saida = trim((string) ($cantor['saida'] ?? ''));
                        echo e($entrada !== '' ? format_member_date($entrada) : '?') . ' - ' . e($saida !== '' ? format_member_date($saida) : '?');
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($parceiros)): ?>
    <hr class="my-5">
    <h2 class="mb-4">
        <i class="bi bi-diagram-3"></i> Parceiros
    </h2>

    <div class="row g-4">
        <?php foreach ($parceiros as $parceiro): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php $fotoUrl = cantor_photo_url($parceiro); ?>
                        <a href="<?php echo url('cantor', ['id' => $parceiro['id']]); ?>" aria-label="Ver perfil de <?php echo e($parceiro['nome']); ?>">
                            <?php if (!empty($fotoUrl)): ?>
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle border bg-white" style="width: 96px; height: 96px; overflow: hidden;">
                                    <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($parceiro['nome']); ?>" class="img-fluid" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                            <?php else: ?>
                                <i class="bi bi-building" style="font-size: 4rem; color: #667eea;"></i>
                            <?php endif; ?>
                        </a>
                    </div>
                    <h5 class="card-title">
                        <a href="<?php echo url('cantor', ['id' => $parceiro['id']]); ?>" class="text-decoration-none">
                            <?php echo e($parceiro['nome']); ?>
                        </a>
                    </h5>
                    <p class="badge bg-dark mb-2"><?php echo e(trim((string) ($parceiro['voz'] ?? '')) !== '' ? $parceiro['voz'] : 'Parceiro'); ?></p>
                    <p class="card-text text-muted small mb-2"><?php echo e($parceiro['bioCurta'] ?? ''); ?></p>
                    <p class="text-muted small mb-0">
                        <?php
                        $entrada = trim((string) ($parceiro['entrada'] ?? ''));
                        echo 'Desde: ' . e($entrada !== '' ? format_member_date($entrada) : '?');
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>