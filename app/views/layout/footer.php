        </main>
        
        <footer class="mt-5 py-4 bg-dark text-white text-center border-top">
            <div class="container-fluid">
                <p class="mb-2">
                    <strong><?php echo e(SITE_NAME); ?></strong> - <?php echo e(SITE_TAGLINE); ?>
                </p>
                <p class="mb-3 text-muted">
                    <i class="bi bi-envelope"></i>
                    <a href="mailto:<?php echo e(CONTACT_EMAIL); ?>" class="text-white-50 text-decoration-none">
                        <?php echo e(CONTACT_EMAIL); ?>
                    </a>
                </p>
                <p class="text-muted small mb-0">
                    &copy; <?php echo date('Y'); ?> VIP - Vox in Progress. Todos os direitos reservados.
                </p>
            </div>
        </footer>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
