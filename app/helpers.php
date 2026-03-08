<?php
/**
 * vip - Funções auxiliares
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
 * Para o segundo caso, construímos a URL usando build_material_url(), que
 * aponta para o subdiretório correspondente dentro de MATERIAL_BASE_URL.
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
        $url = build_material_url($arranjo['storagePath'], $img);
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
        $base = rtrim(MATERIAL_BASE_URL, '/') . '/';
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
 * Ordena arranjos para listagem: Order -> homeOrder -> titulo.
 */
function sort_arranjos_for_listing($arranjos)
{
    $resultado = array_values($arranjos);

    usort($resultado, function ($a, $b) {
        $orderA = $a['Order'] ?? null;
        $orderB = $b['Order'] ?? null;
        $homeA = $a['homeOrder'] ?? null;
        $homeB = $b['homeOrder'] ?? null;

        $hasOrderA = $orderA !== null && $orderA !== '' && is_numeric($orderA);
        $hasOrderB = $orderB !== null && $orderB !== '' && is_numeric($orderB);

        // Quem tem Order definido vem antes.
        if ($hasOrderA !== $hasOrderB) {
            return $hasOrderA ? -1 : 1;
        }

        if ($hasOrderA && $hasOrderB) {
            $cmpOrder = (int) $orderA <=> (int) $orderB;
            if ($cmpOrder !== 0) {
                return $cmpOrder;
            }
        }

        $hasHomeA = $homeA !== null && $homeA !== '' && is_numeric($homeA);
        $hasHomeB = $homeB !== null && $homeB !== '' && is_numeric($homeB);

        // Sem empate por Order, usa homeOrder como segundo critério.
        if ($hasHomeA !== $hasHomeB) {
            return $hasHomeA ? -1 : 1;
        }

        if ($hasHomeA && $hasHomeB) {
            $cmpHome = (int) $homeA <=> (int) $homeB;
            if ($cmpHome !== 0) {
                return $cmpHome;
            }
        }

        return strcasecmp((string) ($a['titulo'] ?? ''), (string) ($b['titulo'] ?? ''));
    });

    return $resultado;
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
 * Retorna true quando o registro representa integrante atual.
 * Regra: `saida` nula/vazia => ativo no grupo.
 */
function is_cantor_ativo($cantor)
{
    if (!is_array($cantor)) {
        return false;
    }

    $saida = $cantor['saida'] ?? null;
    return $saida === null || trim((string) $saida) === '';
}

/**
 * Normaliza o codigo de funcao de colaborador.
 * c = cantor, a = apoio, p = parceiro
 */
function normalize_colaborador_funcao($registro)
{
    $funcao = strtolower(trim((string) ($registro['funcao'] ?? '')));

    if (in_array($funcao, ['c', 'a', 'p'], true)) {
        return $funcao;
    }

    // Compatibilidade com bases antigas sem campo `funcao`.
    $voz = normalize_person_name((string) ($registro['voz'] ?? ''));
    if (in_array($voz, ['apoio tecnico', 'apoio técnico', 'direcao cenica', 'direção cênica'], true)) {
        return 'a';
    }

    return 'c';
}

/**
 * Ordena pessoas por naipe (vozes) e nome.
 */
function sort_colaboradores_por_voz(&$registros)
{
    usort($registros, function ($a, $b) {
        $voiceOrder = [
            'soprano' => 1,
            'mezzo' => 2,
            'mezzosoprano' => 2,
            'contralto' => 3,
            'tenor' => 4,
            'baritono' => 5,
            'baixo' => 6
        ];

        $voiceA = normalize_person_name((string) ($a['voz'] ?? ''));
        $voiceB = normalize_person_name((string) ($b['voz'] ?? ''));
        $orderA = $voiceOrder[$voiceA] ?? 999;
        $orderB = $voiceOrder[$voiceB] ?? 999;

        if ($orderA !== $orderB) {
            return $orderA <=> $orderB;
        }

        return strcasecmp((string) ($a['nome'] ?? ''), (string) ($b['nome'] ?? ''));
    });
}

/**
 * Segmenta colaboradores em:
 * - participantes (cantores e apoio ativos)
 * - colaboradores eventuais (nao ativos)
 * - parceiros (funcao p, ao final)
 */
function split_colaboradores_por_funcao($registros)
{
    $participantesCantores = [];
    $participantesApoio = [];
    $colaboradoresEventuais = [];
    $parceiros = [];

    foreach ((array) $registros as $registro) {
        $funcao = normalize_colaborador_funcao($registro);
        $registro['funcao'] = $funcao;

        if ($funcao === 'p') {
            $parceiros[] = $registro;
            continue;
        }

        if (is_cantor_ativo($registro)) {
            if ($funcao === 'a') {
                $participantesApoio[] = $registro;
            } else {
                $participantesCantores[] = $registro;
            }
        } else {
            $colaboradoresEventuais[] = $registro;
        }
    }

    sort_colaboradores_por_voz($participantesCantores);

    usort($participantesApoio, function ($a, $b) {
        return strcasecmp((string) ($a['nome'] ?? ''), (string) ($b['nome'] ?? ''));
    });

    // Mais recentes primeiro (maior data de saida).
    usort($colaboradoresEventuais, function ($a, $b) {
        $aTs = parse_member_date_to_timestamp($a['saida'] ?? null);
        $bTs = parse_member_date_to_timestamp($b['saida'] ?? null);

        if ($aTs === $bTs) {
            return strcasecmp((string) ($a['nome'] ?? ''), (string) ($b['nome'] ?? ''));
        }

        return $bTs <=> $aTs;
    });

    // Parceiros: ordem decrescente de entrada.
    usort($parceiros, function ($a, $b) {
        $aTs = parse_member_date_to_timestamp($a['entrada'] ?? null);
        $bTs = parse_member_date_to_timestamp($b['entrada'] ?? null);

        if ($aTs === $bTs) {
            return strcasecmp((string) ($a['nome'] ?? ''), (string) ($b['nome'] ?? ''));
        }

        return $bTs <=> $aTs;
    });

    return [$participantesCantores, $participantesApoio, $colaboradoresEventuais, $parceiros];
}

/**
 * Separa cadastros em [ativos, colaboradores].
 */
function split_cantores_e_colaboradores($cantores)
{
    [$participantesCantores, $participantesApoio, $colaboradoresEventuais] = split_colaboradores_por_funcao($cantores);
    $ativos = array_merge($participantesCantores, $participantesApoio);
    return [$ativos, $colaboradoresEventuais];
}

/**
 * Converte data de membro para timestamp.
 * Aceita YYYY-MM-DD, DD/MM/YYYY e YYYY.
 */
function parse_member_date_to_timestamp($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return 0;
    }

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
        $ts = strtotime($value . ' 00:00:00');
        return $ts !== false ? $ts : 0;
    }

    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m)) {
        $iso = $m[3] . '-' . $m[2] . '-' . $m[1];
        $ts = strtotime($iso . ' 00:00:00');
        return $ts !== false ? $ts : 0;
    }

    if (preg_match('/^\d{4}$/', $value)) {
        $ts = strtotime($value . '-01-01 00:00:00');
        return $ts !== false ? $ts : 0;
    }

    $ts = strtotime($value);
    return $ts !== false ? $ts : 0;
}

/**
 * Formata data de cadastro para exibicao (DD/MM/YYYY ou apenas ano).
 */
function format_member_date($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (preg_match('/^\d{4}$/', $value)) {
        return $value;
    }

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
        return $m[3] . '/' . $m[2] . '/' . $m[1];
    }

    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value)) {
        return $value;
    }

    return $value;
}

function cantor_dir_path($cantorId)
{
    if (!valid_id($cantorId)) {
        return null;
    }

    return dirname(APP_DIR) . '/colaboradores/' . $cantorId;
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

    return $base . '/colaboradores/' . rawurlencode($cantorId);
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
 * Busca apoiador/parceiro pelo ID.
 */
function find_apoio($apoios, $id)
{
    foreach ($apoios as $apoio) {
        if (isset($apoio['id']) && $apoio['id'] === $id) {
            return $apoio;
        }
    }

    return null;
}

/**
 * Segmenta apoios entre ativos e espacos em aberto.
 */
function split_apoios_por_status($registros)
{
    $apoiosAtivos = [];
    $apoiosEmAberto = [];

    foreach ((array) $registros as $registro) {
        $status = strtolower(trim((string) ($registro['status'] ?? 'ativo')));
        $registro['status'] = $status;

        if ($status === 'ativo') {
            $apoiosAtivos[] = $registro;
            continue;
        }

        $apoiosEmAberto[] = $registro;
    }

    usort($apoiosAtivos, function ($a, $b) {
        return strcasecmp((string) ($a['nome'] ?? ''), (string) ($b['nome'] ?? ''));
    });

    usort($apoiosEmAberto, function ($a, $b) {
        return strcasecmp((string) ($a['nome'] ?? ''), (string) ($b['nome'] ?? ''));
    });

    return [$apoiosAtivos, $apoiosEmAberto];
}

function apoio_dir_path($apoioId)
{
    if (!valid_id($apoioId)) {
        return null;
    }

    return dirname(APP_DIR) . '/apoios/' . $apoioId;
}

function apoio_base_url($apoioId)
{
    if (!valid_id($apoioId)) {
        return null;
    }

    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if ($base === '/' || $base === '\\') {
        $base = '';
    }

    return $base . '/apoios/' . rawurlencode($apoioId);
}

function apoio_photo_url($apoio)
{
    $apoioId = $apoio['id'] ?? null;
    if (!$apoioId || !valid_id($apoioId)) {
        return null;
    }

    $dirPath = apoio_dir_path($apoioId);
    $baseUrl = apoio_base_url($apoioId);

    if (!empty($apoio['foto']) && is_string($apoio['foto'])) {
        $foto = trim($apoio['foto']);

        if (preg_match('/^https?:\/\//i', $foto)) {
            return $foto;
        }

        if (preg_match('#^(img/|assets/)#', $foto)) {
            return asset($foto);
        }

        if ($dirPath && $baseUrl && is_dir($dirPath) && $foto !== '' && strpos($foto, '..') === false) {
            $candidatePath = $dirPath . '/' . ltrim(str_replace('\\', '/', $foto), '/');
            if (file_exists($candidatePath)) {
                return $baseUrl . '/' . ltrim(str_replace('\\', '/', $foto), '/');
            }
        }
    }

    if (!$dirPath || !$baseUrl || !is_dir($dirPath)) {
        return null;
    }

    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'svg'];
    $priorityNames = ['logo', 'foto', 'profile', 'capa'];

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

function apoio_bio_html($apoio)
{
    $apoioId = $apoio['id'] ?? null;
    $bioCurta = trim((string) ($apoio['bioCurta'] ?? ''));

    if ($apoioId && valid_id($apoioId)) {
        $dirPath = apoio_dir_path($apoioId);
        if ($dirPath && is_dir($dirPath)) {
            $preferredFile = $dirPath . '/' . $apoioId . '.md';
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

function apoio_profile_links($apoio)
{
    $resultado = [];

    if (isset($apoio['links']) && is_array($apoio['links'])) {
        foreach ($apoio['links'] as $link) {
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

    $whatsapp = trim((string) ($apoio['whatsapp'] ?? ''));
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

    $email = trim((string) ($apoio['email'] ?? ''));
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
 * Gera label exibido a partir do id do arranjo e voice.
 */
function file_label_for_arranjo($arranjo, $file)
{
    if (!empty($file['label'])) {
        return (string) $file['label'];
    }

    $arranjoId = (string) ($arranjo['id'] ?? 'arquivo');
    $voice = trim((string) ($file['voice'] ?? ''));

    return $voice !== '' ? $arranjoId . '-' . $voice : $arranjoId;
}

/**
 * Resolve relpath de arquivo. Aceita formato antigo (relpath) e novo (voice).
 */
function file_relpath_for_arranjo($arranjo, $file)
{
    if (!empty($file['relpath'])) {
        return str_replace('\\', '/', (string) $file['relpath']);
    }

    $storagePath = (string) ($arranjo['storagePath'] ?? '');
    $arranjoId = (string) ($arranjo['id'] ?? 'arquivo');
    $type = strtolower((string) ($file['type'] ?? 'other'));
    $voice = trim((string) ($file['voice'] ?? ''));

    $baseName = $arranjoId . ($voice !== '' ? '-' . $voice : '');
    $ext = file_extension_for_type($type);

    $resolved = resolve_material_relpath($storagePath, $type, $baseName, $ext);
    if ($resolved !== null) {
        return $resolved;
    }

    $dirs = file_dir_candidates_for_type($type);
    $dir = $dirs[0] ?? '';
    return ($dir !== '' ? $dir . '/' : '') . $baseName . '.' . $ext;
}

function file_extension_for_type($type)
{
    $map = [
        'midi' => 'mid',
        'mp3' => 'mp3',
        'mp4' => 'mp4',
        'pdf' => 'pdf',
        'sib' => 'sib'
    ];

    return $map[$type] ?? strtolower((string) $type);
}

function file_dir_candidates_for_type($type)
{
    if ($type === 'pdf') {
        return ['parts'];
    }
    if ($type === 'mp3' || $type === 'mp4') {
        return ['recordings', 'audios', 'audio'];
    }
    if ($type === 'sib' || $type === 'midi') {
        return ['', 'parts'];
    }
    return [''];
}

/**
 * Tenta localizar no disco o melhor arquivo para baseName/type.
 */
function resolve_material_relpath($storagePath, $type, $baseName, $ext)
{
    $materialRoot = dirname(APP_DIR) . '/material';
    $storageFs = $materialRoot . '/' . str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', '/', (string) $storagePath));
    if (!is_dir($storageFs)) {
        return null;
    }

    $targetNorm = normalize_file_token($baseName);
    $bestRel = null;
    $bestVersion = [];

    foreach (file_dir_candidates_for_type($type) as $dirRel) {
        $dirFs = $storageFs;
        if ($dirRel !== '') {
            $dirFs .= '/' . str_replace('/', DIRECTORY_SEPARATOR, $dirRel);
        }
        if (!is_dir($dirFs)) {
            continue;
        }

        $entries = @scandir($dirFs);
        if (!is_array($entries)) {
            continue;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $entryFs = $dirFs . DIRECTORY_SEPARATOR . $entry;
            if (!is_file($entryFs)) {
                continue;
            }

            $entryExt = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if ($type === 'midi') {
                if ($entryExt !== 'mid' && $entryExt !== 'midi') {
                    continue;
                }
            } elseif ($entryExt !== strtolower($ext)) {
                continue;
            }

            $stem = pathinfo($entry, PATHINFO_FILENAME);
            if (normalize_file_token($stem) !== $targetNorm) {
                continue;
            }

            $candidateRel = ($dirRel !== '' ? $dirRel . '/' : '') . $entry;
            if ($type !== 'pdf') {
                return $candidateRel;
            }

            $version = extract_version_parts($stem);
            if ($bestRel === null || compare_version_parts($version, $bestVersion) > 0) {
                $bestRel = $candidateRel;
                $bestVersion = $version;
            }
        }
    }

    return $bestRel;
}

function normalize_file_token($value)
{
    $value = strtolower(strip_version_suffix((string) $value));
    return preg_replace('/[^a-z0-9]+/', '', $value);
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
 * conteúdo como a história do vip.
 */
function render_markdown($text)
{
    // first convert markdown links to HTML anchors, storing them as placeholders
    $links = [];
    $text = preg_replace_callback('/\[(.*?)\]\((.*?)\)/', function ($m) use (&$links) {
        $label = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
        $url = $m[2];
        $attrs = '';
        if (preg_match('/^[a-z0-9_\-]+\.md$/i', $url)) {
            // internal markdown page -> convert to router URL
            $p = pathinfo($url, PATHINFO_FILENAME);
            $href = url($p);
        } elseif (preg_match('/^https?:\/\//', $url)) {
            $href = $url;
            $attrs = ' target="_blank" rel="noopener noreferrer"';
        } else {
            // leave relative URLs (could be to images/pdf etc.)
            $href = $url;
        }
        $safeHref = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
        $links[] = '<a href="' . $safeHref . '"' . $attrs . '>' . $label . '</a>';
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

    // quebra de linha manual em Markdown:
    // - dois espacos no fim da linha
    // - barra invertida no fim da linha
    $text = preg_replace('/ {2,}(\r\n|\r|\n)/', '<br>$1', $text);
    $text = preg_replace('/\\\\(\r\n|\r|\n)/', '<br>$1', $text);

    // listas markdown (ordenadas, não ordenadas e aninhadas por indentação)
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $out = [];
    $stack = [];

    $closeListLevel = function () use (&$stack, &$out) {
        $top = array_pop($stack);
        if (!$top) {
            return;
        }

        if (!empty($top['liOpen'])) {
            $out[] = '</li>';
        }
        $out[] = '</' . $top['type'] . '>';
    };

    $openListLevel = function ($type, $indent) use (&$stack, &$out) {
        $out[] = '<' . $type . '>';
        $stack[] = [
            'type' => $type,
            'indent' => $indent,
            'liOpen' => false
        ];
    };

    foreach ((array) $lines as $line) {
        $lineForMatch = str_replace("\t", '    ', $line);
        if (preg_match('/^(\s*)([-\*]|\d+\.)\s+(.*)$/', $lineForMatch, $m) === 1) {
            $indent = strlen($m[1]);
            $marker = $m[2];
            $content = $m[3];
            $type = preg_match('/^\d+\.$/', $marker) ? 'ol' : 'ul';

            if (empty($stack)) {
                $openListLevel($type, $indent);
            } else {
                while (!empty($stack) && $indent < $stack[count($stack) - 1]['indent']) {
                    $closeListLevel();
                }

                if (!empty($stack) && $indent > $stack[count($stack) - 1]['indent']) {
                    $openListLevel($type, $indent);
                } elseif (!empty($stack) && $type !== $stack[count($stack) - 1]['type']) {
                    $closeListLevel();
                    $openListLevel($type, $indent);
                }
            }

            if (!empty($stack) && !empty($stack[count($stack) - 1]['liOpen'])) {
                $out[] = '</li>';
                $stack[count($stack) - 1]['liOpen'] = false;
            }

            $out[] = '<li>' . $content;
            if (!empty($stack)) {
                $stack[count($stack) - 1]['liOpen'] = true;
            }
            continue;
        }

        if (!empty($stack)) {
            if (trim($line) !== '' && !preg_match('/^<\/?(h[1-6]|p|ul|ol|li|table|thead|tbody|tr|th|td|hr)\b/i', trim($line))) {
                $out[] = '<br>' . ltrim($line);
                continue;
            }

            while (!empty($stack)) {
                $closeListLevel();
            }
        }

        $out[] = $line;
    }

    while (!empty($stack)) {
        $closeListLevel();
    }

    $text = implode("\n", $out);

    // regras horizontais (---, ***, ___)
    $hrs = [];
    $text = preg_replace_callback('/^\s*(?:-{3,}|\*{3,}|_{3,})\s*$/m', function () use (&$hrs) {
        $hrs[] = '<hr>';
        return '%%HR' . (count($hrs) - 1) . '%%';
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

        $html = '<table class="table table-sm table-bordered align-middle md-table"><thead><tr>';
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

    // parágrafos: separar apenas por linha realmente em branco
    // (evita tratar um único CRLF do Windows como quebra dupla).
    $text = preg_replace('/(?:\r\n){2,}|\n{2,}|\r{2,}/', '</p><p>', $text);
    $text = '<p>' . $text . '</p>';

    // restaurar blocos de tabela após a etapa de parágrafos
    foreach ($tables as $idx => $tableHtml) {
        $text = str_replace('%%TABLE' . $idx . '%%', $tableHtml, $text);
    }

    // restaurar regras horizontais após a etapa de parágrafos
    foreach ($hrs as $idx => $hrHtml) {
        $text = str_replace('%%HR' . $idx . '%%', $hrHtml, $text);
    }

    // evita markup inválido como <p><hr></p>
    $text = preg_replace('/<p>\s*(<hr>)\s*<\/p>/', '$1', $text);

    // evita wrappers <p> em blocos que já são estruturais
    $text = preg_replace('/<p>\s*(<(?:h[1-6]|ul|ol|table)\b[^>]*>)/', '$1', $text);
    $text = preg_replace('/(<\/(?:h[1-6]|ul|ol|table)>)\s*<\/p>/', '$1', $text);

    // adiciona id/classe nas tabelas markdown com base no cabecalho anterior.
    // Ex.: "### Cantores" seguido de tabela -> id="cantores".
    $usedTableIds = [];
    $slugify = function ($value) {
        $slug = trim((string) $value);
        if ($slug === '') {
            return '';
        }

        $slug = html_entity_decode($slug, ENT_QUOTES, 'UTF-8');
        $slug = strtr($slug, [
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ç' => 'C', 'ç' => 'c'
        ]);

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
            if ($converted !== false) {
                $slug = $converted;
            }
        }

        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string) $slug, '-');
        return $slug;
    };

    $text = preg_replace_callback(
        '/(<h([1-6])>([^<]*)<\/h\2>\s*)(<table class="[^"]*md-table[^"]*")/',
        function ($m) use (&$usedTableIds, $slugify) {
            $headingText = trim(strip_tags($m[3]));
            $baseId = $slugify($headingText);
            if ($baseId === '') {
                return $m[0];
            }

            $tableId = $baseId;
            $suffix = 2;
            while (isset($usedTableIds[$tableId])) {
                $tableId = $baseId . '-' . $suffix;
                $suffix++;
            }
            $usedTableIds[$tableId] = true;

            $tableOpen = $m[4];
            if (preg_match('/class="([^"]*)"/', $tableOpen, $classMatch) === 1) {
                $classValue = trim($classMatch[1]);
                $classValue .= ' md-table--' . $tableId;
                $tableOpen = str_replace(
                    'class="' . $classMatch[1] . '"',
                    'class="' . $classValue . '" id="' . $tableId . '"',
                    $tableOpen
                );
            }

            return $m[1] . $tableOpen;
        },
        $text
    );
    
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
 * Normaliza nome para comparacoes (sem acento, lowercase, sem pontuacao).
 */
function normalize_person_name($name)
{
    $value = trim((string) $name);
    if ($value === '') {
        return '';
    }

    if (function_exists('mb_strtolower')) {
        $value = mb_strtolower($value, 'UTF-8');
    } else {
        $value = strtolower($value);
    }
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false) {
            $value = $converted;
        }
    }

    $value = preg_replace('/[^a-z0-9\s]/', ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return trim($value);
}

/**
 * Carrega colaboradores historicos a partir da tabela em formacoes.md.
 * Exclui integrantes que estao na formacao atual (colaboradores.json).
 */
function load_colaboradores_historicos($formacoesMdPath, $cantoresAtuais)
{
    if (!file_exists($formacoesMdPath)) {
        return [];
    }

    $currentNames = [];
    foreach ((array) $cantoresAtuais as $cantor) {
        $nome = (string) ($cantor['nome'] ?? '');
        $norm = normalize_person_name($nome);
        if ($norm !== '') {
            $currentNames[$norm] = true;
        }
    }

    $lines = preg_split('/\r\n|\r|\n/', (string) file_get_contents($formacoesMdPath));
    if (!is_array($lines)) {
        return [];
    }

    $colaboradores = [];
    $seen = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '|') === false) {
            continue;
        }

        // ignora cabecalho/separador da tabela
        if (preg_match('/^\|\s*-+/', $line)) {
            continue;
        }

        $cells = array_map('trim', explode('|', trim($line, '|')));
        if (count($cells) < 5) {
            continue;
        }

        if (strcasecmp($cells[0], 'Membro') === 0) {
            continue;
        }

        $nome = trim(strip_tags(preg_replace('/\*\*(.*?)\*\*/', '$1', (string) $cells[0])));
        $funcao = trim((string) $cells[1]);
        $entrada = trim((string) $cells[2]);
        $saida = trim((string) $cells[3]);
        $observacoes = trim((string) $cells[4]);

        $norm = normalize_person_name($nome);
        if ($norm === '' || isset($currentNames[$norm]) || isset($seen[$norm])) {
            continue;
        }

        $seen[$norm] = true;
        $colaboradores[] = [
            'nome' => $nome,
            'funcao' => $funcao,
            'entrada' => $entrada,
            'saida' => $saida,
            'observacoes' => $observacoes
        ];
    }

    usort($colaboradores, function ($a, $b) {
        return strcasecmp((string) ($a['nome'] ?? ''), (string) ($b['nome'] ?? ''));
    });

    return $colaboradores;
}

/**
 * Monta URL segura para arquivo no acervo (sem path traversal)
 */
function build_material_url($storagePath, $relpath)
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
    
    $fullPath = MATERIAL_BASE_URL . $storagePath . '/' . $relpath;
    
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
    // Remove tokens de versão em qualquer posição (ex.: _v4.0-, -ver-2.1, -2.3).
    $out = preg_replace('/([._-])(?:v|ver)[._-]?\d+(?:[._]\d+)*/i', '$1', $stem);
    $out = preg_replace('/([._-])\d+[._]\d+(?:[._]\d+)*/', '$1', $out);
    // Limpa separadores duplicados ou sobrando no começo/fim.
    $out = preg_replace('/([._-]){2,}/', '$1', $out);
    $out = trim($out, '._-');
    return $out;
}

/**
 * Extrai versão numérica no final do nome (se houver).
 */
function extract_version_parts($stem)
{
    $all = [];

    if (preg_match_all('/(?:^|[._-])(?:v|ver)[._-]?(\d+(?:[._]\d+)*)/i', $stem, $m1)) {
        $all = array_merge($all, $m1[1]);
    }
    if (preg_match_all('/(?:^|[._-])(\d+[._]\d+(?:[._]\d+)*)(?=$|[._-])/i', $stem, $m2)) {
        $all = array_merge($all, $m2[1]);
    }

    $best = [];
    foreach ($all as $raw) {
        $parts = preg_split('/[._]/', $raw);
        if (!is_array($parts)) {
            continue;
        }
        $candidate = array_map('intval', $parts);
        if (compare_version_parts($candidate, $best) > 0) {
            $best = $candidate;
        }
    }

    return $best;
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
