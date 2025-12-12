<?php
  // includes/head.php
  $page_title = $page_title ?? "JRPG Prépa – Prototype";
?><!doctype html>
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="Prototype JRPG Prépa – structure d'exercices avec Prof IA">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/htmx.org@1.9.12" defer></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="pb-5">
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="/index.php">Test de développement général TDG</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="/index.php">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="/exercices.php">Exercices</a></li>
        <li class="nav-item"><a class="nav-link" href="/prof-ia.php">Prof IA</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
