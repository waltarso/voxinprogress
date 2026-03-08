<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('home'); ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('apoio'); ?>">Patrocinadores e apoio cultural</a></li>
            <li class="breadcrumb-item active"><?php echo e($apoio['nome']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-start">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <?php $fotoUrl = apoio_photo_url($apoio); ?>
                                <?php if (!empty($fotoUrl)): ?>
                                    <div class="apoio-logo-frame apoio-logo-frame-lg mx-auto">
                                        <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($apoio['nome']); ?>" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php else: ?>
                                    <i class="bi bi-building" style="font-size: 6rem; color: #667eea;"></i>
                                <?php endif; ?>
                            </div>
                            <h1 class="h3"><?php echo e($apoio['nome']); ?></h1>
                            <p class="badge bg-primary mb-2" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                <?php echo e($apoio['categoria'] ?? 'Apoio cultural'); ?>
                            </p>
                            <?php if (($apoio['status'] ?? '') !== 'ativo'): ?>
                            <p class="text-muted small mb-0">Cota em aberto para novos parceiros.</p>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-8">
                            <?php echo apoio_bio_html($apoio); ?>

                            <?php $profileLinks = apoio_profile_links($apoio); ?>
                            <?php if (!empty($profileLinks)): ?>
                            <h5 class="mb-3">Links</h5>
                            <div class="d-flex gap-2 mb-3 flex-wrap">
                                <?php foreach ($profileLinks as $link): ?>
                                <a href="<?php echo e($link['url']); ?>" class="btn btn-outline-primary btn-sm" <?php echo (strpos($link['url'], 'mailto:') === 0 ? '' : 'target="_blank" rel="noopener noreferrer"'); ?>>
                                    <i class="bi bi-<?php echo e($link['icon']); ?>"></i> <?php echo e($link['titulo']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <p class="mb-0">
                                <a href="mailto:<?php echo e(CONTACT_EMAIL); ?>?subject=Apoiar%20o%20vip" class="btn btn-primary">
                                    <i class="bi bi-megaphone"></i> Quero apoiar o vip
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
