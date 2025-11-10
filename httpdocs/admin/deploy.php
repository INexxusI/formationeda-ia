<?php
// ===== Config =====
$TOKEN = 'fb_2025_test_937abX';           // <-- garde ton token
$LOG   = __DIR__ . '/deploy.log';
error_reporting(E_ALL); ini_set('display_errors', 1);
date_default_timezone_set('America/Toronto');

// ===== Log util =====
function logl($m){ file_put_contents(__DIR__.'/deploy.log', date('c')." $m\n", FILE_APPEND); }

// ===== Auth (GET ou POST) =====
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== $TOKEN) { http_response_code(401); echo "Bad token\n"; logl('401 bad token'); exit; }

// ===== Réponse immédiate (ne bloque pas l'appelant) =====
ignore_user_abort(true);
header('Content-Type: text/plain'); echo "OK\n"; flush();
if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

// ===== Chemins =====
// Script = /httpdocs/admin
$scriptDir = __DIR__;
$httpdocs  = dirname($scriptDir);        // /httpdocs
$root      = dirname($httpdocs);         // /
$build     = $root . '/git-build';       // /git-build

// ===== Copie récursive simple =====
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
