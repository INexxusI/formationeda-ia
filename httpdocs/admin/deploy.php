<?php
/**
 * /httpdocs/admin/deploy.php  —  Sync "miroir" /git-build -> /httpdocs, /app, /config
 * - Copie/MAJ ce qui est dans le dépôt
 * - SUPPRIME ce qui n’est plus dans le dépôt (hors "preserve")
 * - Répond "OK" immédiatement, puis logue tout dans deploy.log
 */

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0');
date_default_timezone_set('America/Toronto');

$TOKEN  = 'fb_2025_test_937abX';               // <-- ton token
$LOG    = __DIR__ . '/deploy.log';
$DRY_RUN = true;  // <-- 1er run: true (juste logs). Quand OK, mets false pour agir.

function logl(string $m): void {
  file_put_contents(__DIR__.'/deploy.log', date('c')." $m\n", FILE_APPEND);
}

// --- Auth simple (GET/POST token) ---
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== $TOKEN) {
  http_response_code(401);
  header('Content-Type: text/plain');
  echo "Bad token\n";
  logl('401 bad token');
  exit;
}

// --- Réponse immédiate à l'appelant (GitHub Action) ---
ignore_user_abort(true);
header('Content-Type: text/plain');
echo "OK\n";
flush();
if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

// --- Chemins de base ---
$scriptDir = __DIR__;               // /httpdocs/admin
$httpdocs  = dirname($scriptDir);   // /httpdocs
$root      = dirname($httpdocs);    // /
$build     = $root . '/git-build';  // /git-build

// --- Cibles & exceptions de suppression (relatives à la cible) ---
$TARGETS = [
  [
    'src'      => $build.'/httpdocs',
    'dst'      => $root.'/httpdocs',
    // Tout chemin commençant par l’un de ces préfixes sera PRÉSERVÉ côté serveur
    'preserve' => [
      'admin/',        // scripts de déploiement
      '.well-known/',  // ACME / Let's Encrypt
      '.htaccess',     // mets-le ici SEULEMENT s’il n’est pas dans le repo
      '.user.ini',     // si présent
      'uploads/',      // si tu as des uploads
      'storage/',      // si tu en as
      'tmp/',          // cache éventuel
      'robots.txt',    // si pas dans le repo
      'favicon.ico',   // si pas dans le repo
    ],
  ],
  [
    'src'      => $build.'/app',
    'dst'      => $root.'/app',
    'preserve' => [],   // adapte si tu as des dossiers runtime dans app/
  ],
  [
    'src'      => $build.'/config',
    'dst'      => $root.'/config',
    'preserve' => [],   // idem
  ],
];

// --- Helpers ---
function rr_mkdir(string $dir): void {
  if (!is_dir($dir)) mkdir($dir, 0755, true);
}
function copy_update(string $src, string $dst): void {
  if (!is_dir($src)) { logl("skip copy: $src absent"); return; }
  rr_mkdir($dst);
  foreach (scandir($src) as $f) {
    if ($f === '.' || $f === '..') continue;
    $s = $src . DIRECTORY_SEPARATOR . $f;
    $d = $dst . DIRECTORY_SEPARATOR . $f;
    if (is_dir($s)) {
      copy_update($s, $d);
    } else {
      // Copie si absent ou différent (on compare taille/mtime pour limiter I/O)
      $need = !is_file($d) || filesize($d) !== filesize($s) || (@filemtime($d) !== @filemtime($s));
      if ($need) {
        if (@copy($s, $d)) {
          @touch($d, @filemtime($s) ?: time());
          logl("PUT: $d");
        } else {
          logl("copy FAIL: $s -> $d");
        }
      }
    }
  }
}
function rr_delete(string $path): void {
  if (is_dir($path) && !is_link($path)) {
    $it = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
      RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $item) {
      $p = $item->getPathname();
      if ($item->isDir() && !is_link($p)) rmdir($p); else unlink($p);
    }
    rmdir($path);
  } else {
    @unlink($path);
  }
}
function pathRel(string $base, string $absolute): string {
  $base = rtrim($base, '/').'/';
  if (strpos($absolute, $base) === 0) return substr($absolute, strlen($base));
  return $absolute;
}
function isPreserved(string $rel, array $preservePrefixes): bool {
  $rel = ltrim(str_replace('\\','/',$rel), '/');
  foreach ($preservePrefixes as $pref) {
    $p = ltrim($pref, '/');
    if ($p !== '' && str_starts_with($rel, $p)) return true;
  }
  return false;
}

// --- Déploiement (miroir) ---
logl("deploy start (mirror): ROOT=$root BUILD=$build DRY_RUN=".($DRY_RUN?'true':'false'));

foreach ($TARGETS as $t) {
  $src = $t['src']; $dst = $t['dst']; $preserve = $t['preserve'];
  logl("SYNC $src -> $dst");

  // 1) Copie/MAJ depuis src -> dst
  copy_update($src, $dst);

  // 2) SUPPRESSION des éléments qui n’existent plus dans src (hors 'preserve')
  if (is_dir($dst)) {
    $it = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($dst, FilesystemIterator::SKIP_DOTS),
      RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $item) {
      $dAbs = $item->getPathname();
      $rel  = pathRel($dst, $dAbs);
      if (isPreserved($rel, $preserve)) continue;

      $sAbs = $src . '/' . $rel;

      // Si la contrepartie n'existe pas dans le dépôt -> supprimer
      if (!file_exists($sAbs)) {
        if ($DRY_RUN) {
          logl("DELETE (dry-run): $dAbs");
        } else {
          rr_delete($dAbs);
          logl("DELETE: $dAbs");
        }
      }
    }
  }
}

logl("deploy end (mirror)");

