<?php
// $question, $answer, $status, $msg fournis par le contrôleur
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Accueil — Exercice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- MathJax -->
  <script>
    window.MathJax = { tex: { inlineMath: [['\\(','\\)']] }, svg: { fontCache: 'global' } };
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">

  <?php if ($status !== null): ?>
    <div class="alert <?= $status==='ok' ? 'alert-success' : 'alert-danger' ?> mb-3">
      <?= ($msg==='1') ? 'Bonne réponse !' : 'Réponse incorrecte.' ?>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <!-- Ici, intègre ta bulle/markup existant si tu veux -->
      <div class="mb-3" id="stem"><?= $question['stem'] ?></div>

      <?php if (($answer['type'] ?? '') === 'choice' && !empty($answer['choices'])): ?>
        <form action="/api/check" method="post" class="mt-3">
          <input type="hidden" name="question_id" value="<?= (int)$question['id'] ?>">
          <?php foreach ($answer['choices'] as $idx => $ch): ?>
            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="choice" id="c<?= $idx ?>" value="<?= (int)$idx ?>" required>
              <label class="form-check-label" for="c<?= $idx ?>">
                <strong><?= h($ch['label'] ?? chr(65+$idx)) ?>.</strong>
                <span><?= $ch['text_html'] ?? '' ?></span>
              </label>
            </div>
          <?php endforeach; ?>
          <button class="btn btn-primary mt-3">Valider</button>
        </form>
      <?php else: ?>
        <p class="text-muted">Aucun choix disponible pour cette question.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
