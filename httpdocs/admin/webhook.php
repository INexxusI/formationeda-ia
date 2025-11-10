<?php
// --- Config ---
$TOKEN = 'fb_2025_test_937abX';
date_default_timezone_set('America/Toronto');

// --- Auth ---
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== $TOKEN) { http_response_code(401); echo "Bad token\n"; exit; }

// --- Lit le JSON GitHub ---
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// --- Extrait ce qui nous intéresse ---
$after = $data['after'] ?? null; // SHA du push
$pushTs = isset($data['head_commit']['timestamp']) ? strtotime($data['head_commit']['timestamp']) : time();
$files = [];
if (!empty($data['head_commit'])) {
  foreach (['added','modified'] as $k) {
    if (!empty($data['head_commit'][$k]) && is_array($data['head_commit'][$k])) {
      foreach ($data['head_commit'][$k] as $p) {
        if (preg_match('~^(httpdocs|app|config)/~', $p)) $files[] = $p;
      }
    }
  }
}

// --- Sauvegarde “la dernière demande” (écrase) ---
$dir = __DIR__;
file_put_contents($dir.'/last.json', json_encode([
  'sha'=>$after, 'ts'=>$pushTs, 'files'=>$files, 'at'=>date('c'),
], JSON_PRETTY_PRINT));

// --- Réponse immédiate ---
header('Content-Type: text/plain');
echo "queued\n";
