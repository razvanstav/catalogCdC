<?php $title = 'Rezultatele mele'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Evaluări</span></div>
    <h1 class="page-title">Rezultatele mele</h1>
    <p class="page-subtitle">Punctajele tale și recomandările publicate de profesor.</p>
  </div>
</header>

<section class="stack stack--lg">
  <?php foreach ($results as $result): ?>
    <article class="card result-card">
      <div>
        <div class="page-kicker">
          <span class="badge badge--brand"><?= e($result['group_name']) ?></span>
          <span class="badge badge--neutral"><?= format_date_ro($result['assessment_date']) ?></span>
        </div>
        <h2 class="card-title"><?= e($result['assessment_title']) ?></h2>

        <?php if (!empty($result['published_feedback'])): ?>
          <div class="feedback-card">
            <div class="feedback-card__header"><span class="badge badge--sage">Sfat de la profesor</span></div>
            <p class="feedback-card__text"><?= e($result['published_feedback']) ?></p>
          </div>
        <?php endif; ?>
      </div>
      <div class="result-score" aria-label="Scor <?= number_format((float)$result['score'], 2) ?> din <?= (float)$result['max_score'] ?>">
        <div class="result-score__value"><?= number_format((float)$result['score'], 2) ?></div>
        <div class="result-score__max">din <?= (float)$result['max_score'] ?></div>
      </div>
    </article>
  <?php endforeach; ?>

  <?php if (empty($results)): ?>
    <div class="card empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">🎓</div>
        <div class="empty-state__title">Nicio evaluare publicată</div>
        <p class="empty-state__text">Rezultatele tale vor apărea aici după publicare.</p>
      </div>
    </div>
  <?php endif; ?>
</section>
