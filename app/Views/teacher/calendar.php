<?php
$title = 'Orar Săptămânal';
$week = $week ?? (new \App\Repositories\GroupRepository())->getWeekCalendar('today');
?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Tab 3 • Orar</span></div>
    <h1 class="page-title">Orar săptămânal (Duminică – Sâmbătă)</h1>
    <p class="page-subtitle">Săptămâna în curs cu instanțierea automată a ședințelor recurente. Apasă pe orice oră pentru a consemna prezența și plățile în Tabul 4 (Ședințe).</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn--primary" data-modal-open="modal-add-calendar-schedule">＋ Adaugă oră recurentă</button>
  </div>
</header>

<section class="card card--flat">
  <div class="row row--between cluster">
    <div class="cluster">
      <a href="/teacher/calendar?date=<?= e($week['prev_week_date']) ?>" class="btn btn--ghost btn--sm" title="Săptămâna anterioară">← Săpt. anterioară</a>
      <a href="/teacher/calendar?date=today" class="btn <?= $week['is_current_week'] ? 'btn--sage' : 'btn--outline' ?> btn--sm">Săptămâna curentă</a>
      <a href="/teacher/calendar?date=<?= e($week['next_week_date']) ?>" class="btn btn--ghost btn--sm" title="Săptămâna următoare">Săpt. viitoare →</a>
      <span class="badge badge--neutral"><?= e($week['formatted_range']) ?></span>
    </div>

    <form action="/teacher/calendar" method="GET" class="cluster">
      <label class="form-label sr-only" for="calendar-jump-date">Sari la o anumită dată</label>
      <input type="date" id="calendar-jump-date" name="date" value="<?= e($week['sunday_date']) ?>" class="form-control" data-submit-on-change>
      <noscript><button type="submit" class="btn btn--outline btn--sm">Mergi</button></noscript>
    </form>
  </div>
</section>

<section class="schedule-grid">
  <?php foreach ($week['days'] as $day): ?>
    <article class="card schedule-day <?= $day['is_today'] ? 'week-schedule-day--today' : '' ?>">
      <header class="schedule-day__header">
        <div>
          <h2 class="schedule-day__name"><?= e($day['day_name']) ?></h2>
          <span class="content-row__meta"><?= e($day['formatted_date']) ?></span>
        </div>
        <?php if ($day['is_today']): ?>
          <span class="badge badge--brand">Azi</span>
        <?php else: ?>
          <span class="badge badge--neutral"><?= count($day['lessons']) ?> ședințe</span>
        <?php endif; ?>
      </header>

      <div class="schedule-list">
        <?php foreach ($day['lessons'] as $lesson): ?>
          <?php
          $tone = ui_tone_class($lesson['group_id'] ?? $lesson['group_name']);
          $hasAttendance = ((int)($lesson['attendance_count'] ?? 0) > 0);
          ?>
          <div class="schedule-item <?= e($tone) ?>">
            <div class="schedule-item__title"><?= e($lesson['group_name']) ?></div>
            <div class="schedule-item__time"><?= e($lesson['start_time']) ?>–<?= e($lesson['end_time']) ?></div>
            <div class="schedule-item__meta">
              <?= e($lesson['title']) ?>
              <?php if (!empty($lesson['lesson_notes'])): ?>
                • <?= e($lesson['lesson_notes']) ?>
              <?php endif; ?>
            </div>

            <div class="cluster">
              <?php if ($hasAttendance): ?>
                <span class="badge badge--paid">✓ Prezență salvată</span>
              <?php else: ?>
                <span class="badge badge--amber">De consemnat</span>
              <?php endif; ?>
              <a href="/teacher/attendance?lesson_id=<?= e($lesson['id']) ?>" class="btn btn--outline btn--sm">Deschide în Ședințe ➔</a>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($day['lessons'])): ?>
          <div class="schedule-empty">Fără ore programate în această zi</div>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
</section>

<div id="modal-add-calendar-schedule" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="calendar-schedule-title">
    <h2 class="modal-title" id="calendar-schedule-title">Adaugă oră în orar</h2>
    <p class="modal-description">Alege grupa, ziua din săptămână și durata. Această oră devine recurentă și generează automat ședințele în fiecare săptămână.</p>

    <form action="/teacher/calendar/schedule" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <input type="hidden" name="return_date" value="<?= e($week['sunday_date']) ?>">

      <div class="form-group">
        <label class="form-label" for="cal_group_id">Grupa</label>
        <select id="cal_group_id" name="group_id" class="form-control" required>
          <option value="">-- Alege grupa --</option>
          <?php foreach ($groups as $grp): ?>
            <option value="<?= e($grp['id']) ?>"><?= e($grp['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="cal_day_of_week">Ziua din săptămână</label>
        <select id="cal_day_of_week" name="day_of_week" class="form-control" required>
          <option value="7">Duminică</option>
          <option value="1" selected>Luni</option>
          <option value="2">Marți</option>
          <option value="3">Miercuri</option>
          <option value="4">Joi</option>
          <option value="5">Vineri</option>
          <option value="6">Sâmbătă</option>
        </select>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="cal_start_time">Ora de început</label>
          <input type="time" id="cal_start_time" name="start_time" value="09:00" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="cal_duration">Durată ședință</label>
          <select id="cal_duration" name="duration_minutes" class="form-control">
            <option value="60">1 oră (60 minute)</option>
            <option value="90" selected>1 oră și 30 minute (90 min)</option>
            <option value="120">2 ore (120 minute)</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="cal_room">Cabinet didactic / Sală</label>
        <input type="text" id="cal_room" name="room_or_link" class="form-control" value="Cabinet Informatică" placeholder="Laborator Informatică">
      </div>

      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Salvează în orar</button>
      </div>
    </form>
  </section>
</div>
