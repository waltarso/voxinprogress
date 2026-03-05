<?php
/**
 * VIP - Funções auxiliares
 */

/**
 * Escapa string para HTML seguro
 */
function e($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Carrega arquivo JSON e retorna array
 */
function load_json($path)
{
    if (!file_exists($path)) {
        return [];
    }
    
    $json = file_get_contents($path);
    if (strncmp($json, "\xEF\xBB\xBF", 3) === 0) {
        $json = substr($json, 3);
    }
    $data = json_decode($json, true);
    
    return is_array($data) ? $data : [];
}

/**
 * Valida ID (slug): apenas a-z, 0-9, underscore e hífen
 */
function valid_id($id)
{
    return preg_match('/^[a-z0-9_-]+$/i', $id) === 1;
}

/**
 * Retorna URL de asset (CSS, JS, IMG).
 *
 * Gera um caminho relativo ao diretório onde o script principal está
 * instalado, por exemplo ``/vip`` quando o site roda em ``http://host/vip``
 * ou ``''`` quando está na raiz do domínio. Isso evita prefixos
 * codificados quando o local de instalação for diferente.
 */
function asset($rel)
{
    // dirname($_SERVER['SCRIPT_NAME']) retorna algo como "/vip" ou "/".
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if ($base === '/' || $base === '\\') {
        $base = '';
    }
    return $base . '/assets/' . ltrim($rel, '/');
}

/**
 * Retorna URL de imagem usada para um arranjo ou álbum.
 *
 * O campo `image` nos JSONs pode ser:
 *   - um caminho dentro de `assets/` (ex: "img/music/foo.jpg")
 *   - um nome de arquivo relativo ao diretório do arranjo em acervo
 *     (ex: "cover.jpg" ou "folder/cover.png").
 *
 * Para o segundo caso, construímos a URL usando build_acervo_url(), que
 * aponta para o subdiretório correspondente dentro de ACERVO_BASE_URL.
 * Caso nenhum arquivo seja especificado, tentamos usar a imagem do álbum.
 */
function arranjo_image_url($arranjo, $album = null)
{
    if (!empty($arranjo['image'])) {
        $img = $arranjo['image'];
        // caminho começando por "img/" ou "assets/" continua sendo asset
        if (preg_match('#^(img/|assets/)#', $img)) {
            return asset($img);
        }
        // caso contrário, presumimos que o arquivo fica no diretório de
        // armazenamento do arranjo (acervo).
        $url = build_acervo_url($arranjo['storagePath'], $img);
        if ($url !== null) {
            return $url;
        }
        // se a URL falhar, cairá no comportamento abaixo de álbum
    }

    if ($album && !empty($album['image'])) {
        $img = $album['image'];
        if (preg_match('#^(img/|assets/)#', $img)) {
            return asset($img);
        }
        // caso contrário, assume que o caminho é relativo ao acervo raiz
        // (o JSON pode conter algo como "Queen_at_six/cover.jpg").
        $base = rtrim(ACERVO_BASE_URL, '/') . '/';
        return $base . ltrim($img, '/');
    }

    return null;
}

/**
 * Monta URL com query string
 */
function url($p, $params = [])
{
    // se o modo amigável estiver ligado e o servidor aceitar rewrites, gerar
    // uma rota legível em vez de query string.
    if (defined('USE_PRETTY_URLS') && USE_PRETTY_URLS) {
        // caminho base do script (pode ser "" ou "/vip")
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        if ($base === '/' || $base === '\\') {
            $base = '';
        }
        
        switch ($p) {
            case 'arranjos':
                $url = $base . '/arranjos';
                if (isset($params['album'])) {
                    $url .= '/' . urlencode($params['album']);
                    unset($params['album']);
                }
                break;
            case 'arranjo':
                $id = $params['id'] ?? '';
                $url = $base . '/arranjo/' . urlencode($id);
                unset($params['id']);
                break;
            default:
                $url = $base . '/' . $p;
                break;
        }
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }
    
    // fallback tradicional (query string)
    $baseUrl = 'index.php?p=' . urlencode($p);
    
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $baseUrl .= '&' . urlencode($key) . '=' . urlencode($value);
        }
    }
    
    return $baseUrl;
}

/**
 * Renderiza uma view passando dados
 */
function render($view, $data = [])
{
    extract($data);
    include VIEWS_DIR . '/' . $view . '.php';
}

/**
 * Busca album no array pelo ID
 */
function find_album($albums, $id)
{
    foreach ($albums as $album) {
        if (isset($album['id']) && $album['id'] === $id) {
            return $album;
        }
    }
    return null;
}

/**
 * Busca arranjo no array pelo ID
 */
function find_arranjo($arranjos, $id)
{
    foreach ($arranjos as $arranjo) {
        if (isset($arranjo['id']) && $arranjo['id'] === $id) {
            return $arranjo;
        }
    }
    return null;
}

/**
 * Filtra arranjos por album e/ou busca textual
 */
function filter_arranjos($arranjos, $albumId = null, $q = null)
{
    $resultado = $arranjos;
    
    if ($albumId !== null && $albumId !== '') {
        $resultado = array_filter($resultado, function ($arr) use ($albumId) {
            return isset($arr['albumId']) && $arr['albumId'] === $albumId;
        });
    }
    
    if ($q !== null && $q !== '') {
        $qLower = strtolower($q);
        $resultado = array_filter($resultado, function ($arr) use ($qLower) {
            $titulo = strtolower($arr['titulo'] ?? '');
            $artista = strtolower($arr['artistaOriginal'] ?? '');
            return strpos($titulo, $qLower) !== false || strpos($artista, $qLower) !== false;
        });
    }
    
    return array_values($resultado);
}

/**
 * Busca cantor pelo ID
 */
function find_cantor($cantores, $id)
{
    foreach ($cantores as $cantor) {
        if (isset($cantor['id']) && $cantor['id'] === $id) {
            return $cantor;
        }
    }
    return null;
}

function cantor_dir_path($cantorId)
{
    if (!valid_id($cantorId)) {
        return null;
    }

    return dirname(APP_DIR) . '/equipe/' . $cantorId;
}

function cantor_base_url($cantorId)
{
    if (!valid_id($cantorId)) {
        return null;
    }

    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if ($base === '/' || $base === '\\') {
        $base = '';
    }

    return $base . '/equipe/' . rawurlencode($cantorId);
}

function cantor_photo_url($cantor)
{
    $cantorId = $cantor['id'] ?? null;
    if (!$cantorId || !valid_id($cantorId)) {
        return null;
    }

    $dirPath = cantor_dir_path($cantorId);
    $baseUrl = cantor_base_url($cantorId);

    if (!$dirPath || !$baseUrl || !is_dir($dirPath)) {
        return null;
    }

    if (!empty($cantor['foto']) && is_string($cantor['foto'])) {
        $foto = trim($cantor['foto']);

        if (preg_match('/^https?:\/\//i', $foto)) {
            return $foto;
        }

        if (preg_match('#^(img/|assets/)#', $foto)) {
            return asset($foto);
        }

        if ($foto !== '' && strpos($foto, '..') === false) {
            $candidatePath = $dirPath . '/' . ltrim(str_replace('\\', '/', $foto), '/');
            if (file_exists($candidatePath)) {
                return $baseUrl . '/' . ltrim(str_replace('\\', '/', $foto), '/');
            }
        }
    }

    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
    $priorityNames = ['foto', 'profile', 'perfil', 'avatar'];

    foreach ($priorityNames as $name) {
        foreach ($extensions as $ext) {
            $fileName = $name . '.' . $ext;
            if (file_exists($dirPath . '/' . $fileName)) {
                return $baseUrl . '/' . $fileName;
            }
        }
    }

    foreach ($extensions as $ext) {
        $matches = glob($dirPath . '/*.' . $ext);
        if (!empty($matches)) {
            $fileName = basename($matches[0]);
            return $baseUrl . '/' . rawurlencode($fileName);
        }
    }

    return null;
}

function cantor_bio_html($cantor)
{
    $cantorId = $cantor['id'] ?? null;
    $bioCurta = trim((string) ($cantor['bioCurta'] ?? ''));

    if ($cantorId && valid_id($cantorId)) {
        $dirPath = cantor_dir_path($cantorId);
        if ($dirPath && is_dir($dirPath)) {
            $preferredFile = $dirPath . '/' . $cantorId . '.md';
            if (file_exists($preferredFile)) {
                return render_markdown_file($preferredFile);
            }

            $fallbackFile = $dirPath . '/bio.md';
            if (file_exists($fallbackFile)) {
                return render_markdown_file($fallbackFile);
            }
        }
    }

    return $bioCurta !== '' ? '<p class="lead">' . e($bioCurta) . '</p>' : '';
}

function cantor_profile_links($cantor)
{
    $resultado = [];

    if (isset($cantor['links']) && is_array($cantor['links'])) {
        foreach ($cantor['links'] as $link) {
            if (!is_array($link)) {
                continue;
            }

            $titulo = trim((string) ($link['titulo'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));

            if ($titulo === '' || $url === '') {
                continue;
            }

            $resultado[] = [
                'titulo' => $titulo,
                'url' => $url,
                'icon' => 'box-arrow-up-right'
            ];
        }
    }

    $whatsapp = trim((string) ($cantor['whatsapp'] ?? ''));
    if ($whatsapp !== '') {
        if (preg_match('#^https?://#i', $whatsapp)) {
            $waUrl = $whatsapp;
        } else {
            $numero = preg_replace('/\D+/', '', $whatsapp);
            if ($numero !== '') {
                $waUrl = 'https://wa.me/' . $numero;
            }
        }

        if (!empty($waUrl)) {
            $resultado[] = [
                'titulo' => 'WhatsApp',
                'url' => $waUrl,
                'icon' => 'whatsapp'
            ];
        }
    }

    $email = trim((string) ($cantor['email'] ?? ''));
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $resultado[] = [
            'titulo' => 'Email',
            'url' => 'mailto:' . $email,
            'icon' => 'envelope'
        ];
    }

    return $resultado;
}

/**
 * Agrupa arquivos por tipo
 */
function group_files_by_type($files)
{
    $groups = [
        'pdf' => [],
        'sib' => [],
        'mp3' => [],
        'mp4' => [],
        'midi' => [],
        'other' => []
    ];
    
    foreach ($files as $file) {
        $type = $file['type'] ?? 'other';
        if (!isset($groups[$type])) {
            $type = 'other';
        }
        $groups[$type][] = $file;
    }
    
    // Remove tipos vazios
    return array_filter($groups, fn($arr) => count($arr) > 0);
}

/**
 * Retorna ícone Bootstrap Icons para tipo de arquivo
 */
function file_icon($type)
{
    $icons = [
        'pdf' => 'file-pdf',
        'sib' => 'file-music',
        'mp3' => 'music-note-beamed',
        'mp4' => 'play-circle',
        'midi' => 'music-note-beamed',
        'other' => 'file-earmark'
    ];
    
    $icon = $icons[$type] ?? 'file-earmark';
    return '<i class="bi bi-' . $icon . '"></i>';
}

/**
 * Formata data no padrão pt-BR (d/m/Y)
 */
function format_date($dateStr)
{
    $date = DateTime::createFromFormat('Y-m-d', $dateStr);
    if ($date === false) {
        return $dateStr;
    }
    return $date->format('d/m/Y');
}

/**
 * Retorna label amigável do tipo de arquivo
 */
function file_type_label($type)
{
    $labels = [
        'pdf' => 'PDF',
        'sib' => 'Sibelius',
        'mp3' => 'Áudio MP3',
        'mp4' => 'Vídeo MP4',
        'midi' => 'MIDI',
        
    ];
    
    $label = $labels[$type] ?? strtoupper($type);
    return $label;
}

/**
 * Renderiza Markdown simples para HTML.
 * Aceita cabeçalhos (# até ######), listas não ordenadas, negrito **texto** e
 * itálico *texto*.
 * Esta função não depende de bibliotecas externas e serve para páginas de
 * conteúdo como a história do VIP.
 */
function render_markdown($text)
{
    // first convert markdown links to HTML anchors, storing them as placeholders
    $links = [];
    $text = preg_replace_callback('/\[(.*?)\]\((.*?)\)/', function ($m) use (&$links) {
        $label = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
        $url = $m[2];
        if (preg_match('/^[a-z0-9_\-]+\.md$/i', $url)) {
            // internal markdown page -> convert to router URL
            $p = pathinfo($url, PATHINFO_FILENAME);
            $href = url($p);
        } elseif (preg_match('/^https?:\/\//', $url)) {
            $href = $url;
        } else {
            // leave relative URLs (could be to images/pdf etc.)
            $href = $url;
        }
        $links[] = '<a href="' . $href . '">' . $label . '</a>';
        return '%%LINK' . (count($links) - 1) . '%%';
    }, $text);

    // now escape everything else
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // restore links
    foreach ($links as $idx => $linkHtml) {
        $text = str_replace('%%LINK' . $idx . '%%', $linkHtml, $text);
    }

    // cabeçalhos
    for ($i = 6; $i >= 1; $i--) {
        $prefix = str_repeat('#', $i);
        $text = preg_replace('/^' . preg_quote($prefix, '/') . '\s*(.*?)$/m', '<h' . $i . '>$1</h' . $i . '>', $text);
    }

    // negrito e itálico
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);

    // listas não ordenadas (prefixo - ou *)
    $text = preg_replace('/^[\-\*]\s+(.*)$/m', '<li>$1</li>', $text);
    // agrupar itens em <ul>
    $text = preg_replace_callback('/(<li>.*?<\/li>)(\s*<li>.*?<\/li>)+/s', function ($m) {
        return '<ul>' . $m[0] . '</ul>';
    }, $text);

    // tabelas Markdown (linhas com | + linha separadora com ---)
    $tables = [];
    $text = preg_replace_callback('/((?:^\|.*\|\s*(?:\r\n|\r|\n|$)){2,})/m', function ($m) use (&$tables) {
        $block = trim($m[1]);
        $lines = preg_split('/\r\n|\r|\n/', $block);
        if (!is_array($lines) || count($lines) < 2) {
            return $m[0];
        }

        $headerLine = trim($lines[0]);
        $separatorLine = trim($lines[1]);

        // exige separador típico de tabela markdown: | --- | --- |
        if (!preg_match('/^\|?\s*:?-{3,}:?\s*(\|\s*:?-{3,}:?\s*)+\|?$/', $separatorLine)) {
            return $m[0];
        }

        $parseRow = function ($line) {
            $line = trim($line);
            $line = trim($line, '|');
            $cells = explode('|', $line);
            return array_map('trim', $cells);
        };

        $headers = $parseRow($headerLine);
        if (empty($headers)) {
            return $m[0];
        }

        $html = '<table class="table table-sm table-bordered align-middle"><thead><tr>';
        foreach ($headers as $cell) {
            $html .= '<th>' . $cell . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        for ($i = 2; $i < count($lines); $i++) {
            $rowLine = trim($lines[$i]);
            if ($rowLine === '') {
                continue;
            }
            $cells = $parseRow($rowLine);
            $html .= '<tr>';
            foreach ($cells as $cell) {
                $html .= '<td>' . $cell . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        $tables[] = $html;
        return '%%TABLE' . (count($tables) - 1) . '%%';
    }, $text);

    // parágrafos: separar por duas quebras de linha
    $text = preg_replace('/(\r\n|\r|\n){2,}/', '</p><p>', $text);
    $text = '<p>' . $text . '</p>';

    // restaurar blocos de tabela após a etapa de parágrafos
    foreach ($tables as $idx => $tableHtml) {
        $text = str_replace('%%TABLE' . $idx . '%%', $tableHtml, $text);
    }
    
    return $text;
}

/**
 * Lê um arquivo Markdown e converte para HTML.
 */
function render_markdown_file($path)
{
    if (!file_exists($path)) {
        return '';
    }
    $md = file_get_contents($path);
    return render_markdown($md);
}

/**
 * Monta URL segura para arquivo no acervo (sem path traversal)
 */
function build_acervo_url($storagePath, $relpath)
{
    // Validações de segurança
    if (strpos($relpath, '..') !== false) {
        return null;
    }
    if (strpos($storagePath, '..') !== false) {
        return null;
    }
    
    // Normalizar separadores de diretório para URL (Windows usa "\\").
    $storagePath = str_replace('\\', '/', (string) $storagePath);
    $relpath = str_replace('\\', '/', (string) $relpath);

    $storagePath = rtrim($storagePath, '/');
    $relpath = ltrim($relpath, '/');

    // Para PDFs com sufixo de versão (ex.: -v3.4, -ver-2.1, -2.3),
    // resolve automaticamente o arquivo de maior versão disponível.
    $relpath = resolve_latest_pdf_relpath($storagePath, $relpath);
    
    $fullPath = ACERVO_BASE_URL . $storagePath . '/' . $relpath;
    
    // Normalizar barras múltiplas
    $fullPath = preg_replace('#/+#', '/', $fullPath);
    
    return $fullPath;
}

/**
 * Resolve, para PDFs, o arquivo com maior versão na pasta.
 */
function resolve_latest_pdf_relpath($storagePath, $relpath)
{
    if (strtolower(pathinfo($relpath, PATHINFO_EXTENSION)) !== 'pdf') {
        return $relpath;
    }

    $dirRel = pathinfo($relpath, PATHINFO_DIRNAME);
    $fileName = pathinfo($relpath, PATHINFO_BASENAME);
    $targetStem = pathinfo($fileName, PATHINFO_FILENAME);
    $targetBase = strip_version_suffix($targetStem);

    $materialRoot = dirname(APP_DIR) . '/material';
    $storageFs = $materialRoot . '/' . str_replace('/', DIRECTORY_SEPARATOR, $storagePath);
    $dirFs = $storageFs;
    if ($dirRel !== '.' && $dirRel !== '') {
        $dirFs .= '/' . str_replace('/', DIRECTORY_SEPARATOR, $dirRel);
    }

    if (!is_dir($dirFs)) {
        return $relpath;
    }

    $entries = @scandir($dirFs);
    if (!is_array($entries)) {
        return $relpath;
    }

    $bestFile = null;
    $bestVersion = null;

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        if (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) !== 'pdf') {
            continue;
        }

        $stem = pathinfo($entry, PATHINFO_FILENAME);
        if (strip_version_suffix($stem) !== $targetBase) {
            continue;
        }

        $version = extract_version_parts($stem);
        if ($bestFile === null || compare_version_parts($version, $bestVersion) > 0) {
            $bestFile = $entry;
            $bestVersion = $version;
        }
    }

    if ($bestFile === null) {
        return $relpath;
    }

    if ($dirRel === '.' || $dirRel === '') {
        return $bestFile;
    }

    return $dirRel . '/' . $bestFile;
}

/**
 * Remove sufixos comuns de versão no final do nome.
 */
function strip_version_suffix($stem)
{
    $out = preg_replace('/(?:[._-](?:v|ver)[._-]?\d+(?:[._]\d+)*)$/i', '', $stem);
    $out = preg_replace('/(?:[._-]\d+[._]\d+(?:[._]\d+)*)$/', '', $out);
    return $out;
}

/**
 * Extrai versão numérica no final do nome (se houver).
 */
function extract_version_parts($stem)
{
    if (preg_match('/(?:[._-](?:v|ver)[._-]?(\d+(?:[._]\d+)*))$/i', $stem, $m)
        || preg_match('/(?:[._-](\d+[._]\d+(?:[._]\d+)*))$/', $stem, $m)) {
        $parts = preg_split('/[._]/', $m[1]);
        if (is_array($parts)) {
            return array_map('intval', $parts);
        }
    }

    return [];
}

/**
 * Compara dois vetores de versão. Retorna 1, 0 ou -1.
 */
function compare_version_parts($a, $b)
{
    $a = is_array($a) ? $a : [];
    $b = is_array($b) ? $b : [];

    $max = max(count($a), count($b));
    for ($i = 0; $i < $max; $i++) {
        $va = $a[$i] ?? 0;
        $vb = $b[$i] ?? 0;
        if ($va > $vb) {
            return 1;
        }
        if ($va < $vb) {
            return -1;
        }
    }

    return 0;
}
