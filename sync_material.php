<?php
/**
 * Sincroniza app/data/arranjos.json a partir da pasta material/.
 *
 * Uso:
 *   php sync_material.php
 */

declare(strict_types=1);

$rootDir = __DIR__;
$materialDir = $rootDir . DIRECTORY_SEPARATOR . 'material';
$arranjosPath = $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'arranjos.json';
$albumsPath = $rootDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'albums.json';

if (!is_dir($materialDir)) {
    fwrite(STDERR, "Erro: pasta material nao encontrada em {$materialDir}\n");
    exit(1);
}
if (!is_file($arranjosPath)) {
    fwrite(STDERR, "Erro: arquivo arranjos.json nao encontrado em {$arranjosPath}\n");
    exit(1);
}

$arranjosJson = file_get_contents($arranjosPath);
$arranjos = json_decode((string) $arranjosJson, true);
if (!is_array($arranjos)) {
    fwrite(STDERR, "Erro: arranjos.json invalido\n");
    exit(1);
}

$albums = [];
if (is_file($albumsPath)) {
    $albumsJson = file_get_contents($albumsPath);
    $albumsDecoded = json_decode((string) $albumsJson, true);
    if (is_array($albumsDecoded)) {
        $albums = $albumsDecoded;
    }
}

$existingByStorage = [];
$albumIdByFolder = [];
foreach ($arranjos as $idx => $arr) {
    $storage = (string) ($arr['storagePath'] ?? '');
    if ($storage !== '') {
        $key = strtolower(str_replace('\\\\', '/', $storage));
        $existingByStorage[$key] = $idx;

        $first = explode('/', str_replace('\\\\', '/', $storage))[0] ?? '';
        $albumId = (string) ($arr['albumId'] ?? '');
        if ($first !== '' && $albumId !== '' && !isset($albumIdByFolder[$first])) {
            $albumIdByFolder[$first] = $albumId;
        }
    }
}

foreach ($albums as $album) {
    $id = (string) ($album['id'] ?? '');
    if ($id === '') {
        continue;
    }
    $title = (string) ($album['titulo'] ?? '');
    if ($title !== '' && !isset($albumIdByFolder[$title])) {
        $albumIdByFolder[$title] = $id;
    }
}

$foundStorageKeys = [];
$newCount = 0;
$updatedCount = 0;

$albumDirs = array_values(array_filter(scandir($materialDir) ?: [], function (string $name): bool {
    return $name !== '.' && $name !== '..';
}));

foreach ($albumDirs as $albumFolder) {
    $albumFull = $materialDir . DIRECTORY_SEPARATOR . $albumFolder;
    if (!is_dir($albumFull)) {
        continue;
    }

    $arranjoDirs = array_values(array_filter(scandir($albumFull) ?: [], function (string $name): bool {
        return $name !== '.' && $name !== '..';
    }));

    foreach ($arranjoDirs as $arrFolder) {
        $arrFull = $albumFull . DIRECTORY_SEPARATOR . $arrFolder;
        if (!is_dir($arrFull)) {
            continue;
        }

        $storagePath = $albumFolder . '/' . $arrFolder;
        $storageKey = strtolower($storagePath);

        $arranjoId = isset($existingByStorage[$storageKey])
            ? (string) ($arranjos[$existingByStorage[$storageKey]]['id'] ?? slugify($arrFolder))
            : slugify($arrFolder);

        $scan = scanArranjoDir($arrFull, $arranjoId);
        if (empty($scan['files'])) {
            // Ignorar pastas sem arquivos de arranjo.
            continue;
        }

        $foundStorageKeys[$storageKey] = true;

        if (isset($existingByStorage[$storageKey])) {
            $idx = $existingByStorage[$storageKey];
            $arranjos[$idx]['files'] = $scan['files'];
            if (!empty($scan['image']) && empty($arranjos[$idx]['image'])) {
                $arranjos[$idx]['image'] = $scan['image'];
            }
            $updatedCount++;
            continue;
        }

        $albumId = $albumIdByFolder[$albumFolder] ?? slugify($albumFolder);

        $arranjos[] = [
            'id' => slugify($arrFolder),
            'albumId' => $albumId,
            'titulo' => humanize($arrFolder),
            'artistaOriginal' => '',
            'ano' => null,
            'duracao' => null,
            'dificuldade' => 3,
            'observacoes' => 'Importado automaticamente do material',
            'storagePath' => $storagePath,
            'image' => $scan['image'],
            'files' => $scan['files'],
            'homeOrder' => null,
        ];
        $newCount++;
    }
}

// Ordenar por albumId e titulo para manter arquivo previsivel.
usort($arranjos, function (array $a, array $b): int {
    $albumCmp = strcmp((string) ($a['albumId'] ?? ''), (string) ($b['albumId'] ?? ''));
    if ($albumCmp !== 0) {
        return $albumCmp;
    }
    return strcmp((string) ($a['titulo'] ?? ''), (string) ($b['titulo'] ?? ''));
});

$backupPath = $arranjosPath . '.bak-' . date('Ymd-His');
copy($arranjosPath, $backupPath);

$newJson = json_encode($arranjos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($newJson === false) {
    fwrite(STDERR, "Erro: falha ao serializar JSON\n");
    exit(1);
}

file_put_contents($arranjosPath, $newJson . PHP_EOL);

echo "Sincronizacao concluida.\n";
echo "- Atualizados: {$updatedCount}\n";
echo "- Novos: {$newCount}\n";
echo "- Backup: {$backupPath}\n";

echo "\nObservacao: arranjos removidos da pasta material NAO sao excluidos do JSON automaticamente.\n";

afterChecks($arranjos, $foundStorageKeys);

function scanArranjoDir(string $arrFull, string $arranjoId): array
{
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($arrFull, FilesystemIterator::SKIP_DOTS)
    );

    $files = [];
    $imageCandidates = [];

    foreach ($it as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $name = $fileInfo->getFilename();
        if (strcasecmp($name, 'index.php') === 0) {
            continue;
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $relative = str_replace('\\\\', '/', substr($fileInfo->getPathname(), strlen($arrFull) + 1));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'avif'], true)) {
            $imageCandidates[] = $relative;
            continue;
        }

        $type = detectType($ext);
        if ($type === null) {
            continue;
        }

        $stem = pathinfo($name, PATHINFO_FILENAME);
        $voice = deriveVoiceFromStem($stem, $arranjoId);

        $entry = ['type' => $type];
        if ($voice !== null && $voice !== '') {
            $entry['voice'] = $voice;
        }

        $files[] = $entry;
    }

    usort($files, function (array $a, array $b): int {
        $typeCmp = strcmp($a['type'], $b['type']);
        if ($typeCmp !== 0) {
            return $typeCmp;
        }
        return strcasecmp((string) ($a['voice'] ?? ''), (string) ($b['voice'] ?? ''));
    });

    $image = null;
    if (!empty($imageCandidates)) {
        // Preferir imagem na raiz da pasta do arranjo.
        usort($imageCandidates, function (string $a, string $b): int {
            $aDepth = substr_count($a, '/');
            $bDepth = substr_count($b, '/');
            if ($aDepth !== $bDepth) {
                return $aDepth <=> $bDepth;
            }
            return strcmp($a, $b);
        });
        $image = $imageCandidates[0];
    }

    return [
        'files' => $files,
        'image' => $image,
    ];
}

function stripVersionForVoice(string $stem): string
{
    $out = preg_replace('/([._-])(?:v|ver)[._-]?\d+(?:[._]\d+)*/i', '$1', $stem);
    $out = preg_replace('/([._-])\d+[._]\d+(?:[._]\d+)*/', '$1', $out);
    $out = preg_replace('/([._-]){2,}/', '$1', (string) $out);
    return trim((string) $out, "._- ");
}

function deriveVoiceFromStem(string $stem, string $arranjoId): ?string
{
    $stem = stripVersionForVoice($stem);

    $parts = preg_split('/[_\-\s]+/', $arranjoId);
    $parts = array_values(array_filter(is_array($parts) ? $parts : [], fn($p) => $p !== ''));
    if (!empty($parts)) {
        $idPattern = implode('[ _\\-]*', array_map(fn($p) => preg_quote($p, '/'), $parts));

        if (preg_match('/^' . $idPattern . '(?:[ _\\-]+(.+))?$/i', $stem, $m)) {
            $voice = trim((string) ($m[1] ?? ''), " _-\t\r\n");
            return $voice !== '' ? $voice : null;
        }

        if (preg_match('/^(.+?)[ _\\-]+' . $idPattern . '$/i', $stem, $m)) {
            $voice = trim((string) ($m[1] ?? ''), " _-\t\r\n");
            return $voice !== '' ? $voice : null;
        }
    }

    $chunks = preg_split('/-+/', $stem);
    if (is_array($chunks) && count($chunks) > 1) {
        $last = trim((string) end($chunks));
        if ($last !== '') {
            $known = ['Tutti', 'Bai', 'Bar', 'Con', 'Mez', 'Sop', 'Ten', 'Click', 'Guia', 'Guide', 'Baixo', 'Baritono', 'Contralto', 'Mezzo', 'Soprano', 'Tenor', 'Fem', 'Mas', 'Solo', 'Vozes', 'Compacta'];
            foreach ($known as $k) {
                if (strcasecmp($last, $k) === 0) {
                    return $last;
                }
            }
        }
    }

    return null;
}

function detectType(string $ext): ?string
{
    if ($ext === 'pdf') {
        return 'pdf';
    }
    if ($ext === 'sib') {
        return 'sib';
    }
    if ($ext === 'mp3') {
        return 'mp3';
    }
    if ($ext === 'mp4') {
        return 'mp4';
    }
    if ($ext === 'mid' || $ext === 'midi') {
        return 'midi';
    }
    if ($ext === 'wav' || $ext === 'flac' || $ext === 'ogg') {
        return 'other';
    }
    return null;
}

function slugify(string $value): string
{
    $value = trim($value);
    $value = str_replace([' ', '-'], '_', $value);
    $value = preg_replace('/[^a-zA-Z0-9_]/', '', $value);
    return strtolower((string) $value);
}

function humanize(string $value): string
{
    $value = str_replace('_', ' ', $value);
    return ucwords(trim($value));
}

function afterChecks(array $arranjos, array $foundStorageKeys): void
{
    $missing = 0;
    foreach ($arranjos as $arr) {
        $storage = strtolower((string) ($arr['storagePath'] ?? ''));
        if ($storage === '') {
            continue;
        }
        if (!isset($foundStorageKeys[$storage])) {
            $missing++;
        }
    }
    if ($missing > 0) {
        echo "Aviso: {$missing} arranjo(s) do JSON nao foram encontrados em material/ (mantidos no arquivo).\n";
    }
}
