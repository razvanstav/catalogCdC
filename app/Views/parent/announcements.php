<?php $title = 'Anunțuri — ' . $child['first_name']; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Informări</span></div>
    <h1 class="page-title">Anunțuri și noutăți</h1>
    <p class="page-subtitle">Mesajele generale pentru grupele în care participă <?= e($child['first_name']) ?>.</p>
  </div>
</header>

<section class="stack stack--lg">
  <?php foreach ($announcements as $announcement): ?>
    <?php $tone = ui_tone_class($announcement['group_name'] ?? $announcement['id']); ?>
    <article class="card card--interactive <?= e($tone) ?>">
      <div class="card-header">
        <div class="card-header__copy">
          <div class="page-kicker">
            <span class="badge badge--brand"><?= e($announcement['group_name'] ?: 'Anunț general') ?></span>
            <span class="badge badge--neutral"><?= format_date_ro($announcement['created_at']) ?></span>
          </div>
          <h2 class="card-title"><?= e($announcement['title']) ?></h2>
        </div>
        <span class="tone-dot" aria-hidden="true"></span>
      </div>
      <p class="card-description"><?= nl2br(e($announcement['content'])) ?></p>
    </article>
  <?php endforeach; ?>

  <?php if (empty($announcements)): ?>
    <div class="card empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">◌</div>
        <div class="empty-state__title">Niciun anunț nou</div>
        <p class="empty-state__text">Informările publicate de profesor vor apărea aici.</p>
      </div>
    </div>
  <?php endif; ?>
</section>
