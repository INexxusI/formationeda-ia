<?php
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
