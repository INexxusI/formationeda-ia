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
  <script>
    window.MathJax = { tex: { inlineMath: [['\\(','\\)']] }, svg: { fontCache: 'global' } };
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js"></script>

  <style>
    /* Fond clair doux + typographie */
    :root { --bg:#f7f8fb; --ink:#111827; --muted:#6b7280; --brand:#2563eb; }
    body { background: var(--bg); color: var(--ink); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"; }
    .navbar { background: #ffffff; border-bottom: 1px solid #e5e7eb; }
    .navbar .navbar-brand { color: var(--ink); }
    .navbar .btn { border-color:#d1d5db; color:#374151; }
    .pill { background:#e0e7ff; color:#3730a3; border-radius:9999px; padding:.2rem .6rem; font-size:.75rem; font-weight:600; }
    /* JRPG grid peut rester dans votre CSS, ceci est juste un garde-fou léger */
    .jrpg-grid { display:grid; grid-template-columns: 1.35fr .9fr; gap:1.25rem; }
    @media (max-width: 992px){ .jrpg-grid { grid-template-columns: 1fr; } }
    .panel { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .title { font-weight:800; letter-spacing:.3px; }
    .accent { color:#0ea5e9; } .accent-2 { color:#f97316; }
    .btn-cta { background: var(--brand); color:#fff; border:none; }
    .btn-cta:hover { background:#1d4ed8; }
    .btn-ghost { border:1px solid #d1d5db; }
	
.prof-head {
  display:flex;
  gap:1.5rem;
  align-items:center;
}

/* Gros avatar rond */
.prof-head .avatar-img {
  width:180px;
  height:180px;
  border-radius:50%;
  object-fit:cover;
  border:2px solid #e5e7eb;
  box-shadow:0 10px 25px rgba(15,23,42,0.15);
}

/* Bulle bleue utilisée sur l'accueil */
.bubble-prof {
  flex: 1;
  background:#e9f5ff;
  border-radius:24px;
  padding:1.5rem 1.75rem;
  font-size:1rem;
  line-height:1.4;
  box-shadow:0 2px 6px rgba(15,23,42,0.08);
  position: relative; /* pour pouvoir placer le triangle */
}

/* Triangle de la bulle, même couleur que la bulle */
.bubble-prof::after {
  content: "";
  position: absolute;
  left: -16px;         /* décale le triangle vers l’avatar */
  top: 24px;           /* ajuste la hauteur si besoin */
  width: 0;
  height: 0;
  border: 10px solid transparent;
  border-right-color: #e9f5ff;  /* même couleur que la bulle */
}


/* On garde .bubble si tu l'utilises ailleurs */
.bubble {
  background:#fff;
  border:1px solid #e5e7eb;
  border-radius:16px;
  padding:12px 14px;
  box-shadow:0 1px 2px rgba(0,0,0,.03);
}


/* Avatar un peu plus petit sur mobile */
@media (max-width: 992px){
  .prof-head .avatar-img {
    width:140px;
    height:140px;
  }
}

.title { font-weight:800; letter-spacing:.3px; }

  </style>
</head>
<body>

require __DIR__ . '/../partials/navbar.php';

  <main class="py-4">
    <div class="container">
      <?= $content ?>
    </div>
  </main>

  <footer class="py-4 border-top bg-white">
    <div class="container d-flex justify-content-between align-items-center">
      <small class="text-muted">© FormationEDA – Prototype JRPG</small>
      <div class="d-flex gap-3 small">
        <span>Thème clair</span>
        <a class="link-secondary text-decoration-none" href="https://getbootstrap.com/">Bootstrap 5</a>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/app.js?v=2025-10-09-1"></script>
</body>
</html>
