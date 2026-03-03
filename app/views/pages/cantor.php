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
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-start">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <?php $fotoUrl = cantor_photo_url($cantor); ?>
                                <?php if (!empty($fotoUrl)): ?>
                                    <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($cantor['nome']); ?>" class="img-fluid rounded-circle" style="width: 180px; height: 180px; object-fit: cover;">
                                <?php else: ?>
                                    <i class="bi bi-person-circle" style="font-size: 6rem; color: #667eea;"></i>
                                <?php endif; ?>
                            </div>
                            <h1><?php echo e($cantor['nome']); ?></h1>
                            <p class="badge bg-primary mb-3" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                <?php echo e($cantor['voz']); ?>
                            </p>
                        </div>

                        <div class="col-md-8">
                            <h3 class="mb-3">Sobre</h3>
                            <?php echo cantor_bio_html($cantor); ?>

                            <?php $profileLinks = cantor_profile_links($cantor); ?>
                            <?php if (!empty($profileLinks)): ?>
                            <h5 class="mb-3">Links</h5>
                            <div class="d-flex gap-2 mb-3">
                                <?php foreach ($profileLinks as $link): ?>
                                <a href="<?php echo e($link['url']); ?>" class="btn btn-outline-primary btn-sm" <?php echo (strpos($link['url'], 'mailto:') === 0 ? '' : 'target="_blank" rel="noopener noreferrer"'); ?>>
                                    <i class="bi bi-<?php echo e($link['icon']); ?>"></i> <?php echo e($link['titulo']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
