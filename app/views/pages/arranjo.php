<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('home'); ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('arranjos'); ?>">Músicas</a></li>
            <?php if ($album): ?>
            <li class="breadcrumb-item"><a href="<?php echo url('arranjos', ['album' => $album['id']]); ?>"><?php echo e($album['titulo']); ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo e($arranjo['titulo']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Metadados -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body">
                    <h1 class="card-title mb-3"><?php echo e($arranjo['titulo']); ?></h1>
                    <?php if (!empty($arranjo['image'])): ?>
                        <p class="text-center mb-3">
                            <img src="<?php echo e(arranjo_image_url($arranjo, $album)); ?>" alt="<?php echo e($arranjo['titulo']); ?>" class="img-fluid rounded">
                        </p>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Artista Original:</strong><br>
                                <?php echo e($arranjo['artistaOriginal']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Álbum:</strong><br>
                                <a href="<?php echo url('arranjos', ['album' => $arranjo['albumId']]); ?>">
                                    <?php echo e($album['titulo']); ?>
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <?php if (isset($arranjo['ano'])): ?>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Ano:</strong><br>
                                <?php echo e($arranjo['ano']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($arranjo['duracao'])): ?>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="bi bi-clock"></i> Duração:</strong><br>
                                <?php echo e($arranjo['duracao']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($arranjo['dificuldade'])): ?>
                    <p class="mb-2">
                        <strong>Dificuldade:</strong><br>
                        <?php for ($i = 0; $i < $arranjo['dificuldade']; $i++): ?>
                            <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                        <?php endfor; ?>
                        <span class="text-muted">(<?php echo e($arranjo['dificuldade']); ?>/5)</span>
                    </p>
                    <?php endif; ?>

                    <?php if (isset($arranjo['observacoes']) && $arranjo['observacoes']): ?>
                    <div class="alert alert-info">
                        <strong>Notas:</strong><br>
                        <?php echo e($arranjo['observacoes']); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Downloads -->
            <h3 class="mb-3">
                <i class="bi bi-download"></i> Arquivos Disponíveis
            </h3>

            <?php if (!empty($arranjo['files'])): ?>
                <?php 
                    $grouped = group_files_by_type($arranjo['files']);
                    $type_labels = [
                        'pdf' => 'Partituras (PDF)',
                        'sib' => 'Sibelius',
                        'mp3' => 'Áudios (MP3)',
                        'mp4' => 'Vídeos (MP4)',
                        'midi' => 'MIDI',
                        'other' => 'Outros Arquivos'
                    ];
                ?>

                <?php foreach ($grouped as $type => $files): ?>
                <div class="card shadow-sm mb-3 border-0">
                    <div class="card-header bg-light">
                        <strong><?php echo isset($type_labels[$type]) ? $type_labels[$type] : ucfirst($type); ?></strong>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($files as $file): ?>
                        <?php 
                            $fileUrl = build_acervo_url($arranjo['storagePath'], $file['relpath']);
                            if (!$fileUrl) continue; // Skip invalid paths
                        ?>
                        <a href="<?php echo e($fileUrl); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank" rel="noopener noreferrer">
                            <span>
                                <?php echo file_icon($type); ?> 
                                <strong><?php echo e($file['label']); ?></strong>
                            </span>
                            <i class="bi bi-download"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Nenhum arquivo disponível para esta música.
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top arranjo-sidebar" style="top: 70px; z-index:102;">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-info-circle"></i> Sobre esta Música
                    </h5>
                    <p class="text-muted small">
                        Esta música faz parte da série <strong><?php echo e($album['titulo']); ?></strong>.
                    </p>
                    <?php
                        $albumImage = null;
                        if (!empty($album['image'])) {
                            if (preg_match('#^(img/|assets/)#', $album['image'])) {
                                $albumImage = asset($album['image']);
                            } else {
                                $albumImage = rtrim(ACERVO_BASE_URL, '/') . '/' . ltrim($album['image'], '/');
                            }
                        }
                    ?>
                    <?php if (!empty($albumImage)): ?>
                        <p class="text-center mb-2">
                            <img src="<?php echo e($albumImage); ?>" alt="Capa de <?php echo e($album['titulo']); ?>" class="img-fluid rounded">
                        </p>
                    <?php endif; ?>
                    <p class="text-muted small">
                        <?php echo e($album['descricao']); ?>
                    </p>
                    
                    <hr>

                    <p class="mb-2">
                        <a href="<?php echo url('arranjos', ['album' => $arranjo['albumId']]); ?>" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-collection-play"></i> Ver Todos de <?php echo e($album['titulo']); ?>
                        </a>
                    </p>
                    <p>
                        <a href="<?php echo url('arranjos'); ?>" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-arrow-left"></i> Voltar ao Repertório
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
