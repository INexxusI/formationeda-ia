<?php
// ===== 0) CONFIG =====
$TOKEN_EXPECTED = 'fb_2025_test_937abX';
$LOG_FILE = __DIR__ . '/deploy.log';
$REPO_DIR = dirname(__DIR__); // ex: remonte d'un niveau depuis /admin vers la racine du projet

// ===== 1) LOG HIT =====
function logl($msg){
  file_put_contents($GLOBALS['LOG_FILE'], date('c').' '.$msg."\n", FILE_APPEND);
}
logl('hit '.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI']);

// ===== 2) MÉTHODE POST OBLIGATOIRE =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  logl('405 not POST');
  exit('Method Not Allowed');
}

// ===== 3) TOKEN EN QUERY =====
$token = $_GET['token'] ?? '';
if (!hash_equals($TOKEN_EXPECTED, $token)) {
  http_response_code(401);
  logl('401 bad token');
  exit('Bad token');
}

// ===== 4) LIRE LE PAYLOAD JSON =====
$raw = file_get_contents('php://input'); // JSON, pas $_POST !
$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? $_SERVER['HTTP_X_GITLAB_EVENT'] ?? 'unknown';
logl('payload '.strlen($raw).' bytes; event='.$event);

// (Optionnel) tu peux décoder:
// $data = json_decode($raw, true);

// ===== 5) RÉPONDRE TOUT DE SUITE (<= 2s) =====
ignore_user_abort(true);
header('Content-Type: text/plain');
http_response_code(200);
echo "OK\n";
flush(); // envoie la réponse à GitHub

// ===== 6) DÉPLOIEMENT EN ARRIÈRE-PLAN =====
// Lance un script shell détaché pour éviter le timeout
$cmd = <<<BASH
#!/bin/bash
cd "$REPO_DIR"
{
  echo "---- $(date -Is) DEPLOY START ----"
  whoami
  pwd
  which git
  git rev-parse --show-toplevel 2>&1
  git fetch --all 2>&1
  git reset --hard origin/main 2>&1   # ou 'git pull --rebase'
  # composer install --no-interaction --no-dev --prefer-dist 2>&1
  # npm ci && npm run build 2>&1
  echo "---- $(date -Is) DEPLOY END ----"
} >> "$LOG_FILE" 2>&1
BASH;

$tmp = tempnam(sys_get_temp_dir(), 'deploy_');
file_put_contents($tmp, $cmd);
chmod($tmp, 0755);

// Détaché (ne pas bloquer PHP) :
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  pclose(popen("start /B ".$tmp, "r"));
} else {
  exec($tmp." > /dev/null 2>&1 &");
}
