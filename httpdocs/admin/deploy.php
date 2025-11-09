<?php

// ----- Lire le payload GitHub pour récupérer le SHA du commit poussé -----
$raw = file_get_contents('php://input');
$expectedSha = null;
if (!empty($raw)) {
  $data = json_decode($raw, true);
  // GitHub envoie le SHA final dans 'after'
  if (is_array($data) && !empty($data['after'])) {
    $expectedSha = strtolower(trim($data['after']));
  }
}

// ----- Fonctions utilitaires pour lire le SHA courant dans /git-build/.git -----
function git_current_sha($repoPath) {
  $git = rtrim($repoPath, '/').'/.git';
  if (!is_dir($git)) return null;
  $head = @file_get_contents($git.'/HEAD');
  if ($head === false) return null;
  $head = trim($head);

  // Cas HEAD détaché : HEAD contient directement un SHA
  if (preg_match('~^[0-9a-f]{40}$~', $head)) return strtolower($head);

  // Cas HEAD -> ref
  if (strpos($head, 'ref:') === 0) {
    $ref = trim(substr($head, 4)); // ex: refs/heads/main
    $refFile = $git.'/'.$ref;
    if (is_file($refFile)) {
      $sha = trim(file_get_contents($refFile));
      if (preg_match('~^[0-9a-f]{40}$~', $sha)) return strtolower($sha);
    }
    // Sinon chercher dans packed-refs
    $packed = $git.'/packed-refs';
    if (is_file($packed)) {
      foreach (file($packed) as $line) {
        if ($line === '' || $line[0] === '#') continue;
        if (preg_match('~^([0-9a-f]{40})\s+(.+)$~', trim($line), $m)) {
          if ($m[2] === $ref) return strtolower($m[1]);
        }
      }
    }
  }
  return null;
}

// ----- Attendre que /git-build reflète le SHA du webhook (max ~60s) -----
$waited = 0;
if ($expectedSha) {
  for ($i = 0; $i < 60; $i++) { // 60 * 1s = 60s max
    $current = git_current_sha($build);
    if ($current && $current === $expectedSha) break;
    sleep(1);
    $waited++;
  }
  logl("waitedForSha={$expectedSha} waited={$waited}s current=".git_current_sha($build));
} else {
  // si pas de SHA dans le payload (cas rare), petite pause de sécurité
  sleep(5);
  $waited = 5;
  logl("waited={$waited}s (no SHA in payload)");
}























// ===== Config =====
$TOKEN = 'fb_2025_test_937abX'; // garde ton token
$LOG   = __DIR__ . '/deploy.log';

// ===== Utils =====
function logl($m){ file_put_contents($GLOBALS['LOG'], date('c')." $m\n", FILE_APPEND); }
error_reporting(E_ALL); ini_set('display_errors', 1);

// ===== Auth (GET ou POST) =====
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== $TOKEN) { http_response_code(401); echo "Bad token"; logl('401 bad token'); exit; }

// (Optionnel) petite attente pour laisser Plesk finir de cloner dans /git-build
// usleep(800000); // 0.8s
// sleep(1);

// ===== Chemins =====
// Script = /httpdocs/admin
$scriptDir = __DIR__;
$httpdocs  = dirname($scriptDir);        // /httpdocs
$root      = dirname($httpdocs);         // / (racine vhost)
$build     = $root . '/git-build';       // /git-build

// ===== Copie récursive =====
function rr_mkdir($dir){ if(!is_dir($dir)) mkdir($dir,0755,true); }
function rr_copy($src,$dst){
  if(!is_dir($src)) { logl("skip: $src absent"); return; }
  rr_mkdir($dst);
  foreach(scandir($src) as $f){
    if($f==='.'||$f==='..') continue;
    $s=$src.DIRECTORY_SEPARATOR.$f;
    $d=$dst.DIRECTORY_SEPARATOR.$f;
    if(is_dir($s)) rr_copy($s,$d);
    else { if(!copy($s,$d)) logl("copy FAIL $s -> $d"); else @touch($d,filemtime($s)); }
  }
}

// ===== Déploiement =====
logl("deploy start: ROOT=$root BUILD=$build");
$targets = [
  'httpdocs' => $root.'/httpdocs',
  'app'      => $root.'/app',
  'config'   => $root.'/config',
];
foreach($targets as $rel=>$dst){
  $src = $build.'/'.$rel;
  logl("SYNC $src -> $dst");
  rr_copy($src,$dst);
}
logl("deploy end");
header('Content-Type: text/plain'); echo "OK\n";
