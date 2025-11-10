<?php
// /httpdocs/admin/gitsha.php (debuggable)
header('Content-Type: text/plain');
$root  = dirname(dirname(__DIR__));      // /
$build = $root . '/git-build';
$git   = rtrim($build, '/').'/.git';

$debug = isset($_GET['debug']);

if ($debug) {
  echo "ROOT=$root\nBUILD=$build\nGIT=$git\n";
  echo "is_dir(BUILD)=".(is_dir($build)?'1':'0')."\n";
  echo "is_dir(GIT)=".(is_dir($git)?'1':'0')."\n";
}

$sha = '';

// 1) HEAD (detached ou ref)
if (is_dir($git)) {
  $headPath = $git.'/HEAD';
  $head = @file_get_contents($headPath);
  if ($debug) echo "HEAD exists=".((file_exists($headPath))?'1':'0')." len=".strlen((string)$head)."\n";
  if ($head !== false) {
    $head = trim($head);
    if (preg_match('~^[0-9a-f]{40}$~', $head)) {
      $sha = strtolower($head); // detached
      if ($debug) echo "detached HEAD=$sha\n";
    } elseif (strpos($head,'ref:') === 0) {
      $ref = trim(substr($head,4)); // ex: refs/heads/main
      $refFile = $git.'/'.$ref;
      if (is_file($refFile)) {
        $sha = strtolower(trim(file_get_contents($refFile)));
        if ($debug) echo "refFile $ref -> $sha\n";
      } else {
        // 2) packed-refs
        $packed = $git.'/packed-refs';
        if (is_file($packed)) {
          if ($debug) echo "using packed-refs\n";
          foreach (file($packed) as $line) {
            if ($line==='' || $line[0]==='#') continue;
            if (preg_match('~^([0-9a-f]{40})\s+(.+)$~', trim($line), $m)) {
              if ($m[2] === $ref) { $sha = strtolower($m[1]); break; }
            }
          }
        } else {
          if ($debug) echo "refFile missing and no packed-refs\n";
        }
      }
    } else {
      if ($debug) echo "HEAD content not recognized: '$head'\n";
    }
  } else {
    if ($debug) echo "cannot read HEAD\n";
  }
}

if (!$debug) {
  echo $sha; // normal mode: affiche juste le SHA (ou vide)
} else {
  echo "FINAL_SHA=".($sha ?: '(empty)')."\n";
}
