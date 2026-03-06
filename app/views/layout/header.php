<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo asset('img/favicon.png'); ?>" type="image/png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS (cache-busted by file mtime) -->
    <?php
    $cssPath = dirname(__DIR__, 3) . '/assets/css/site.css';
    $cssVersion = file_exists($cssPath) ? (string) filemtime($cssPath) : (string) time();
    ?>
    <link rel="stylesheet" href="<?php echo asset('css/site.css') . '?v=' . urlencode($cssVersion); ?>">
</head>
<body>
    <div class="d-flex flex-column min-vh-100">
        <?php include VIEWS_DIR . '/layout/nav.php'; ?>
        
        <main class="flex-grow-1">
