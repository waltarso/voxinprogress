<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('home'); ?>">Home</a></li>
            <li class="breadcrumb-item active">Arranjos</li>
        </ol>
    </nav>

    <h1 class="mb-4">
        <i class="bi bi-collection-play"></i> Catálogo de Arranjos
    </h1>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form method="GET" action="<?php echo url('arranjos'); ?>">
                <div class="row g-2">
                    <div class="col-md-6">
                        <select name="album" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos os Álbuns</option>
                            <?php foreach ($albums as $album): ?>
                            <option value="<?php echo e($album['id']); ?>" <?php echo isset($albumId) && $albumId === $album['id'] ? 'selected' : ''; ?>>
                                <?php echo e($album['titulo']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="q" class="form-control" placeholder="Buscar por título..." 
                               value="<?php echo isset($q) ? e($q) : ''; ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <?php if (isset($albumId) || isset($q)): ?>
                        <a href="<?php echo url('arranjos'); ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Limpar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <?php if (!empty($arranjos)): ?>
    <div class="row g-4 arranjos-list">
        <?php foreach ($arranjos as $arranjo): ?>
        <div class="col-md-6 col-lg-4">
            <?php 
                $album = find_album($albums, $arranjo['albumId']);
                // decide URL da imagem cuidando de caminho em acervo
                $thumb = arranjo_image_url($arranjo, $album);
            ?>
            <div class="card h-100 shadow-sm border-0 hover-card">
                <?php if (!empty($thumb)): ?>
                    <img src="<?php echo e($thumb); ?>" class="card-img-top" alt="<?php echo e($arranjo['titulo']); ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title text-truncate"><?php echo e($arranjo['titulo']); ?></h5>
                    <p class="card-text text-muted small mb-2">
                        <strong>Artista:</strong> <?php echo e($arranjo['artistaOriginal']); ?>
                    </p>
                    <p class="card-text text-muted small mb-2">
                        <strong>Álbum:</strong>
                        <?php 
                            $album = find_album($albums, $arranjo['albumId']);
                            echo $album ? e($album['titulo']) : e($arranjo['albumId']);
                        ?>
                    </p>
                    
                    <?php if (isset($arranjo['duracao'])): ?>
                    <p class="card-text text-muted small mb-2">
                        <i class="bi bi-clock"></i> <?php echo e($arranjo['duracao']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if (isset($arranjo['dificuldade'])): ?>
                    <p class="card-text text-muted small mb-2">
                        <strong>Dificuldade:</strong> 
                        <?php for ($i = 0; $i < $arranjo['dificuldade']; $i++): ?>
                            <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                        <?php endfor; ?>
                    </p>
                    <?php endif; ?>
                    
                    <a href="<?php echo url('arranjo', ['id' => $arranjo['id']]); ?>" class="btn btn-sm btn-primary mt-3">
                        Ver Detalhes <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-4 text-muted">
        <p>Mostrando <?php echo count($arranjos); ?> arranjo(s)</p>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Nenhum arranjo encontrado com os critérios especificados.
    </div>
    <?php endif; ?>
</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
