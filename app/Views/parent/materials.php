<?php $title = 'Materiale didactice — ' . $child['first_name']; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--sage">Resurse didactice</span></div>
    <h1 class="page-title">Materiale didactice</h1>
    <p class="page-subtitle">Fișe, suporturi de teorie, linkuri și resurse pentru <?= e($child['first_name'] . ' ' . $child['last_name']) ?>.</p>
  </div>
</header>

<section class="grid-3">
  <?php foreach ($materials as $material): ?>
    <?php $tone = ui_tone_class($material['group_name'] ?? $material['id']); ?>
    <article class="card entity-card <?= e($tone) ?>">
      <span class="entity-card__accent" aria-hidden="true"></span>
      <div class="entity-card__head">
        <span class="badge badge--sage"><?= strtoupper(e($material['file_type'])) ?></span>
        <span class="badge badge--neutral"><?= e($material['group_name']) ?></span>
      </div>
      <div>
        <h2 class="entity-card__title"><?= e($material['title']) ?></h2>
        <p class="entity-card__description">Resursă recomandată de profesor pentru recapitulare și aprofundare.</p>
      </div>
      <footer class="entity-card__footer">
        <span class="tone-dot" aria-hidden="true"></span>
        <a href="<?= e($material['url'] ?: '#') ?>" target="_blank" rel="noreferrer" class="btn btn--outline btn--sm">Deschide resursa</a>
      </footer>
    </article>
  <?php endforeach; ?>

  <?php if (empty($materials)): ?>
    <div class="card empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">▤</div>
        <div class="empty-state__title">Nicio resursă publicată încă</div>
        <p class="empty-state__text">Materialele încărcate de profesor pentru grupele copilului vor apărea aici.</p>
      </div>
    </div>
  <?php endif; ?>
</section>
