<?php
/**
 * VIP (Vox in Progress) - Configuração do Site
 */

// Informações do site
define('SITE_NAME', 'VIP - Vox in Progress');
define('SITE_TAGLINE', 'Arranjos e Performances Vocais');
define('CONTACT_EMAIL', 'contato@voxinprogress.vip');

// URL da base de dados de arquivos (relative path)
// mudou: acervo agora fica sob /vip/acervo no servidor
// ajuste conforme a configuração do seu ambiente
define('ACERVO_BASE_URL', '/vip/acervo/');

// Habilitar envio de email (mail())
define('ENABLE_EMAIL', false);

// Caminhos absolutos (baseado em dirname(__FILE__))
define('APP_DIR', __DIR__);
define('DATA_DIR', APP_DIR . '/data');
define('VIEWS_DIR', APP_DIR . '/views');

// Timezone
date_default_timezone_set('America/Sao_Paulo');
