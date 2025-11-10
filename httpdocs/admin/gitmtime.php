<?php
// /httpdocs/admin/gitmtime.php
// Renvoie l'epoch (secondes) de la dernière modification trouvée dans /git-build/{httpdocs,app,config}
// Protégé par un token en query: ?token=XXX

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$TOKEN = 'fb_2025_test_937abX';

if (($_GET['token'] ?? '') !== $TOKEN) {
  http_response_code(401);
  header('Content-Type: text/plain');
  echo "Bad token";
  exit;
}

$root  = dirname(dirname(__DIR__));   // /
$build = $root . '/git-build';

$paths = [
  $build . '/httpdocs',
  $build . '/app',
  $build . '/config',
];

$max = 0;
foreach ($paths as $p) {
  if (!is_dir($p)) continue;
  $it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($p, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
  );
  foreach ($it as $f) {
    $t = @filemtime($f);
    if ($t && $t > $max) $max = $t;
  }
}

header('Content-Type: text/plain');
echo $max; // 0 si rien trouvé
