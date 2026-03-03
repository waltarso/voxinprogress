<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('home'); ?>">Home</a></li>
            <li class="breadcrumb-item active">Cantores</li>
        </ol>
    </nav>

    <h1 class="mb-4">
        <i class="bi bi-person-lines-fill"></i> Nossa Equipe Vocal
    </h1>

    <?php if (!empty($cantores)): ?>
    <div class="row g-4">
        <?php foreach ($cantores as $cantor): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                            <?php $fotoUrl = cantor_photo_url($cantor); ?>
                            <?php if (!empty($fotoUrl)): ?>
                                <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($cantor['nome']); ?>" class="img-fluid rounded-circle" style="width: 96px; height: 96px; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-person-circle" style="font-size: 4rem; color: #667eea;"></i>
                            <?php endif; ?>
                    </div>
                    <h5 class="card-title"><?php echo e($cantor['nome']); ?></h5>
                    <p class="badge bg-primary mb-3"><?php echo e($cantor['voz']); ?></p>
                    <p class="card-text text-muted small"><?php echo e($cantor['bioCurta']); ?></p>
                    
                    <a href="<?php echo url('cantor', ['id' => $cantor['id']]); ?>" class="btn btn-sm btn-primary mt-3">
                        Ver Perfil Completo
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Nenhum cantor cadastrado.
    </div>
    <?php endif; ?>
</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
