<?php
  // Réponse factice pour démontrer HTMX
  $msg = trim($_POST['message'] ?? '');
  if ($msg === '') {
    echo '<div class="text-muted">Aucune question reçue.</div>';
    exit;
  }
  $safe = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
  echo '<div class="bubble teacher"><strong>Prof IA :</strong> Tu as demandé : <em>' . $safe . '</em>.<br>Dans la vraie app, je réponds ici 👋</div>';
