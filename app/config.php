<?php
/**
 * VIP (Vox in Progress) - Configuração do Site
 */

// Informações do site
define('SITE_NAME', 'Vox in Progress');
define('SITE_TAGLINE', 'Arranjos e Performances Vocais');
define('CONTACT_EMAIL', 'vip@voxinprogress.vip');

// URL da base de dados de arquivos (relative path)
// o site pode ficar sob a raiz ou num subdiretório (ex: /vip). Para não
// precisar editar esta constante a cada mudança, calculamos automaticamente
// o prefixo usando SCRIPT_NAME.
$baseAcervo = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($baseAcervo === '/' || $baseAcervo === '\\') {
    $baseAcervo = '';
}
define('ACERVO_BASE_URL', $baseAcervo . '/acervo/');

// URLs amigáveis: o helper `url()` irá gerar rotas sem querystring quando essa
// opção estiver ativa. Requer mod_rewrite no Apache (arquivo .htaccess). O
// servidor embutido do PHP (`php -S`) **não respeita** .htaccess, portanto
// desabilitamos automaticamente quando estivermos usando o CLI server.

if (php_sapi_name() === 'cli-server') {
    // desenvolvimento local via "php -S"
    define('USE_PRETTY_URLS', false);
} else {
    define('USE_PRETTY_URLS', true);
}

// Habilitar envio de email (mail())
define('ENABLE_EMAIL', false);

// Critério dos "Últimos Arranjos" na home:
// - 'file_order' : usa a ordem do arranjos.json (padrão atual)
// - 'year_desc'  : ordena por ano (mais recentes primeiro)
define('HOME_LATEST_CRITERIA', 'file_order');

// Caminhos absolutos (baseado em dirname(__FILE__))
define('APP_DIR', __DIR__);
define('DATA_DIR', APP_DIR . '/data');
define('VIEWS_DIR', APP_DIR . '/views');

// Timezone
date_default_timezone_set('America/Sao_Paulo');
