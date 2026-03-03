<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('home'); ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('cantores'); ?>">Cantores</a></li>
            <li class="breadcrumb-item active"><?php echo e($cantor['nome']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-start">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="bi bi-person-circle" style="font-size: 6rem; color: #667eea;"></i>
                            </div>
                            <h1><?php echo e($cantor['nome']); ?></h1>
                            <p class="badge bg-primary mb-3" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                <?php echo e($cantor['voz']); ?>
                            </p>
                        </div>

                        <div class="col-md-8">
                            <h3 class="mb-3">Sobre</h3>
                            <p class="lead"><?php echo e($cantor['bioCurta']); ?></p>

                            <?php if (isset($cantor['links']) && is_array($cantor['links']) && count($cantor['links']) > 0): ?>
                            <h5 class="mb-3">Links</h5>
                            <div class="d-flex gap-2 mb-3">
                                <?php foreach ($cantor['links'] as $link): ?>
                                <a href="<?php echo e($link['url']); ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-box-arrow-up-right"></i> <?php echo e($link['titulo']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-info-circle"></i> Informações
                    </h5>
                    <p>
                        <strong>Voz:</strong> <?php echo e($cantor['voz']); ?>
                    </p>
                    <hr>
                    <p class="mb-2">
                        <a href="<?php echo url('cantores'); ?>" class="btn btn-sm btn-outline-primary w-100 mb-2">
                            <i class="bi bi-arrow-left"></i> Voltar aos Cantores
                        </a>
                    </p>
                    <p>
                        <a href="<?php echo url('home'); ?>" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
