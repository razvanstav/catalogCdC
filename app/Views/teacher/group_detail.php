<?php
$title = $group['name'];
$enrolledIds = array_flip(array_column($enrolledStudents, 'id'));
$unenrolledStudents = array_values(array_filter($allStudents, static fn(array $student): bool => !isset($enrolledIds[$student['id']])));
$tone = ui_tone_class($group['id']);
?>
<a href="/teacher/groups" class="btn btn--ghost btn--sm">← Înapoi la grupe</a>

<section class="hero-card <?= e($tone) ?>">
  <div class="page-kicker">
    <span class="badge badge--brand"><?= e(group_type_label($group['type'])) ?></span>
    <span class="badge badge--neutral"><?= count($enrolledStudents) ?> elevi</span>
  </div>
  <h1 class="hero-card__title"><?= e($group['name']) ?></h1>
  <p class="hero-card__description"><?= e($group['description'] ?: 'Spațiu de lucru pentru elevi, ședințe, teme și materiale.') ?></p>
  <div class="page-actions">
    <a href="/teacher/attendance?group_id=<?= e($group['id']) ?>" class="btn btn--sage">Deschide prezența</a>
    <button type="button" class="btn btn--outline" data-modal-open="modal-edit-group">Editează grupa</button>
    <button type="button" class="btn btn--primary" data-modal-open="modal-enroll-student">Înscrie elev</button>
  </div>
</section>

<div class="tab-list" data-tab-group="group-tabs" aria-label="Secțiunile grupei">
  <button type="button" class="tab-button is-active" data-tab-target="tab-students">Elevi (<?= count($enrolledStudents) ?>)</button>
  <button type="button" class="tab-button" data-tab-target="tab-schedule">Orar și ședințe</button>
  <button type="button" class="tab-button" data-tab-target="tab-assignments">Teme (<?= count($assignments) ?>)</button>
  <button type="button" class="tab-button" data-tab-target="tab-materials">Materiale (<?= count($materials) ?>)</button>
</div>

<section id="tab-students" class="tab-panel" data-tab-panel="group-tabs">
  <div class="grid-3">
    <?php foreach ($enrolledStudents as $student): ?>
      <article class="card entity-card">
        <div class="row row--start">
          <span class="avatar avatar--brand" aria-hidden="true"><?= e(initials($student['first_name'], $student['last_name'])) ?></span>
          <div class="content-row__main">
            <h2 class="entity-card__title"><?= e(trim($student['first_name'] . ' ' . ($student['father_initial'] ?? '') . ' ' . $student['last_name'])) ?></h2>
            <div class="content-row__meta"><?= e($student['email'] ?: $student['phone'] ?: 'Fără contact direct') ?></div>
          </div>
        </div>

        <?php if (!empty($student['private_notes'])): ?>
          <div class="private-note">
            <div class="private-note__header"><span class="private-note__title">Notiță privată</span><span class="badge badge--amber">Doar profesor</span></div>
            <p class="private-note__text"><?= e($student['private_notes']) ?></p>
          </div>
        <?php endif; ?>

        <footer class="entity-card__footer">
          <form action="/teacher/groups/<?= e($group['id']) ?>/unenroll" method="POST" class="inline-form">
            <?= csrf_field() ?>
            <input type="hidden" name="student_id" value="<?= e($student['id']) ?>">
            <button type="submit" class="btn btn--ghost btn--sm" title="Scoate elevul din această grupă">Scoate din grupă</button>
          </form>

          <a href="/teacher/students/<?= e($student['id']) ?>" class="btn btn--outline btn--sm">Dosar elev</a>
        </footer>
      </article>
    <?php endforeach; ?>

    <?php if (empty($enrolledStudents)): ?>
      <div class="card empty-state">
        <div>
          <div class="empty-state__icon" aria-hidden="true">＋</div>
          <div class="empty-state__title">Grupa nu are încă elevi</div>
          <p class="empty-state__text">Folosește butonul „Înscrie elev” pentru a completa grupa.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<section id="tab-schedule" class="tab-panel" data-tab-panel="group-tabs" hidden>
  <div class="grid-2">
    <article class="card">
      <div class="card-header">
        <div class="card-header__copy">
          <h2 class="card-title">Program recurent (săptămânal)</h2>
          <p class="card-description">Ședințele se repetă automat în fiecare săptămână conform acestui orar</p>
        </div>
        <button type="button" class="btn btn--primary btn--sm" data-modal-open="modal-add-schedule">+ Adaugă orar</button>
      </div>

      <div class="stack">
        <?php foreach ($schedules as $schedule): ?>
          <div class="content-row <?= e($tone) ?>">
            <span class="tone-dot" aria-hidden="true"></span>
            <div class="content-row__main">
              <div class="content-row__title"><?= e(day_name_ro((int)$schedule['day_of_week'])) ?> • <?= e($schedule['start_time']) ?>–<?= e($schedule['end_time']) ?></div>
              <div class="content-row__meta"><?= e($schedule['room_or_link'] ?: 'Cabinet didactic') ?> (Recurent)</div>
            </div>
            <?php if (!empty($schedule['id'])): ?>
              <form action="/teacher/groups/<?= e($group['id']) ?>/schedules/<?= e($schedule['id']) ?>/delete" method="POST" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--ghost btn--xs" title="Șterge acest interval">Șterge</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <?php if (empty($schedules)): ?>
          <div class="empty-state">
            <div>
              <p class="empty-state__text">Nu există încă un program recurent stabilit pentru această grupă.</p>
              <div class="empty-state__actions">
                <button type="button" class="btn btn--outline btn--sm" data-modal-open="modal-add-schedule">+ Setează ziua și ora recurentă</button>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </article>

    <article class="card">
      <div class="card-header">
        <div class="card-header__copy">
          <h2 class="card-title">Jurnalul ședințelor</h2>
          <p class="card-description">Ședințele programate și desfășurate</p>
        </div>
        <a href="/teacher/attendance?group_id=<?= e($group['id']) ?>" class="btn btn--sage btn--sm">Deschide catalogul</a>
      </div>
      <div class="stack">
        <?php foreach ($lessons as $lesson): ?>
          <div class="content-row">
            <span class="status-dot" aria-hidden="true">◷</span>
            <div class="content-row__main">
              <div class="content-row__title"><?= e($lesson['title']) ?></div>
              <div class="content-row__meta"><?= format_date_ro($lesson['lesson_date']) ?> • <?= e($lesson['start_time']) ?>–<?= e($lesson['end_time']) ?></div>
              <?php if (!empty($lesson['lesson_notes'])): ?><p class="card-description"><?= e($lesson['lesson_notes']) ?></p><?php endif; ?>
            </div>
            <a href="/teacher/attendance?group_id=<?= e($group['id']) ?>&lesson_id=<?= e($lesson['id']) ?>" class="btn btn--outline btn--xs">Prezență</a>
          </div>
        <?php endforeach; ?>
        <?php if (empty($lessons)): ?>
          <div class="empty-state"><div><p class="empty-state__text">Nu există încă ședințe generate pentru această grupă.</p></div></div>
        <?php endif; ?>
      </div>
    </article>
  </div>
</section>

<section id="tab-assignments" class="tab-panel" data-tab-panel="group-tabs" hidden>
  <div class="grid-2">
    <?php foreach ($assignments as $assignment): ?>
      <article class="card entity-card">
        <div class="entity-card__head">
          <span class="badge badge--amber">Termen: <?= format_date_ro($assignment['due_date']) ?></span>
        </div>
        <div><h2 class="entity-card__title"><?= e($assignment['title']) ?></h2><p class="entity-card__description"><?= e($assignment['description']) ?></p></div>
      </article>
    <?php endforeach; ?>
    <?php if (empty($assignments)): ?><div class="card empty-state"><div><p class="empty-state__text">Nu există teme publicate.</p></div></div><?php endif; ?>
  </div>
</section>

<section id="tab-materials" class="tab-panel" data-tab-panel="group-tabs" hidden>
  <div class="grid-3">
    <?php foreach ($materials as $material): ?>
      <article class="card entity-card">
        <div class="entity-card__head"><span class="badge badge--sage"><?= strtoupper(e($material['file_type'])) ?></span></div>
        <div><h2 class="entity-card__title"><?= e($material['title']) ?></h2></div>
        <footer class="entity-card__footer">
          <small>Resursă pentru grupă</small>
          <?php if (!empty($material['url'])): ?><a href="<?= e($material['url']) ?>" target="_blank" rel="noreferrer" class="btn btn--outline btn--sm">Deschide</a><?php endif; ?>
        </footer>
      </article>
    <?php endforeach; ?>
    <?php if (empty($materials)): ?><div class="card empty-state"><div><p class="empty-state__text">Nu există materiale publicate.</p></div></div><?php endif; ?>
  </div>
</section>

<div id="modal-edit-group" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="edit-group-title">
    <h2 class="modal-title" id="edit-group-title">Editează Grupa</h2>
    <p class="modal-description">Modifică denumirea, descrierea sau eticheta grupei.</p>
    <form action="/teacher/groups/<?= e($group['id']) ?>/edit" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label" for="edit_group_name">Denumire grupă</label>
        <input type="text" id="edit_group_name" name="name" value="<?= e($group['name']) ?>" class="form-control" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="edit_group_type">Tip curs</label>
        <select id="edit_group_type" name="type" class="form-control">
          <option value="tutoring_group" <?= $group['type'] === 'tutoring_group' ? 'selected' : '' ?>>Grupă de curs / meditație</option>
          <option value="workshop" <?= $group['type'] === 'workshop' ? 'selected' : '' ?>>Atelier aplicativ / workshop</option>
          <option value="individual_lesson" <?= $group['type'] === 'individual_lesson' ? 'selected' : '' ?>>Lecție individuală (1-la-1)</option>
          <option value="school_class" <?= $group['type'] === 'school_class' ? 'selected' : '' ?>>Clasă școlară</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="edit_group_description">Descriere & obiective</label>
        <textarea id="edit_group_description" name="description" class="form-control"><?= e($group['description'] ?? '') ?></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Salvează modificările</button>
      </div>
    </form>
  </section>
</div>

<div id="modal-enroll-student" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="enroll-student-title">
    <h2 class="modal-title" id="enroll-student-title">Înscrie elev în <?= e($group['name']) ?></h2>
    <p class="modal-description">Selectează un elev existent din catalog.</p>
    <form action="/teacher/groups/<?= e($group['id']) ?>/enroll" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <?php if (!empty($unenrolledStudents)): ?>
        <div class="form-group">
          <label class="form-label" for="student_id">Alege elevul din catalog</label>
          <select id="student_id" name="student_id" class="form-control" required>
            <option value="">Alege din listă (apar doar elevii neînscriși încă)</option>
            <?php foreach ($unenrolledStudents as $student): ?>
              <option value="<?= e($student['id']) ?>"><?= e($student['first_name'] . ' ' . $student['last_name']) ?><?= $student['email'] ? ' • ' . e($student['email']) : '' ?></option>
            <?php endforeach; ?>
          </select>
          <span class="form-hint">Elevii deja înscriși în această grupă nu mai apar în listă.</span>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
          <button type="submit" class="btn btn--primary">Înscrie elevul în grupă</button>
        </div>
      <?php else: ?>
        <div class="card card--flat">
          <p class="entity-card__description">Toți elevii din catalog sunt deja înscriși în această grupă.</p>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn--ghost" data-modal-close>Închide</button>
          <a href="/teacher/students" class="btn btn--primary">＋ Adaugă un elev nou în catalog</a>
        </div>
      <?php endif; ?>
    </form>
  </section>
</div>

<div id="modal-add-schedule" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="add-schedule-title">
    <h2 class="modal-title" id="add-schedule-title">Stabilește orarul recurent al grupei</h2>
    <p class="modal-description">Ziua și ora la care această grupă are curs pe tot parcursul anului școlar.</p>
    <form action="/teacher/groups/<?= e($group['id']) ?>/schedules" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label" for="schedule_day">Ziua din săptămână</label>
        <select id="schedule_day" name="day_of_week" class="form-control" required>
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
          <label class="form-label" for="schedule_start">Ora de început</label>
          <input type="time" id="schedule_start" name="start_time" value="09:00" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="schedule_duration">Durată ședință</label>
          <select id="schedule_duration" name="duration_minutes" class="form-control">
            <option value="60">1 oră (60 min)</option>
            <option value="90" selected>1 oră și 30 min (90 min)</option>
            <option value="120">2 ore (120 min)</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="schedule_room">Cabinet didactic / Sală / Link</label>
        <input type="text" id="schedule_room" name="room_or_link" class="form-control" value="Cabinet Informatică" placeholder="Laborator Informatică sau Link Discord/Zoom">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Salvează orarul recurent pe tot anul</button>
      </div>
    </form>
  </section>
</div>
