<?php
/**
 * vip (Vox in Progress) - Router Principal
 * Arquivo: index.php
 */

// Iniciar session
session_start();

// Carregar configuração e helpers
require 'app/config.php';
require 'app/helpers.php';

// Função para gerar CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Carregar dados JSON
$albums = load_json(DATA_DIR . '/albums.json');
$arranjos = load_json(DATA_DIR . '/arranjos.json');
$colaboradoresData = load_json(DATA_DIR . '/colaboradores.json');
$apoiosData = load_json(DATA_DIR . '/apoios.json');
$cantores = $colaboradoresData;
$agenda = load_json(DATA_DIR . '/agenda.json');

// Whitelist de páginas permitidas (fixas)
$allowed_pages = ['home', 'arranjos', 'arranjo', 'sobre', 'apoio', 'apoiador', 'colaboradores', 'cantor', 'agenda'];

// Obter página da query string
$p = $_GET['p'] ?? 'home';

// Se nenhum parâmetro `p` foi passado explicitamente, permita que parâmetros
// como `album`, `q` ou `id` determinarem qual página deve ser exibida.
// Isso evita que `index.php?album=...` fique exibindo a `home` por engano.
if (!array_key_exists('p', 
    $_GET) ) {
    if (isset($_GET['id']) && $_GET['id'] !== '') {
        $p = 'arranjo';
    } elseif (isset($_GET['album']) || isset($_GET['q'])) {
        $p = 'arranjos';
    }
}

// aliases de rotas antigas para manter compatibilidade de links existentes
if ($p === 'historia') {
    $p = 'sobre';
} elseif ($p === 'cantores') {
    $p = 'colaboradores';
}

// Validação inicial: nome válido e ou explícito ou corresponde a markdown disponível
if (!preg_match('/^[a-z0-9_]+$/i', $p)) {
    $invalid = true;
} else {
    $invalid = false;
    if (!in_array($p, $allowed_pages)) {
        // verificar existência de arquivo markdown
        $mdPath = DATA_DIR . '/md/' . $p . '.md';
        if (!file_exists($mdPath)) {
            $invalid = true;
        }
    }
}

if ($invalid) {
    // Página inválida
    $current_page = '404';
    include VIEWS_DIR . '/layout/header.php';
    include VIEWS_DIR . '/pages/404.php';
    exit;
}

// Variável para ativa na nav
$current_page = $p;

// Incluir header
include VIEWS_DIR . '/layout/header.php';

// ===== HOME =====
if ($p === 'home') {
    $pageTitle = 'Home';
    [$participantesCantores] = split_colaboradores_por_funcao($colaboradoresData);
    $cantores = $participantesCantores;

    // Exibir apenas arranjos com ordem definida no campo `homeOrder`
    $arranjos_home = array_values(array_filter($arranjos, function ($arr) {
        if (!array_key_exists('homeOrder', $arr)) {
            return false;
        }

        $value = $arr['homeOrder'];
        return $value !== null && $value !== '' && is_numeric($value);
    }));

    // Ordenar em ordem crescente de homeOrder
    usort($arranjos_home, function ($a, $b) {
        $ordemA = (int) $a['homeOrder'];
        $ordemB = (int) $b['homeOrder'];

        if ($ordemA === $ordemB) {
            $tituloA = strtolower($a['titulo'] ?? '');
            $tituloB = strtolower($b['titulo'] ?? '');
            return $tituloA <=> $tituloB;
        }

        return $ordemA <=> $ordemB;
    });

    $ultimos_arranjos = $arranjos_home;
    
    render('pages/home', compact('albums', 'arranjos', 'cantores', 'ultimos_arranjos', 'agenda', 'pageTitle'));
}

// ===== ARRANJOS (lista com filtros) =====
elseif ($p === 'arranjos') {
    $pageTitle = 'Músicas';
    
    // Filtros
    $albumId = $_GET['album'] ?? null;
    if (($albumId === null || $albumId === '') && isset($_GET['id']) && $_GET['id'] !== '') {
        $albumId = $_GET['id'];
    }
    $q = $_GET['q'] ?? null;
    
    // Validar album ID
    if ($albumId !== null && !valid_id($albumId)) {
        $albumId = null;
    }
    
    // Filtrar
    $arranjos = filter_arranjos($arranjos, $albumId, $q);
    $arranjos = sort_arranjos_for_listing($arranjos);
    
    render('pages/arranjos', compact('albums', 'arranjos', 'albumId', 'q', 'pageTitle'));
}

// ===== ARRANJO (detalhe) =====
elseif ($p === 'arranjo') {
    $id = $_GET['id'] ?? null;
    
    // Validar ID
    if (!$id || !valid_id($id)) {
        $current_page = '404';
        render('pages/404');
        exit;
    }
    
    // Buscar arranjo
    $arranjo = find_arranjo($arranjos, $id);
    if (!$arranjo) {
        $current_page = '404';
        render('pages/404');
        exit;
    }
    
    // Buscar álbum
    $album = find_album($albums, $arranjo['albumId']);
    
    $pageTitle = $arranjo['titulo'];
    
    render('pages/arranjo', compact('arranjo', 'album', 'albums', 'pageTitle'));
}

// ===== SOBRE O vip =====
elseif ($p === 'sobre') {
    $pageTitle = 'Sobre o vip';
    
    render('pages/sobre', compact('pageTitle', 'current_page'));
}

// ===== PATROCINADORES E APOIO CULTURAL =====
elseif ($p === 'apoio') {
    $pageTitle = 'Patrocinadores e apoio cultural';

    [$apoiosAtivos, $apoiosEmAberto] = split_apoios_por_status($apoiosData);

    render('pages/apoio', compact('pageTitle', 'current_page', 'apoiosAtivos', 'apoiosEmAberto'));
}

// ===== APOIADOR (detalhe) =====
elseif ($p === 'apoiador') {
    $id = $_GET['id'] ?? null;

    if (!$id || !valid_id($id)) {
        $current_page = '404';
        render('pages/404');
        exit;
    }

    $apoio = find_apoio($apoiosData, $id);
    if (!$apoio) {
        $current_page = '404';
        render('pages/404');
        exit;
    }

    $pageTitle = $apoio['nome'];

    render('pages/apoiador', compact('apoio', 'pageTitle', 'current_page'));
}

// ===== COLABORADORES (lista) =====
elseif ($p === 'colaboradores') {
    $pageTitle = 'Colaboradores';
    [$participantesCantores, $participantesApoio, $colaboradoresEventuais, $parceiros] = split_colaboradores_por_funcao($colaboradoresData);
    
    render('pages/colaboradores', compact('participantesCantores', 'participantesApoio', 'colaboradoresEventuais', 'parceiros', 'pageTitle'));
}

// ===== CANTOR (detalhe) =====
elseif ($p === 'cantor') {
    $id = $_GET['id'] ?? null;
    
    // Validar ID
    if (!$id || !valid_id($id)) {
        $current_page = '404';
        render('pages/404');
        exit;
    }
    
    // Buscar cantor
    $cantor = find_cantor($colaboradoresData, $id);
    if (!$cantor) {
        $current_page = '404';
        render('pages/404');
        exit;
    }
    
    $pageTitle = $cantor['nome'];
    
    render('pages/cantor', compact('cantor', 'pageTitle'));
}

// ===== AGENDA =====
elseif ($p === 'agenda') {
    $pageTitle = 'Agenda';
    
    render('pages/agenda', compact('agenda', 'pageTitle'));
}

// ===== PÁGINAS MARKDOWN DINÂMICAS =====
elseif (!in_array($p, $allowed_pages, true)) {
    // Nesta altura, a validação inicial já confirmou que DATA_DIR/md/$p.md existe.
    $pageTitle = ucwords(str_replace('_', ' ', $p));
    render('pages/sobre', compact('pageTitle', 'current_page'));
}
