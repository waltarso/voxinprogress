<?php
$dir=@opendir(".");
if ($dir):
$oculto = array(".", "..", "index.php", ".htaccess", ".index.php.swp", "error_log");
$conteudo = "..";
print "<a style='font-size:1.2em' href=\"./$conteudo\">Pasta anterior</a><br /></h1>";
print "<p>";
while ($conteudo = readdir($dir)) {
if (!in_array($conteudo, $oculto))
print "<a style='font-size:1.5em' href=\"./$conteudo\">$conteudo</a><br />";
}
endif;
 
closedir($dir);
?>