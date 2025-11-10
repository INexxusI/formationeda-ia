<?php
/**** CONFIG ****/
$TOKEN = 'fb_2025_test_937abX';
error_reporting(E_ALL); ini_set('display_errors', 1);
date_default_timezone_set('America/Toronto');
function logl($m){ file_put_contents(__DIR__.'/deploy.log', date('c')." $m\n", FILE_APPEND); }

/**** AUTH (GET ou POST) ****/
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== $TOKEN) { http_response_code(401); echo "Bad token\n"; logl('401 bad token'); exit; }

/**** RÉPONSE IMMÉDIATE AU WEBHOOK ****/
ignore_user_abort(true);
header('Content-Type: text/plain'); echo "OK\n"; flush();
if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

/**** CHEMINS ****/
$scriptDir = __DIR__;                 // /httpdocs/admin
$httpdocs  = dirname($scriptDir);     // /httpdocs
$root      = dirname($httpdocs);      // /
$build     = $root . '/git-build';    // /git-build

/**** PAYLOAD : SHA + fichiers attendus ****/
$raw = file_get_contents('php://input');
$expectedSha = null;
$expectFiles = [];
$pushTs = time();
if ($raw) {
  $data = json_decode($raw, true);
  if (is_array($data)) {
    if (!empty($data['after'])) $expectedSha = strtolower(trim($data['after']));
    if (!empty($data['head_commit']['timestamp'])) $pushTs = strtotime($data['head_commit']['timestamp']) ?: $pushTs;
    $lists = [];
    if (!empty($data['head_commit'])) $lists[] = $data['head_commit'];
    if (!empty($data['commits']))     $lists   = array_merge($lists, $data['commits']);
    foreach ($lists as $c) {
      foreach (['added','modified'] as $k) {
        if (!empty($c[$k]) && is_array($c[$k])) {
          foreach ($c[$k] as $p) if (preg_match('~^(httpdocs|app|config)/~', $p)) $expectFiles[] = $p;
        }
      }
    }
  }
}

/**** LECTURE SHA COURANT ****/
function git_current_sha($repoPath){
  $git = rtrim($repoPath,'/').'/.git';
  if (!is_dir($git)) return null;
  $head = @file_get_contents($git.'/HEAD'); if ($head===false) return null; $head = trim($head);
  if (preg_match('~^[0-9a-f]{40}$~',$head)) return strtolower($head);
  if (strpos($head,'ref:')===0){
    $ref = trim(substr($head,4)); $refFile = $git.'/'.$ref;
    if (is_file($refFile)){ $sha = trim(file_get_contents($refFile)); if (preg_match('~^[0-9a-f]{40}$~',$sha)) return strtolower($sha); }
    $packed = $git.'/packed-refs';
    if (is_file($packed)){
      foreach (file($packed) as $line){
        if ($line===''||$line[0]==='#') continue;
        if (preg_match('~^([0-9a-f]{40})\s+(.+)$~',trim($line),$m)) if ($m[2]===$ref) return strtolower($m[1]);
      }
    }
  }
  return null;
}

/**** ATTENTE ROBUSTE : SHA OU FICHIERS ****/
$ok = false; $waited=0;
for ($i=0;$i<90;$i++){ // 90s max
  $sha = git_current_sha($build);
  $shaOk = ($expectedSha && $sha === $expectedSha);
  $filesOk = true;
  if ($expectFiles){
    $filesOk = false;
    $seen=0;
    foreach ($expectFiles as $rel){
      $probe = $build.'/'.$rel;
      if (file_exists($probe)){
        // si on a un timestamp de push, vérifie que le fichier n'est pas plus vieux (évite un ancien commit)
        if (@filemtime($probe) >= $pushTs - 5) { $seen++; }
      }
    }
    if ($seen === count($expectFiles)) $filesOk = true;
  }
  if ($shaOk || $filesOk){ $ok=true; break; }
  sleep(1); $waited++;
}
logl("ready=".($ok?'1':'0')." waited={$waited}s expectedSha={$expectedSha} currentSha=".git_current_sha($build)." filesTracked=".count($expectFiles));

if (!$ok){
  // On NE DÉPLOIE PAS si le dépôt n'est pas prêt : on attendra le prochain webhook/cron
  logl("ABORT deploy: repo not ready");
  exit;
}

/**** COPIE /git-build → /httpdocs|/app|/config ****/
function rr_mkdir($d){ if(!is_dir($d)) mkdir($d,0755,true); }
function rr_copy($src,$dst){
  if(!is_dir($src)) { logl("skip: $src absent"); return; }
  rr_mkdir($dst);
  foreach (scandir($src) as $f){
    if($f==='.'||$f==='..') continue;
    $s=$src.DIRECTORY_SEPARATOR.$f; $d=$dst.DIRECTORY_SEPARATOR.$f;
    if (is_dir($s)) rr_copy($s,$d);
    else { if(!copy($s,$d)) logl("copy FAIL $s -> $d"); else @touch($d,filemtime($s)); }
  }
}

logl("deploy start: ROOT=$root BUILD=$build");
$targets = ['httpdocs'=>$root.'/httpdocs','app'=>$root.'/app','config'=>$root.'/config'];
foreach($targets as $rel=>$dst){ $src=$build.'/'.$rel; logl("SYNC $src -> $dst"); rr_copy($src,$dst); }
logl("deploy end");
