<?php
$title = 'Orar — ' . $child['first_name'];
$days = [1 => 'Luni', 2 => 'Marți', 3 => 'Miercuri', 4 => 'Joi', 5 => 'Vineri', 6 => 'Sâmbătă', 7 => 'Duminică'];
$isVacation = \App\Support\Settings::isVacationMode();
?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Program recurent</span></div>
    <h1 class="page-title">Orar săptămânal</h1>
    <p class="page-subtitle">Cursurile și ședințele lui <?= e($child['first_name'] . ' ' . $child['last_name']) ?>.</p>
  </div>
</header>

<?php if ($isVacation): ?>
  <article class="vacation-card">
    <h2 class="vacation-card__title">🌴 Suntem în Vacanță!</h2>
    <p class="vacation-card__text"><?= e(\App\Support\Settings::getVacationMessage()) ?></p>
    <span class="badge badge--amber">Ședințele recurente sunt oprite temporar pe durata vacanței</span>
  </article>
<?php endif; ?>

<section class="schedule-grid">
  <?php foreach ($days as $dayNumber => $dayName): ?>
    <?php $daySchedules = array_values(array_filter($schedules, static fn(array $schedule): bool => (int)$schedule['day_of_week'] === $dayNumber)); ?>
    <article class="card schedule-day">
      <div class="schedule-day__header">
        <h2 class="schedule-day__name"><?= e($dayName) ?></h2>
        <span class="badge badge--neutral"><?= count($daySchedules) ?> activități</span>
      </div>
      <div class="schedule-list">
        <?php foreach ($daySchedules as $schedule): ?>
          <?php $tone = ui_tone_class($schedule['group_id'] ?? $schedule['group_name']); ?>
          <div class="schedule-item <?= e($tone) ?>">
            <div class="schedule-item__title"><?= e($schedule['group_name']) ?></div>
            <div class="schedule-item__time"><?= e($schedule['start_time']) ?>–<?= e($schedule['end_time']) ?></div>
            <?php if (!empty($schedule['room_or_link'])): ?>
              <div class="schedule-item__meta"><?= e($schedule['room_or_link']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <?php if (empty($daySchedules)): ?>
          <div class="empty-state">
            <div><p class="empty-state__text">Zi fără ședințe</p></div>
          </div>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
</section>
