<?php $title = 'Aprecieri primite'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--sage">Ce faci bine</span></div>
    <h1 class="page-title">Aprecieri primite</h1>
    <p class="page-subtitle">Mesajele profesorului despre efort, progres și momentele tale bune.</p>
  </div>
</header>

<section class="stack stack--lg">
  <?php foreach ($feedbacks as $feedback): ?>
    <article class="feedback-card">
      <div class="feedback-card__header">
        <span class="badge badge--sage">De la profesor</span>
        <small><?= format_date_ro($feedback['created_at']) ?></small>
      </div>
      <p class="feedback-card__text"><?= e($feedback['content']) ?></p>
    </article>
  <?php endforeach; ?>

  <?php if (empty($feedbacks)): ?>
    <div class="card empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">✦</div>
        <div class="empty-state__title">Nicio apreciere publicată încă</div>
        <p class="empty-state__text">Mesajele profesorului vor apărea aici.</p>
      </div>
    </div>
  <?php endif; ?>
</section>
