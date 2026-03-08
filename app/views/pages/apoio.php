<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('home'); ?>">Home</a></li>
            <li class="breadcrumb-item active">Patrocinadores e apoio cultural</li>
        </ol>
    </nav>

    <section class="sponsors-section rounded-4 p-3 p-md-4 mb-4">
        <div class="row align-items-start g-3 g-lg-4">
            <div class="col-lg-8">
                <h1 class="h2 mt-2 mb-3">Patrocinadores e apoio cultural que fortalecem o projeto</h1>
                <p class="text-muted mb-3">
                    Estes são parceiros que caminham com o vip e ajudam a manter vivo
                    nosso trabalho artístico e cultural.
                </p>
                <p class="text-muted mb-0">
                    Cada parceria viabiliza criação, repertório, produção e circulação.
                    Se sua marca também acredita nisso, vamos conversar.
                </p>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-center">
                        <h2 class="h6 text-uppercase text-muted mb-2">Apoie o vip</h2>
                        <?php if (!empty($apoiosAtivos)): ?>
                            <p class="small text-muted mb-3">
                                Hoje já contamos com <?php echo count($apoiosAtivos); ?> parceria(s) ativa(s).
                            </p>
                        <?php else: ?>
                            <p class="small text-muted mb-3">
                                Seu apoio pode abrir o próximo capítulo desta trajetória.
                            </p>
                        <?php endif; ?>
                        <a href="mailto:<?php echo e(CONTACT_EMAIL); ?>?subject=Apoiar%20o%20vip" class="btn btn-primary w-100">
                            <i class="bi bi-envelope-heart"></i> Quero apoiar o vip
                        </a>
                        <a href="<?php echo url('dossie'); ?>" class="btn btn-outline-primary w-100 mt-2">
                            <i class="bi bi-file-earmark-text"></i> Ver dossiê de patrocínio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($apoiosAtivos)): ?>
    <h2 class="h4 mb-3"><i class="bi bi-building"></i> Patrocinadores e apoiadores</h2>
    <div class="row g-4 mb-5">
        <?php foreach ($apoiosAtivos as $apoio): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php $fotoUrl = apoio_photo_url($apoio); ?>
                        <a href="<?php echo url('apoiador', ['id' => $apoio['id']]); ?>" aria-label="Ver pagina de <?php echo e($apoio['nome']); ?>">
                            <?php if (!empty($fotoUrl)): ?>
                                <div class="apoio-logo-frame">
                                    <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($apoio['nome']); ?>" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            <?php else: ?>
                                <i class="bi bi-building" style="font-size: 4rem; color: #667eea;"></i>
                            <?php endif; ?>
                        </a>
                    </div>
                    <h5 class="card-title mb-2">
                        <a href="<?php echo url('apoiador', ['id' => $apoio['id']]); ?>" class="text-decoration-none">
                            <?php echo e($apoio['nome']); ?>
                        </a>
                    </h5>
                    <p class="badge bg-dark mb-2"><?php echo e($apoio['categoria'] ?? 'Apoio cultural'); ?></p>
                    <p class="card-text text-muted small mb-0"><?php echo e($apoio['bioCurta'] ?? ''); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($apoiosEmAberto)): ?>
    <div class="row g-4 mb-4">
        <?php foreach ($apoiosEmAberto as $apoio): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php $fotoUrl = apoio_photo_url($apoio); ?>
                        <a href="<?php echo url('apoiador', ['id' => $apoio['id']]); ?>" aria-label="Ver pagina de <?php echo e($apoio['nome']); ?>">
                            <?php if (!empty($fotoUrl)): ?>
                                <div class="apoio-logo-frame">
                                    <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($apoio['nome']); ?>" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            <?php else: ?>
                                <i class="bi bi-stars" style="font-size: 4rem; color: #667eea;"></i>
                            <?php endif; ?>
                        </a>
                    </div>
                    <h5 class="card-title mb-2">
                        <a href="<?php echo url('apoiador', ['id' => $apoio['id']]); ?>" class="text-decoration-none">
                            <?php echo e($apoio['nome']); ?>
                        </a>
                    </h5>
                    <p class="badge bg-primary mb-2"><?php echo e($apoio['categoria'] ?? 'Apoio cultural'); ?></p>
                    <p class="card-text text-muted small mb-0"><?php echo e($apoio['bioCurta'] ?? ''); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
