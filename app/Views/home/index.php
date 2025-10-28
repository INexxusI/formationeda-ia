<?php
use App\Services\AiStyleService;
$styles = AiStyleService::profiles();
?>
<div class="container">
  <div class="jrpg-grid">

    <!-- ======== PANEL GAUCHE : questions ======== -->
    <section class="panel left animate-left">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <h1 class="title m-0">Prépa <span class="accent">TENS</span>/<span class="accent-2">TDG</span></h1>
        <span class="pill">Prototype</span>
      </div>

      <?php if (!empty($status)): ?>
        <div class="alert <?= $status==='ok' ? 'alert-success' : 'alert-danger' ?> mb-3">
          <?= ($msg==='1') ? 'Bonne réponse !' : 'Réponse incorrecte.' ?>
        </div>
      <?php endif; ?>

      <!-- Question depuis la base -->
      <div class="fw-bold mb-1">Question</div>
      <div class="border border-3 p-3 rounded-3">
        <div id="stem" class="mb-3"><?= $question['stem'] ?? '<em>Aucune question</em>' ?></div>

        <?php if (($answer['type'] ?? '') === 'choice' && !empty($answer['choices'])): ?>
          <form action="/api/check" method="post" class="mt-2">
            <input type="hidden" name="question_id" value="<?= (int)($question['id'] ?? 0) ?>">

            <?php foreach ($answer['choices'] as $idx => $ch): ?>
              <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="choice" id="c<?= (int)$idx ?>" value="<?= (int)$idx ?>" required>
                <label class="form-check-label" for="c<?= (int)$idx ?>">
                  <strong><?= htmlspecialchars($ch['label'] ?? chr(65+$idx)) ?>.</strong>
                  <span><?= $ch['text_html'] ?? '' ?></span>
                </label>
              </div>
            <?php endforeach; ?>

            <button class="btn btn-cta mt-2">Valider</button>
          </form>
        <?php else: ?>
          <p class="text-muted mb-0">Aucun choix disponible.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- ======== PANEL DROIT : Prof IA ======== -->
    <aside class="panel right animate-right">
      <div class="prof-head mb-3">
        <img class="avatar-img" src="/assets/img/prof-ai.png" alt="Prof IA">
        <div>
          <div class="small text-muted">Ton tuteur</div>
          <div class="fw-bold" id="profTone">Prof IA</div>
        </div>
      </div>

<div class="bubble-prof mb-4">
  Bienvenue ! Je serai ton accompagnateur.  
  <br>Choisis ton style de prof ci-dessous.
</div>

      <div class="mb-3"><span class="prof-name">Choisis ton Prof IA</span></div>
      <div class="d-flex gap-2 flex-wrap">
        <?php foreach (['arielle','max','noa','sora'] as $k): $p = $styles[$k]; ?>
          <button class="avatar <?= $k==='arielle'?'sel':'' ?>" data-prof="<?= $k ?>" onclick="pick(this)" aria-label="<?= htmlspecialchars($p['name']) ?>">
            <?php require __DIR__ . '/../partials/ai-prof-avatars.php'; ?>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="prof-card mt-3">
        <div class="small text-uppercase fw-bold mb-1">Style sélectionné</div>
        <div id="styleOut" class="prof-name">Arielle la Sage — détaillé</div>
      </div>
    </aside>

  </div>
</div>

<script>
  const STYLES = <?= json_encode($styles, JSON_UNESCAPED_UNICODE) ?>;
  function pick(btn){
    document.querySelectorAll('.avatar').forEach(a=>a.classList.remove('sel'));
    btn.classList.add('sel');
    const key = btn.dataset.prof; const s = STYLES[key];
    document.getElementById('styleOut').textContent = s.name + (key==='arielle'?' — détaillé': key==='max'?' — concis': key==='noa'?' — humoristique':' — exigeant');
    document.getElementById('profTone').textContent = s.name;
    window._current = key;
  }
</script>



