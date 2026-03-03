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

    // parágrafos: separar por duas quebras de linha
    $text = preg_replace('/(\r\n|\r|\n){2,}/', '</p><p>', $text);
    $text = '<p>' . $text . '</p>';
    
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
    
    $storagePath = rtrim($storagePath, '/');
    $relpath = ltrim($relpath, '/');
    
    $fullPath = ACERVO_BASE_URL . $storagePath . '/' . $relpath;
    
    // Normalizar barras múltiplas
    $fullPath = preg_replace('#/+#', '/', $fullPath);
    
    return $fullPath;
}
