<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('home'); ?>">Home</a></li>
            <li class="breadcrumb-item active">História</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <?php 
                        // determine page key (fallback to 'historia' when not set)
                        $pageKey = isset($current_page) ? $current_page : 'historia';
                        // choose page-specific markdown if available
                        $mdPath = DATA_DIR . '/md/' . $pageKey . '.md';
                        if (file_exists($mdPath)) {
                            echo render_markdown_file($mdPath);
                        } else {
                            // fallback: show any HTML files or all MD pages
                            $htmlFiles = glob(DATA_DIR . '/*.html');
                            if (!empty($htmlFiles)) {
                                foreach ($htmlFiles as $file) {
                                    echo file_get_contents($file);
                                }
                            } else {
                                // last resort render all md in folder
                                $mdFiles = glob(DATA_DIR . '/md/*.md');
                                if (!empty($mdFiles)) {
                                    foreach ($mdFiles as $file) {
                                        echo render_markdown_file($file);
                                    }
                                } else {
                                    echo '<p class="text-muted">Conteúdo não disponível.</p>';
                                }
                            }
                        }
                    ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-book"></i> Saiba Mais
                    </h5>
                    <p class="text-muted small mb-3">
                        A história do VIP é marcada por dedicação, criatividade e paixão pela música vocal.
                    </p>
                    <hr>
                    <p class="mb-2">
                        <a href="<?php echo url('cantores'); ?>" class="btn btn-sm btn-outline-primary w-100 mb-2">
                            <i class="bi bi-people"></i> Conheça os Cantores
                        </a>
                    </p>
                    <p class="mb-2">
                        <a href="<?php echo url('arranjos'); ?>" class="btn btn-sm btn-outline-primary w-100 mb-2">
                            <i class="bi bi-collection-play"></i> Veja Nossas Músicas
                        </a>
                    </p>
                    <p>
                        <a href="<?php echo url('agenda'); ?>" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-calendar-event"></i> Próximos Shows
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
