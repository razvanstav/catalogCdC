<?php
$title = 'Prezență — ' . $child['first_name'];
$total = count($records);
$present = count(array_filter($records, static fn(array $record): bool => $record['status'] === 'present'));
$late = count(array_filter($records, static fn(array $record): bool => $record['status'] === 'late'));
$excused = count(array_filter($records, static fn(array $record): bool => $record['status'] === 'excused'));
$rate = $total > 0 ? (int)round(($present / $total) * 100) : 100;
?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--sage">Participare</span></div>
    <h1 class="page-title">Prezență și participare</h1>
    <p class="page-subtitle">Evidența ședințelor pentru <?= e($child['first_name'] . ' ' . $child['last_name']) ?>.</p>
  </div>
  <div class="page-actions">
    <a href="/parent/conversations" class="btn btn--outline">Anunță o învoire</a>
  </div>
</header>

<section class="metric-grid">
  <article class="metric-card metric-card--sage">
    <div class="metric-value"><?= $rate ?>%</div>
    <div class="metric-label">Rată de prezență</div>
  </article>
  <article class="metric-card">
    <div class="metric-value"><?= $present ?></div>
    <div class="metric-label">Ședințe prezente</div>
  </article>
  <article class="metric-card metric-card--amber">
    <div class="metric-value"><?= $late ?></div>
    <div class="metric-label">Întârzieri</div>
  </article>
  <article class="metric-card">
    <div class="metric-value"><?= $excused ?></div>
    <div class="metric-label">Învoiri aprobate</div>
  </article>
</section>

<section class="card card--flush">
  <div class="table-toolbar">
    <div>
      <h2 class="card-title">Istoric cronologic</h2>
      <p class="card-description">Toate ședințele înregistrate</p>
    </div>
    <span class="badge badge--neutral"><?= $total ?> înregistrări</span>
  </div>

  <div class="divider-list">
    <?php foreach ($records as $record): ?>
      <div class="status-line">
        <span class="status-dot <?= $record['status'] === 'present' ? 'status-dot--sage' : ($record['status'] === 'late' ? 'status-dot--amber' : ($record['status'] === 'absent' ? 'status-dot--rose' : '')) ?>" aria-hidden="true">•</span>
        <div>
          <div class="content-row__title"><?= e($record['lesson_title'] ?: 'Ședință de curs') ?></div>
          <div class="content-row__meta"><?= e($record['group_name']) ?> • <?= format_date_ro($record['lesson_date']) ?></div>
          <?php if (!empty($record['note'])): ?>
            <div class="private-note">
              <div class="private-note__text">Mențiune: <?= e($record['note']) ?></div>
            </div>
          <?php endif; ?>
        </div>
        <div class="row row--center">
          <span class="badge <?= e(attendance_badge_class($record['status'])) ?>"><?= e(attendance_status_label($record['status'])) ?></span>
          <?php if (!empty($record['is_paid'])): ?>
            <span class="badge badge--paid">Plătit</span>
          <?php else: ?>
            <span class="badge badge--unpaid">Neplătit</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (empty($records)): ?>
    <div class="empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">◷</div>
        <div class="empty-state__title">Nicio prezență înregistrată</div>
        <p class="empty-state__text">Istoricul va apărea după primele ședințe.</p>
      </div>
    </div>
  <?php endif; ?>
</section>
