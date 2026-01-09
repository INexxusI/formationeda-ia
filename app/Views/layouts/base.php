<?php
/** @var string $title */
/** @var string $content */
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Prépa') ?></title>

  <!-- Fonts + Bootstrap -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Votre thème (met un cache-busting si besoin) -->
  <link rel="stylesheet" href="/assets/css/main.css?v=2025-10-09-1">

  <!-- MathJax pour \( ... \) -->
	
<?php require __DIR__ . '/../partials/mathjax.php'; ?>

  
</head>
<body>

<?php require __DIR__ . '/../partials/navbar.php'; ?>

  <main class="py-4">
    <div class="container">
      <?= $content ?>
    </div>
  </main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<?php require __DIR__ . '/../partials/scripts.php'; ?>

</body>
</html>
