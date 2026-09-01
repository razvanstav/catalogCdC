<?php $title = 'Obiective — ' . $child['first_name']; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Progres personal</span></div>
    <h1 class="page-title">Obiective de învățare</h1>
    <p class="page-subtitle">Țintele stabilite împreună cu profesorul și <?= e($child['first_name']) ?>, fără comparații publice.</p>
  </div>
</header>

<section class="stack">
  <?php foreach ($goals as $goal): ?>
    <article class="check-item <?= $goal['is_completed'] ? 'is-complete' : '' ?>">
      <div class="check-item__main">
        <span class="check-control <?= $goal['is_completed'] ? 'is-complete' : '' ?>" aria-hidden="true"><?= $goal['is_completed'] ? '✓' : '○' ?></span>
        <div class="content-row__main">
          <h2 class="content-row__title"><?= e($goal['title']) ?></h2>
          <?php if (!empty($goal['target_date'])): ?>
            <div class="content-row__meta">Țintă: <?= format_date_ro($goal['target_date']) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <span class="badge <?= $goal['is_completed'] ? 'badge--sage' : 'badge--amber' ?>"><?= $goal['is_completed'] ? 'Atins' : 'În lucru' ?></span>
    </article>
  <?php endforeach; ?>

  <?php if (empty($goals)): ?>
    <div class="card empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">◎</div>
        <div class="empty-state__title">Nu există obiective încă</div>
        <p class="empty-state__text">Obiectivele personale vor apărea aici după ce sunt stabilite.</p>
      </div>
    </div>
  <?php endif; ?>
</section>
