<?php $title = 'Aprecieri — ' . $child['first_name']; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--sage">Feedback formativ</span></div>
    <h1 class="page-title">Aprecieri de la profesor</h1>
    <p class="page-subtitle">Mesaje despre efort, progres și lucrurile pe care <?= e($child['first_name']) ?> le face bine.</p>
  </div>
</header>

<section class="stack stack--lg">
  <?php foreach ($feedbacks as $feedback): ?>
    <article class="feedback-card">
      <div class="feedback-card__header">
        <span class="badge badge--sage">Profesor</span>
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
        <p class="empty-state__text">Mesajele formative vor apărea aici imediat ce profesorul le publică.</p>
      </div>
    </div>
  <?php endif; ?>
</section>
