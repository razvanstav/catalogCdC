<?php
$title = 'Calendar săptămânal';
$days = [1 => 'Luni', 2 => 'Marți', 3 => 'Miercuri', 4 => 'Joi', 5 => 'Vineri', 6 => 'Sâmbătă'];
?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Săptămâna ta</span></div>
    <h1 class="page-title">Calendar săptămânal</h1>
    <p class="page-subtitle">Grupele, intervalele și locurile de întâlnire într-o vedere clară, potrivită și pentru telefon.</p>
  </div>
</header>

<section class="schedule-grid">
  <?php foreach ($days as $dayNumber => $dayName): ?>
    <?php $daySchedules = array_values(array_filter($schedules, static fn(array $schedule): bool => (int)$schedule['day_of_week'] === $dayNumber)); ?>
    <article class="card schedule-day">
      <header class="schedule-day__header"><h2 class="schedule-day__name"><?= e($dayName) ?></h2><span class="badge badge--neutral"><?= count($daySchedules) ?> ședințe</span></header>
      <div class="schedule-list">
        <?php foreach ($daySchedules as $schedule): ?>
          <?php $tone = ui_tone_class($schedule['group_id'] ?? $schedule['group_name']); ?>
          <div class="schedule-item <?= e($tone) ?>">
            <div class="schedule-item__title"><?= e($schedule['group_name']) ?></div>
            <div class="schedule-item__time"><?= e($schedule['start_time']) ?>–<?= e($schedule['end_time']) ?></div>
            <?php if (!empty($schedule['room_or_link'])): ?><div class="schedule-item__meta"><?= e($schedule['room_or_link']) ?></div><?php endif; ?>
          </div>
        <?php endforeach; ?>
        <?php if (empty($daySchedules)): ?><div class="schedule-empty">Fără ședințe programate</div><?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
</section>
