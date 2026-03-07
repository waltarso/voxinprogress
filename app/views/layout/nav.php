<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?php echo url('home'); ?>">
            <img src="<?php echo asset('img/vip-logo-transp.png'); ?>" alt="vip" height="30" class="d-inline-block align-text-top me-2 logo-img">
            <?php echo e(SITE_NAME); ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page === 'home' ? 'active' : ''); ?>" href="<?php echo url('home'); ?>">
                        <i class="bi bi-house"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page === 'arranjos' ? 'active' : ''); ?>" href="<?php echo url('arranjos'); ?>">
                        <i class="bi bi-collection-play"></i> Músicas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page === 'colaboradores' ? 'active' : ''); ?>" href="<?php echo url('colaboradores'); ?>">
                        <i class="bi bi-person-lines-fill"></i> Colaboradores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page === 'agenda' ? 'active' : ''); ?>" href="<?php echo url('agenda'); ?>">
                        <i class="bi bi-calendar-event"></i> Agenda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page === 'sobre' ? 'active' : ''); ?>" href="<?php echo url('sobre'); ?>">
                        <i class="bi bi-book"></i> Sobre o vip
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
