<?php
$dir = @opendir(".");
if ($dir):

$oculto = array(".", "..", "index.php", ".htaccess", ".index.php.swp", "error_log");

$itens = array();

while (($conteudo = readdir($dir)) !== false) {
    if (!in_array($conteudo, $oculto)) {
        $itens[] = $conteudo;
    }
}

closedir($dir);

// Ordena alfabeticamente (case-insensitive)
natcasesort($itens);

// Reindexa para poder usar foreach normalmente
$itens = array_values($itens);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Arquivos</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 12px; }
    a.item {
      display: block;
      font-size: 1.2rem;   /* desktop */
      line-height: 1.6;
      text-decoration: none;
      margin: 4px 0;
    }
    a.item:hover { text-decoration: underline; }

    a.voltar {
      display: inline-block;
      font-size: 1.3rem;
      margin-bottom: 12px;
    }

    /* Celulares */
    @media (max-width: 600px) {
      a.item { font-size: 1.8rem; }
      a.voltar { font-size: 2.0rem; }
    }
  </style>
</head>
<body>

<a class="voltar" href="../">Pasta anterior</a>

<?php foreach ($itens as $nome): ?>
  <a class="item" href="./<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>">
    <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>
  </a>
<?php endforeach; ?>

</body>
</html>
<?php
endif;
?>
