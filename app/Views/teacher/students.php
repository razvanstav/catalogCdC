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
    <h2 class="modal-title" id="create-student-title">Înregistrează un elev nou</h2>
    <p class="modal-description">Completează datele elevului, părintele și credențialele de acces setate direct de tine.</p>

    <form action="/teacher/students" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="last_name">Nume de familie</label>
          <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Popescu" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="first_name">Prenume elev</label>
          <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Alexandru" required>
        </div>
      </div>

      <div class="card card--flat">
        <div class="form-group">
          <label class="form-label" for="guardian_id">Părinte / Familie</label>
          <select id="guardian_id" name="guardian_id" class="form-control">
            <option value="">-- Alege un părinte existent sau completează mai jos --</option>
            <?php foreach (($guardians ?? []) as $g): ?>
              <option value="<?= e($g['id']) ?>"><?= e($g['first_name'] . ' ' . $g['last_name']) ?> (<?= e($g['phone'] ?: $g['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="guardian_name">Nume părinte nou (dacă nu e în listă)</label>
            <input type="text" id="guardian_name" name="guardian_name" class="form-control" placeholder="Ion Popescu">
          </div>
          <div class="form-group">
            <label class="form-label" for="guardian_phone">Telefon părinte</label>
            <input type="tel" id="guardian_phone" name="guardian_phone" class="form-control" placeholder="07xx xxx xxx">
          </div>
        </div>
      </div>

      <div class="card card--flat">
        <div class="form-group"><span class="badge badge--brand">Credențiale Login Elev</span></div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="username">Nume utilizator (login elev)</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="alex.popescu" autocomplete="off">
            <span class="form-hint">Dacă lași gol, se generează automat din nume (ex: alex.popescu).</span>
          </div>
          <div class="form-group">
            <label class="form-label" for="password">Parolă de login</label>
            <input type="text" id="password" name="password" class="form-control" value="elev123" placeholder="Parolă dorită" autocomplete="off" required>
            <span class="form-hint">Parola pe care o transmiți elevului pentru autentificare.</span>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="group_id">Înscrie direct în grupă (opțional)</label>
        <select id="group_id" name="group_id" class="form-control">
          <option value="">-- Fără grupă inițial (îl înscrii ulterior din tabul Grupe) --</option>
          <?php foreach (($groups ?? []) as $grp): ?>
            <option value="<?= e($grp['id']) ?>"><?= e($grp['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="private_notes">Notiță privată confidențială (doar profesoară)</label>
        <textarea id="private_notes" name="private_notes" class="form-control" placeholder="Observații despre elev, nivel inițial, ritm de învățare..."></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Înregistrează elevul și creează contul</button>
      </div>
    </form>
  </section>
</div>
