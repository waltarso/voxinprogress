        </main>
        
        <footer class="mt-5 py-4 bg-dark text-white text-center border-top">
            <div class="container-fluid">
                <p class="mb-2">
                    <img src="<?php echo asset('img/vip-logo-transp.png'); ?>" alt="VIP" style="height:2em;" class="d-inline-block align-text-bottom me-1"> 
                    <strong><?php echo e(SITE_NAME); ?></strong> - <?php echo e(SITE_TAGLINE); ?>
                </p>
                <p class="mb-3 text-muted">
                    <i class="bi bi-envelope"></i>
                    <a href="mailto:<?php echo e(CONTACT_EMAIL); ?>" class="text-white-50 text-decoration-none">
                        <?php echo e(CONTACT_EMAIL); ?>
                    </a>
                </p>
                <p class="text-white-50 small mb-0">
                    &copy; <?php echo date('Y'); ?> 
                    vip - Vox in Progress. Conteúdo livre; sinta-se à vontade para usar, copiar e modificar.
                </p>
            </div>
        </footer>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
