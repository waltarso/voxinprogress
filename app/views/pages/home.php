<!-- Hero Section -->
<section class="py-2 text-white" style="background: #000;">
    <div class="container py-2">
        <div class="row align-items-center">
            <div class="col-lg-8 col-xl-7 text-center text-lg-start">
                <p class="lead mb-4"><?php echo e(SITE_TAGLINE); ?></p>
                <div class="d-flex gap-2 justify-content-center justify-content-lg-start flex-wrap">
                    <a href="<?php echo url('arranjos'); ?>" class="btn btn-light btn-lg">
                        <i class="bi bi-collection-play"></i> Ver Músicas
                    </a>
                    <a href="<?php echo url('agenda'); ?>" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-calendar-event"></i> Próximos Shows
                    </a>
                </div>
                <div class="d-flex gap-2 justify-content-center justify-content-lg-start flex-wrap mt-3">
                    <a href="https://www.instagram.com/vox_in_progress/" class="btn btn-sm btn-outline-light" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-instagram"></i> Instagram
                    </a>
                    <a href="https://www.facebook.com/voxinprogress" class="btn btn-sm btn-outline-light" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-facebook"></i> Facebook
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-xl-5 d-flex justify-content-center align-items-center mt-4 mt-lg-0">
                <img src="<?php echo asset('img/pub.png'); ?>" alt="vip" class="img-fluid" style="max-width: 420px; width: 100%; height: auto;">
            </div>
        </div>
    </div>
</section>

<!-- Seção de Últimas Músicas -->
<section class="py-5">
    <div class="container">
        <h2 class="mb-4" style="border-bottom: 3px solid #667eea; padding-bottom: 10px; display: inline-block;">
            <i class="bi bi-stars"></i> Últimas Músicas
        </h2>
        
        <?php if (!empty($ultimos_arranjos)): ?>
        <div class="row g-4">
            <?php foreach ($ultimos_arranjos as $arr): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 hover-card">
                    <div class="card-body">
                        <h5 class="card-title text-truncate"><?php echo e($arr['titulo']); ?></h5>
                        <p class="card-text text-muted small">
                            <strong>Artista:</strong> <?php echo e($arr['artistaOriginal']); ?>
                        </p>
                        <p class="card-text text-muted small">
                            <strong>Álbum:</strong> 
                            <?php 
                                $album = find_album($albums, $arr['albumId']);
                                echo $album ? e($album['titulo']) : e($arr['albumId']);
                            ?>
                        </p>
                        <?php if (isset($arr['ano'])): ?>
                        <p class="card-text text-muted small">
                            <strong>Ano:</strong> <?php echo e($arr['ano']); ?>
                        </p>
                        <?php endif; ?>
                        <a href="<?php echo url('arranjo', ['id' => $arr['id']]); ?>" class="btn btn-sm btn-primary mt-2">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted">Nenhuma música disponível.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Seção de Formação -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="mb-4" style="border-bottom: 3px solid #667eea; padding-bottom: 10px; display: inline-block;">
            <i class="bi bi-people"></i> Nossa Formação
        </h2>
        
        <?php if (!empty($cantores)): ?>
        <div class="row g-4">
            <?php foreach (array_slice($cantores, 0, 6) as $cantor): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <?php $fotoUrl = cantor_photo_url($cantor); ?>
                            <?php if (!empty($fotoUrl)): ?>
                                <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($cantor['nome']); ?>" class="img-fluid rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-person-circle" style="font-size: 3rem; color: #667eea;"></i>
                            <?php endif; ?>
                        </div>
                        <h5 class="card-title"><?php echo e($cantor['nome']); ?></h5>
                        <p class="badge bg-primary mb-2"><?php echo e($cantor['voz']); ?></p>
                        <p class="card-text small text-muted"><?php echo e($cantor['bioCurta']); ?></p>
                        <a href="<?php echo url('cantor', ['id' => $cantor['id']]); ?>" class="btn btn-sm btn-outline-primary">
                            Ver Perfil
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted">Nenhum cantor cadastrado.</p>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-dark text-white">
    <div class="container text-center">
        <h2 class="mb-3">Quer nos conhecer mais?</h2>
        <p class="lead mb-4">Conheça a trajetória e os bastidores do vip</p>
        <a href="<?php echo url('sobre'); ?>" class="btn btn-light btn-lg">
            <i class="bi bi-book"></i> Sobre o vip
        </a>
    </div>
</section>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
