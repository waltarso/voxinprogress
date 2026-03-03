<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('home'); ?>">Home</a></li>
            <li class="breadcrumb-item active">Agenda</li>
        </ol>
    </nav>

    <h1 class="mb-4">
        <i class="bi bi-calendar-event"></i> Agenda de Eventos
    </h1>

    <?php 
        // Separar próximos e passados
        $hoje = new DateTime();
        $proximos = [];
        $passados = [];
        
        foreach ($agenda as $evento) {
            $data = DateTime::createFromFormat('Y-m-d', $evento['data']);
            if ($data === false) continue;
            
            if ($data >= $hoje) {
                $proximos[] = $evento;
            } else {
                $passados[] = $evento;
            }
        }
        
        // Ordenar proximos por data
        usort($proximos, function ($a, $b) {
            return strcmp($a['data'], $b['data']);
        });
        
        // Ordenar passados por data descendente
        usort($passados, function ($a, $b) {
            return strcmp($b['data'], $a['data']);
        });
    ?>

    <!-- Próximos Eventos -->
    <h3 class="mb-3" style="border-bottom: 3px solid #667eea; padding-bottom: 10px; display: inline-block;">
        <i class="bi bi-calendar-check"></i> Próximos Eventos
    </h3>

    <?php if (!empty($proximos)): ?>
    <div class="row g-3 mb-5">
        <?php foreach ($proximos as $evento): ?>
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-left: 5px solid #667eea;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div style="font-size: 2.5rem; color: #667eea; font-weight: bold;">
                                    <?php echo date('d', strtotime($evento['data'])); ?>
                                </div>
                                <div style="font-size: 1.2rem; color: #666;">
                                    <?php echo date('M', strtotime($evento['data'])); ?>
                                </div>
                                <div style="color: #999; font-size: 0.9rem;">
                                    <?php echo date('Y', strtotime($evento['data'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h5 class="card-title mb-2"><?php echo e($evento['titulo']); ?></h5>
                            
                            <?php if (isset($evento['hora'])): ?>
                            <p class="card-text text-muted mb-1">
                                <i class="bi bi-clock"></i> <?php echo e($evento['hora']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if (isset($evento['local'])): ?>
                            <p class="card-text text-muted mb-1">
                                <i class="bi bi-geo-alt"></i> <?php echo e($evento['local']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if (isset($evento['descricao'])): ?>
                            <p class="card-text"><?php echo e($evento['descricao']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (isset($evento['link'])): ?>
                            <a href="<?php echo e($evento['link']); ?>" class="btn btn-sm btn-primary" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-box-arrow-up-right"></i> Mais Informações
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info mb-5">
        <i class="bi bi-info-circle"></i>
        Nenhum evento próximo agendado.
    </div>
    <?php endif; ?>

    <!-- Eventos Passados -->
    <h3 class="mb-3" style="border-bottom: 3px solid #999; padding-bottom: 10px; display: inline-block;">
        <i class="bi bi-calendar-x"></i> Eventos Passados
    </h3>

    <?php if (!empty($passados)): ?>
    <div class="row g-3">
        <?php foreach (array_slice($passados, 0, 5) as $evento): ?>
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-left: 5px solid #ccc; opacity: 0.8;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div style="font-size: 2.5rem; color: #999; font-weight: bold;">
                                    <?php echo date('d', strtotime($evento['data'])); ?>
                                </div>
                                <div style="font-size: 1.2rem; color: #999;">
                                    <?php echo strftime('%b', strtotime($evento['data'])); ?>
                                </div>
                                <div style="color: #ccc; font-size: 0.9rem;">
                                    <?php echo date('Y', strtotime($evento['data'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h5 class="card-title mb-2 text-muted"><?php echo e($evento['titulo']); ?></h5>
                            
                            <?php if (isset($evento['local'])): ?>
                            <p class="card-text text-muted small mb-0">
                                <i class="bi bi-geo-alt"></i> <?php echo e($evento['local']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (count($passados) > 5): ?>
    <p class="text-muted mt-3">... e mais <?php echo count($passados) - 5; ?> evento(s) anterior(es)</p>
    <?php endif; ?>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Nenhum evento passado registrado.
    </div>
    <?php endif; ?>
</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
