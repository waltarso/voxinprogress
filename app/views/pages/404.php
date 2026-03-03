<div class="container py-5">
    <div class="row justify-content-center py-5">
        <div class="col-md-6 text-center">
            <div style="font-size: 5rem; color: #667eea; margin-bottom: 20px;">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <h1 class="display-4 fw-bold mb-3">404</h1>
            <h2 class="h3 mb-4">Página Não Encontrada</h2>
            <p class="lead text-muted mb-4">
                Desculpe, a página que você está procurando não existe ou foi movida.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="<?php echo url('home'); ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-house"></i> Voltar à Home
                </a>
                <a href="<?php echo url('arranjos'); ?>" class="btn btn-outline-primary btn-lg">
                    <i class="bi bi-collection-play"></i> Ver Músicas
                </a>
            </div>
        </div>
    </div>
</div>

<?php include VIEWS_DIR . '/layout/footer.php'; ?>
