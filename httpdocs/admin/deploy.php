<?php
// ====== CONFIG SÉCURITÉ ======
$TOKEN = 'fb_2025_test_937abX';

// ====== CONTRÔLE ACCÈS ======
if (php_sapi_name() !== 'cli') {
  if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN) {
    http_response_code(403);
    echo "Forbidden";
    exit;
  }
}

// ====== DIAGNOSTIC ======
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Le script est dans /httpdocs/admin
$scriptDir = __DIR__;                   // /httpdocs/admin
$httpdocs  = dirname($scriptDir);       // /httpdocs
$root      = dirname($httpdocs);        // (racine abonnement) contient httpdocs, git-build, app, config
$build     = $root . '/git-build';      // /git-build

// Affiche les chemins pour vérifier
echo "ROOT=$root\n";
echo "BUILDDIR=$build\n";

// Garde-fous
if (!is_dir($build)) {
  http_response_code(500);
  echo "ERREUR: $build introuvable. Vérifie que Plesk déploie bien sur /git-build.\n";
  exit;
}

function rr_mkdir($dir) {
  if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
    throw new RuntimeException("Impossible de créer $dir");
  }
}

function rr_copy($src, $dst) {
  if (!is_dir($src)) {
    echo "skip: source absente $src\n";
    return;
  }
  rr_mkdir($dst);
  $items = scandir($src);
  foreach ($items as $f) {
    if ($f === '.' || $f === '..') continue;
    $s = $src . DIRECTORY_SEPARATOR . $f;
    $d = $dst . DIRECTORY_SEPARATOR . $f;
    if (is_dir($s)) {
      rr_copy($s, $d);
    } else {
      if (!copy($s, $d)) {
        echo "copy FAIL: $s -> $d\n";
      } else {
        @touch($d, filemtime($s));
        echo "copied: $s -> $d\n";
      }
    }
  }
}

// Cibles réelles hors webroot
$targets = [
  'httpdocs' => $root . '/httpdocs',
  'app'      => $root . '/app',
  'config'   => $root . '/config',
];

foreach ($targets as $rel => $dstAbs) {
  $srcAbs = $build . '/' . $rel;
  echo "SYNC $srcAbs -> $dstAbs\n";
  rr_copy($srcAbs, $dstAbs);
}

echo "OK\n";

