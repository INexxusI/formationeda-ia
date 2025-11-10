<?php
// ===== Config =====
date_default_timezone_set('America/Toronto');
$LOG  = __DIR__.'/deploy.log';
function logl($m){ file_put_contents(__DIR__.'/deploy.log', date('c')." $m\n", FILE_APPEND); }

// ===== Chemins =====
$scriptDir = __DIR__;             // /httpdocs/admin
$httpdocs  = dirname($scriptDir); // /httpdocs
$root      = dirname($httpdocs);  // /
$build     = $root.'/git-build';  // /git-build

// ===== Lit la “queue” =====
$lastFile = __DIR__.'/last.json';
$doneFile = __DIR__.'/processed.sha';

if (!is_file($lastFile)) { logl("no-op: no last.json"); exit; }
$last = json_decode(file_get_contents($lastFile), true);
$expectedSha = strtolower(trim($last['sha'] ?? ''));
$pushTs      = intval($last['ts'] ?? time());
$files       = is_array($last['files'] ?? null) ? $last['files'] : [];

if (!$expectedSha) { logl("no-op: missing sha"); exit; }

// déjà traité ?
$doneSha = is_file($doneFile) ? trim(file_get_contents($doneFile)) : '';
if ($doneSha === $expectedSha) { logl("no-op: sha already processed {$expectedSha}"); exit; }

// ===== Outils Git (lecture SHA courant) =====
function git_current_sha($repoPath){
  $git = rtrim($repoPath,'/').'/.git';
  if (!is_dir($git)) return null;
  $head = @file_get_contents($git.'/HEAD'); if ($head===false) return null; $head = trim($head);
  if (preg_match('~^[0-9a-f]{40}$~',$head)) return strtolower($head);
  if (strpos($head,'ref:')===0){
    $ref = trim(substr($head,4));
    $refFile = $git.'/'.$ref;
    if (is_file($refFile)) {
      $sha = trim(file_get_contents($refFile));
      if (preg_match('~^[0-9a-f]{40}$~',$sha)) return strtolower($sha);
    }
    $packed = $git.'/packed-refs';
    if (is_file($packed)){
      foreach (file($packed) as $line){
        if ($line===''||$line[0]==='#') continue;
        if (preg_match('~^([0-9a-f]{40})\s+(.+)$~',trim($line),$m)) {
          if ($m[2]===$ref) return strtolower($m[1]);
        }
      }
    }
  }
  return null;
}

// ===== Attente robuste (SHA OU fichiers présents/récents) =====
$ok=false; $waited=0;
for($i=0;$i<90;$i++){ // 90s max
  $sha = git_current_sha($build);
  $shaOk = ($sha && $sha === $expectedSha);
  $filesOk = true;
  if ($files){
    $filesOk=false; $seen=0;
    foreach($files as $rel){
      $probe = $build.'/'.$rel;
      if (file_exists($probe) && @filemtime($probe) >= $pushTs - 5) $seen++;
    }
    if ($seen === count($files)) $filesOk=true;
  }
  if ($shaOk || $filesOk){ $ok=true; break; }
  sleep(1); $waited++;
}
logl("ready=".($ok?'1':'0')." waited={$waited}s expectedSha={$expectedSha} currentSha=".git_current_sha($build)." filesTracked=".count($files));
if (!$ok){ logl("ABORT deploy: repo not ready"); exit; }

// ===== Copie (safe) git-build -> prod =====
function rr_mkdir($d){ if(!is_dir($d)) mkdir($d,0755,true); }
function rr_copy($src,$dst){
  if(!is_dir($src)) { logl("skip: $src absent"); return; }
  rr_mkdir($dst);
  foreach(scandir($src) as $f){
    if($f==='.'||$f==='..') continue;
    $s=$src.DIRECTORY_SEPARATOR.$f; $d=$dst.DIRECTORY_SEPARATOR.$f;
    if (is_dir($s)) rr_copy($s,$d);
    else { if(!copy($s,$d)) logl("copy FAIL $s -> $d"); else @touch($d,filemtime($s)); }
  }
}

logl("deploy start: ROOT=$root BUILD=$build");
$targets = ['httpdocs'=>$root.'/httpdocs','app'=>$root.'/app','config'=>$root.'/config'];
foreach($targets as $rel=>$dst){ $src=$build.'/'.$rel; logl("SYNC $src -> $dst"); rr_copy($src,$dst); }
file_put_contents($doneFile, $expectedSha);
logl("deploy end sha={$expectedSha}");
