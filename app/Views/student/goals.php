<?php $title = 'Obiectivele mele'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Pași personali</span></div>
    <h1 class="page-title">Obiectivele mele</h1>
    <p class="page-subtitle">Stabilește ținte clare și urmărește progresul fără să te compari cu altcineva.</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn--primary" data-modal-open="modal-add-goal">Adaugă obiectiv</button>
  </div>
</header>

<section class="stack">
  <?php foreach ($goals as $goal): ?>
    <article class="check-item <?= $goal['is_completed'] ? 'is-complete' : '' ?>">
      <div class="check-item__main">
        <a href="/student/goals/toggle/<?= e($goal['id']) ?>" class="check-control <?= $goal['is_completed'] ? 'is-complete' : '' ?>" aria-label="<?= $goal['is_completed'] ? 'Marchează obiectivul ca nefinalizat' : 'Marchează obiectivul ca finalizat' ?>"><?= $goal['is_completed'] ? '✓' : '○' ?></a>
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
        <div class="empty-state__title">Primul obiectiv te așteaptă</div>
        <p class="empty-state__text">Alege un pas mic, clar și realizabil.</p>
        <div class="empty-state__actions">
          <button type="button" class="btn btn--primary" data-modal-open="modal-add-goal">Adaugă obiectiv</button>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<div id="modal-add-goal" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="goal-modal-title">
    <h2 class="modal-title" id="goal-modal-title">Adaugă un obiectiv</h2>
    <p class="modal-description">Un obiectiv bun este scurt, concret și ușor de urmărit.</p>

    <form action="/student/goals" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label" for="goal_title">Titlul obiectivului</label>
        <input type="text" id="goal_title" name="title" class="form-control" placeholder="Exemplu: Rezolv 15 probleme din culegere" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="target_date">Data țintă</label>
        <input type="date" id="target_date" name="target_date" class="form-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Salvează obiectivul</button>
      </div>
    </form>
  </section>
</div>
