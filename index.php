<?php
/**
 * VIP (Vox in Progress) - Router Principal
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
$cantores = load_json(DATA_DIR . '/cantores.json');
$agenda = load_json(DATA_DIR . '/agenda.json');

// Whitelist de páginas permitidas (fixas)
$allowed_pages = ['home', 'arranjos', 'arranjo', 'historia', 'cantores', 'cantor', 'agenda', 'contato'];

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
    
    render('pages/home', compact('albums', 'arranjos', 'cantores', 'ultimos_arranjos', 'pageTitle'));
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

// ===== HISTÓRIA =====
elseif ($p === 'historia') {
    $pageTitle = 'História';
    
    render('pages/historia', compact('pageTitle', 'current_page'));
}

// ===== CANTORES (lista) =====
elseif ($p === 'cantores') {
    $pageTitle = 'Cantores';
    
    render('pages/cantores', compact('cantores', 'pageTitle'));
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
    $cantor = find_cantor($cantores, $id);
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

// ===== CONTATO =====
elseif ($p === 'contato') {
    $pageTitle = 'Contato';
    $message = null;
    
    // Processar formulário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $message = ['type' => 'danger', 'text' => 'Token de segurança inválido.'];
        }
        // Validar honeypot
        elseif (!empty($_POST['website'])) {
            // Bot detectado - silenciosamente fingir sucesso
            $message = ['type' => 'success', 'text' => 'Obrigado! Sua mensagem foi enviada com sucesso.'];
        }
        else {
            // Validar campos obrigatórios
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $assunto = trim($_POST['assunto'] ?? '');
            $mensagem = trim($_POST['mensagem'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            
            $erros = [];
            
            if (empty($nome) || strlen($nome) < 2) {
                $erros[] = 'Nome deve ter pelo menos 2 caracteres.';
            }
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erros[] = 'Email válido é obrigatório.';
            }
            
            if (empty($assunto)) {
                $erros[] = 'Assunto é obrigatório.';
            }
            
            if (empty($mensagem) || strlen($mensagem) < 10) {
                $erros[] = 'Mensagem deve ter pelo menos 10 caracteres.';
            }
            
            if (!empty($erros)) {
                $message = ['type' => 'danger', 'text' => 'Erros: ' . implode(' / ', $erros)];
            }
            else {
                // Tentar enviar email
                if (ENABLE_EMAIL) {
                    $to = CONTACT_EMAIL;
                    $subject = '[VIP] ' . htmlspecialchars($assunto);
                    $body = "Nome: " . htmlspecialchars($nome) . "\n";
                    $body .= "Email: " . htmlspecialchars($email) . "\n";
                    $body .= "Telefone: " . htmlspecialchars($telefone) . "\n";
                    $body .= "Assunto: " . htmlspecialchars($assunto) . "\n";
                    $body .= "---\n";
                    $body .= htmlspecialchars($mensagem) . "\n";
                    
                    $headers = "From: " . htmlspecialchars($email) . "\r\n";
                    $headers .= "Reply-To: " . htmlspecialchars($email) . "\r\n";
                    $headers .= "X-Mailer: VIP\r\n";
                    
                    if (mail($to, $subject, $body, $headers)) {
                        $message = ['type' => 'success', 'text' => 'Obrigado! Sua mensagem foi enviada com sucesso. Entraremos em contato em breve.'];
                    } else {
                        $message = ['type' => 'warning', 'text' => 'Mensagem recebida, mas houve um erro ao enviar email. Tentaremos contato logo.'];
                    }
                } else {
                    // Email desabilitado - apenas simular sucesso
                    $message = ['type' => 'success', 'text' => 'Obrigado! Sua mensagem foi recebida. Entraremos em contato em breve.'];
                }
            }
        }
    }
    
    render('pages/contato', compact('message', 'pageTitle'));
}

// ===== PÁGINAS MARKDOWN DINÂMICAS =====
elseif (!in_array($p, $allowed_pages, true)) {
    // Nesta altura, a validação inicial já confirmou que DATA_DIR/md/$p.md existe.
    $pageTitle = ucwords(str_replace('_', ' ', $p));
    render('pages/historia', compact('pageTitle', 'current_page'));
}
