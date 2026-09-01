<?php $title = 'Elevi și dosare'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Catalog privat</span></div>
    <h1 class="page-title">Elevi și dosare pedagogice</h1>
    <p class="page-subtitle">Datele elevilor, grupele, progresul și notițele tale private, organizate clar.</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn--primary" data-modal-open="modal-create-student">Adaugă elev</button>
  </div>
</header>

<section class="grid-3">
  <?php foreach ($students as $student): ?>
    <article class="card entity-card card--interactive">
      <div class="row row--start">
        <span class="avatar avatar--brand" aria-hidden="true"><?= e(initials($student['first_name'], $student['last_name'])) ?></span>
        <div class="content-row__main">
          <h2 class="entity-card__title"><?= e(trim($student['first_name'] . ' ' . ($student['father_initial'] ?? '') . ' ' . $student['last_name'])) ?></h2>
          <div class="content-row__meta"><?= e($student['email'] ?: $student['phone'] ?: 'Fără contact direct') ?></div>
        </div>
      </div>

      <?php if (!empty($student['private_notes'])): ?>
        <div class="private-note">
          <div class="private-note__header">
            <span class="private-note__title">Notiță privată</span>
            <span class="badge badge--amber">Doar profesor</span>
          </div>
          <p class="private-note__text"><?= e($student['private_notes']) ?></p>
        </div>
      <?php else: ?>
        <p class="entity-card__description">Nu ai adăugat încă observații private pentru acest elev.</p>
      <?php endif; ?>

      <footer class="entity-card__footer">
        <a href="/teacher/students/<?= e($student['id']) ?>" class="btn btn--outline btn--sm">Deschide dosar</a>
      </footer>
    </article>
  <?php endforeach; ?>

  <?php if (empty($students)): ?>
    <div class="card empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">＋</div>
        <div class="empty-state__title">Adaugă primul elev</div>
        <p class="empty-state__text">După înregistrare îl poți înscrie în una sau mai multe grupe.</p>
        <div class="empty-state__actions"><button type="button" class="btn btn--primary" data-modal-open="modal-create-student">Adaugă elev</button></div>
      </div>
    </div>
  <?php endif; ?>
</section>

<div id="modal-create-student" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-student-title">
    <h2 class="modal-title" id="create-student-title">Înregistrează un elev</h2>
    <p class="modal-description">Datele de bază și o notiță privată opțională.</p>

    <form action="/teacher/students" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="first_name">Prenume</label>
          <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Andrei" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="last_name">Nume de familie</label>
          <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Ionescu" required>
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="father_initial">Inițială</label>
          <input type="text" id="father_initial" name="father_initial" maxlength="2" class="form-control" placeholder="M">
        </div>
        <div class="form-group">
          <label class="form-label" for="email">E-mail</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="andrei@elev.ro">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="phone">Telefon</label>
        <input type="tel" id="phone" name="phone" class="form-control" placeholder="07xx xxx xxx">
      </div>
      <div class="form-group">
        <label class="form-label" for="private_notes">Notiță privată inițială</label>
        <textarea id="private_notes" name="private_notes" class="form-control" placeholder="Nivel de pornire, ritm de lucru, lucruri de reluat"></textarea>
        <span class="form-hint">Această notiță rămâne vizibilă exclusiv profesorului.</span>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Salvează elevul</button>
      </div>
    </form>
  </section>
</div>
