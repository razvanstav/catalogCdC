<?php
$title = 'Prezența mea';
$total = count($records);
$present = count(array_filter($records, static fn(array $record): bool => $record['status'] === 'present'));
$late = count(array_filter($records, static fn(array $record): bool => $record['status'] === 'late'));
$excused = count(array_filter($records, static fn(array $record): bool => $record['status'] === 'excused'));
$rate = $total > 0 ? (int)round(($present / $total) * 100) : 100;
?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--sage">Participare la ore</span></div>
    <h1 class="page-title">Prezența mea la ședințe</h1>
    <p class="page-subtitle">Evidența participării tale la toate cursurile și atelierele din orar.</p>
  </div>
  <div class="page-actions">
    <a href="/student/conversations" class="btn btn--outline">Anunță profesorul</a>
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
      <h2 class="card-title">Istoric ședințe</h2>
      <p class="card-description">Toate ședințele consemnate de profesor</p>
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
        <div class="status-badge-wrap">
          <?php if ($record['status'] === 'present'): ?>
            <span class="badge badge--sage">Prezent</span>
          <?php elseif ($record['status'] === 'late'): ?>
            <span class="badge badge--amber">Întârziat</span>
          <?php elseif ($record['status'] === 'excused'): ?>
            <span class="badge badge--neutral">Învoit</span>
          <?php else: ?>
            <span class="badge badge--rose">Absent</span>
          <?php endif; ?>

          <?php if (isset($record['is_paid']) && (int)$record['is_paid'] === 1): ?>
            <span class="badge badge--paid">Achitat</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (empty($records)): ?>
      <div class="empty-state">
        <div>
          <div class="empty-state__icon" aria-hidden="true">✓</div>
          <div class="empty-state__title">Nicio prezență consemnată încă</div>
          <p class="empty-state__text">Ședințele bifate de profesor vor apărea în această listă.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
