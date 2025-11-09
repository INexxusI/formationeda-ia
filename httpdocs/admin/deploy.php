<?php
/********** CONFIG **********/
$TOKEN = 'fb_2025_test_937abX';
$LOG   = __DIR__ . '/deploy.log';
error_reporting(E_ALL); ini_set('display_errors', 1);
date_default_timezone_set('America/Toronto'); // pour des timestamps cohérents

/********** LOG **********/
function logl($m){
  file_put_contents(__DIR__.'/deploy.log', date('c')." $m\n", FILE_APPEND);
}

/********** AUTH (GET ou POST) **********/
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== $TOKEN) {
  http_response_code(401);
  echo "Bad token\n";
  logl('401 bad token');
  exit;
}

/********** RÉPONDRE TOUT DE SUITE AU CLIENT (webhook) **********/
ignore_user_abort(true);
@set_time_limit(180);
header('Content-Type: text/plain');
echo "OK\n";
flush();
if (function_exists('fastcgi_finish_request')) {
  // PHP-FPM: envoie la réponse et libère le client, la suite continue côté serveur
  fastcgi_finish_request();
}

/********** CHEMINS **********/
// Script = /httpdocs/admin
$scriptDir = __DIR__;
$httpdocs  = dirname($scriptDir);     // /httpdocs
$root      = dirname($httpdocs);      // /
$build     = $root . '/git-build';    // /git-build

/********** LIRE PAYLOAD → SHA attendu **********/
$raw = file_get_contents('php://input');
$expectedSha = null;
if (!empty($raw)) {
  $data = json_decode($raw, true);
  if (is_array($data) && !empty($data['after'])) {
    $expectedSha = strtolower(trim($data['after'])); // SHA du push GitHub
  }
}

/********** LECTURE SHA courant dans /git-build/.git **********/
function git_current_sha($repoPath) {
  $git = rtrim($repoPath, '/').'/.git';
  if (!is_dir($git)) return null;
  $head = @file_get_contents($git.'/HEAD');
  if ($head === false) return null;
  $head = trim($head);

  if (preg_match('~^[0-9a-f]{40}$~', $head)) return strtolower($head); // detached

  if (strpos($head, 'ref:') === 0) {
    $ref = trim(substr($head, 4)); // ex: refs/heads/main
    $refFile = $git.'/'.$ref;
    if (is_file($refFile)) {
      $sha = trim(file_get_contents($refFile));
      if (preg_match('~^[0-9a-f]{40}$~', $sha)) return strtolower($sha);
    }
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

/********** ATTENDRE QUE /git-build AIT LE BON COMMIT **********/
$waited = 0;
if ($expectedSha) {
  for ($i = 0; $i < 60; $i++) { // 60s max
    $current = git_current_sha($build);
    if ($current && $current === $expectedSha) break;
    sleep(1);
    $waited++;
  }
  $current = git_current_sha($build);
  logl("waitedForSha={$expectedSha} waited={$waited}s current={$current}");
} else {
  // Appel manuel (GET) ou payload absent → pause courte
  sleep(5);
  logl("waited=5s (no SHA in payload)");
}

/********** COPIE /git-build → /httpdocs, /app, /config **********/
function rr_mkdir($dir){ if(!is_dir($dir)) mkdir($dir,0755,true); }
function rr_copy($src,$dst){
  if(!is_dir($src)) { logl("skip: $src absent"); return; }
  rr_mkdir($dst);
  foreach(scandir($src) as $f){
    if($f==='.'||$f==='..') continue;
    $s=$src.DIRECTORY_SEPARATOR.$f;
    $d=$dst.DIRECTORY_SEPARATOR.$f;
    if(is_dir($s)) rr_copy($s,$d);
    else {
      if(!copy($s,$d)) logl("copy FAIL $s -> $d");
      else @touch($d,filemtime($s));
    }
  }
}

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
