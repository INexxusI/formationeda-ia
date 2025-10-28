<?php
use App\Services\AiStyleService;
$styles = AiStyleService::profiles();
?>
<div class="container">
  <div class="jrpg-grid">
    <section class="panel left animate-left">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="title m-0">Équation à un pas</h1>
        <span class="badge text-bg-primary" style="--bs-bg-opacity:1">Démo</span>
      </div>
      <p class="mb-1">Résous :</p>
      <div class="code">x + 7 = 10</div>
      <div class="row g-2 align-items-center mt-2">
        <div class="col-auto"><input id="ans2" class="form-control" placeholder="Réponse"></div>
        <div class="col-auto"><button class="btn btn-dark" onclick="check2()">Vérifier</button></div>
        <div class="col"><small id="out2" class="text-muted"></small></div>
      </div>
      <hr>
      <div class="bubble" id="explain">Indice : soustrais 7 des deux côtés.</div>
      <div class="mt-2 d-flex gap-2">
        <button class="btn btn-outline-dark btn-sm" onclick="setExplain('short')">Court</button>
        <button class="btn btn-outline-dark btn-sm" onclick="setExplain('deep')">Détaillé</button>
      </div>
    </section>

    <aside class="panel right animate-right">
      <div class="mb-2 fw-bold">Profs IA</div>
      <div class="d-flex gap-2 flex-wrap">
        <?php foreach (['arielle','max','noa','sora'] as $k): $p = $styles[$k]; ?>
          <button class="avatar <?= $k==='arielle'?'sel':'' ?>" data-prof="<?= $k ?>" onclick="pick2(this)" aria-label="<?= htmlspecialchars($p['name']) ?>">
            <?php require __DIR__ . '/../partials/ai-prof-avatars.php'; ?>
          </button>
        <?php endforeach; ?>
      </div>
      <hr>
      <div class="bubble" id="welcome">Prof IA : Bienvenue ! Je t’accompagne pendant la leçon.</div>
    </aside>
  </div>
</div>

<script>
  const TEXTS = <?= json_encode($styles, JSON_UNESCAPED_UNICODE) ?>;
  let cur = 'arielle';
  function pick2(btn){
    document.querySelectorAll('.avatar').forEach(b=>b.classList.remove('sel'));
    btn.classList.add('sel');
    cur = btn.dataset.prof;
    document.getElementById('welcome').textContent = TEXTS[cur].name + ' : Bienvenue !';
  }
  async function check2(){
    const v = (document.getElementById('ans2').value||'').trim();
    const resp = await fetch('/api/check', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams({answer:v}) });
    const data = await resp.json();
    document.getElementById('out2').textContent = data.message;
  }
  function setExplain(kind){
    document.getElementById('explain').textContent = TEXTS[cur][kind];
  }
</script>
