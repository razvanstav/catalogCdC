<?php $title = 'Ședințe și planificare'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Planificare</span></div>
    <h1 class="page-title">Ședințe și lecții</h1>
    <p class="page-subtitle">Planifică întâlnirile, notează tema abordată și deschide prezența direct din fiecare ședință.</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn--primary" data-modal-open="modal-create-lesson">Programează ședință</button>
  </div>
</header>

<section class="grid-2">
  <?php foreach ($lessons as $lesson): ?>
    <?php $tone = ui_tone_class($lesson['group_id']); ?>
    <article class="card entity-card card--interactive <?= e($tone) ?>">
      <span class="entity-card__accent" aria-hidden="true"></span>
      <div class="entity-card__head">
        <span class="badge badge--brand"><?= e($lesson['group_name']) ?></span>
        <span class="badge badge--neutral"><?= format_date_ro($lesson['lesson_date']) ?></span>
      </div>
      <div>
        <h2 class="entity-card__title"><?= e($lesson['title']) ?></h2>
        <p class="entity-card__description"><?= e($lesson['lesson_notes'] ?: 'Nu au fost adăugate încă notițe pentru această ședință.') ?></p>
      </div>
      <footer class="entity-card__footer">
        <small><?= e($lesson['start_time']) ?>–<?= e($lesson['end_time']) ?></small>
        <a href="/teacher/attendance?group_id=<?= e($lesson['group_id']) ?>&lesson_id=<?= e($lesson['id']) ?>" class="btn btn--outline btn--sm">Deschide prezența</a>
      </footer>
    </article>
  <?php endforeach; ?>

  <?php if (empty($lessons)): ?>
    <div class="card empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">＋</div>
        <div class="empty-state__title">Nu ai programat încă nicio ședință</div>
        <p class="empty-state__text">Adaugă prima întâlnire și completează tema lecției.</p>
        <div class="empty-state__actions"><button type="button" class="btn btn--primary" data-modal-open="modal-create-lesson">Programează ședința</button></div>
      </div>
    </div>
  <?php endif; ?>
</section>

<div id="modal-create-lesson" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-lesson-title">
    <h2 class="modal-title" id="create-lesson-title">Programează o ședință</h2>
    <p class="modal-description">Adaugă o întâlnire nouă în calendarul grupei.</p>
    <form action="/teacher/lessons" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label" for="lesson-group">Grupă</label>
        <select id="lesson-group" name="group_id" class="form-control" required>
          <?php foreach ($groups as $group): ?><option value="<?= e($group['id']) ?>"><?= e($group['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="lesson-title">Titlul lecției</label>
        <input type="text" id="lesson-title" name="title" class="form-control" placeholder="Exemplu: Relații metrice în triunghi" required>
      </div>
      <div class="form-grid form-grid--three">
        <div class="form-group"><label class="form-label" for="lesson-date">Data</label><input type="date" id="lesson-date" name="lesson_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
        <div class="form-group"><label class="form-label" for="lesson-start">Început</label><input type="time" id="lesson-start" name="start_time" class="form-control" value="16:00" required></div>
        <div class="form-group"><label class="form-label" for="lesson-end">Sfârșit</label><input type="time" id="lesson-end" name="end_time" class="form-control" value="18:00" required></div>
      </div>
      <div class="form-group">
        <label class="form-label" for="lesson-notes">Tematică și exerciții</label>
        <textarea id="lesson-notes" name="lesson_notes" class="form-control" placeholder="Ce explicați și ce exerciții lucrați"></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Salvează ședința</button>
      </div>
    </form>
  </section>
</div>
