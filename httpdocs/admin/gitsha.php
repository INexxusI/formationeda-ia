<?php
$root = dirname(dirname(__DIR__));       // / (racine vhost)
$build = $root . '/git-build';           // /git-build
$git = rtrim($build,'/').'/.git';
$sha = null;
if (is_dir($git)) {
  $head = @file_get_contents($git.'/HEAD');
  if ($head !== false) {
    $head = trim($head);
    if (preg_match('~^[0-9a-f]{40}$~',$head)) $sha = strtolower($head); // detached
    if (!$sha && strpos($head,'ref:')===0) {
      $ref = trim(substr($head,4));
      $refFile = $git.'/'.$ref;
      if (is_file($refFile)) $sha = strtolower(trim(file_get_contents($refFile)));
      if (!$sha && is_file($git.'/packed-refs')) {
        foreach (file($git.'/packed-refs') as $line) {
          if ($line===''||$line[0]==='#') continue;
          if (preg_match('~^([0-9a-f]{40})\s+(.+)$~', trim($line), $m) && $m[2]===$ref) { $sha=strtolower($m[1]); break; }
        }
      }
    }
  }
}
header('Content-Type: text/plain');
echo $sha ?: "";
