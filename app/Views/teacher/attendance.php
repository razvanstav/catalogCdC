<?php
$title = 'Catalog de prezență';
$allStudents = $allStudents ?? [];
$currentStudentIds = array_flip(array_column($students, 'id'));
$availableGuestStudents = array_values(array_filter($allStudents, static fn(array $st): bool => !isset($currentStudentIds[$st['id']])));
$week = $week ?? (new \App\Repositories\GroupRepository())->getWeekCalendar($selectedDate ?? 'today');
?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--sage">Tab 4 • Ședințe</span></div>
    <h1 class="page-title">Ședințe & Prezență</h1>
    <p class="page-subtitle">Ședințele grupei din săptămâna selectată. Bifează prezența (implicit Prezent) și plata ședinței.</p>
  </div>
  <div class="page-actions">
    <?php if ($selectedLessonId): ?>
      <a href="/teacher/assessments?lesson_id=<?= e($selectedLessonId) ?>" class="btn btn--outline">⭐ Evaluează această ședință (Tab 5)</a>
      <button type="button" class="btn btn--primary" data-modal-open="modal-add-guest">+ Elev la recuperare</button>
    <?php endif; ?>
    <button type="button" class="btn btn--outline" data-mark-all-present>Toți prezenți</button>
  </div>
</header>

<section class="card card--flat">
  <div class="row row--between cluster">
    <div class="cluster">
      <a href="/teacher/attendance?group_id=<?= e($selectedGroupId) ?>&date=<?= e($week['prev_week_date']) ?>" class="btn btn--ghost btn--sm" title="Săptămâna anterioară">← Săpt. anterioară</a>
      <a href="/teacher/attendance?group_id=<?= e($selectedGroupId) ?>&date=today" class="btn <?= $week['is_current_week'] ? 'btn--sage' : 'btn--outline' ?> btn--sm">Săptămâna curentă</a>
      <a href="/teacher/attendance?group_id=<?= e($selectedGroupId) ?>&date=<?= e($week['next_week_date']) ?>" class="btn btn--ghost btn--sm" title="Săptămâna următoare">Săpt. viitoare →</a>
      <span class="badge badge--neutral"><?= e($week['formatted_range']) ?></span>
    </div>

    <form action="/teacher/attendance" method="GET" class="cluster">
      <input type="hidden" name="group_id" value="<?= e($selectedGroupId) ?>">
      <label class="form-label sr-only" for="attendance-jump-date">Sari la o săptămână anume</label>
      <input type="date" id="attendance-jump-date" name="date" value="<?= e($week['sunday_date']) ?>" class="form-control" data-submit-on-change>
      <noscript><button type="submit" class="btn btn--outline btn--sm">Mergi</button></noscript>
    </form>
  </div>

  <div class="form-grid attendance-filters">
    <form action="/teacher/attendance" method="GET" class="form-group">
      <input type="hidden" name="date" value="<?= e($week['sunday_date']) ?>">
      <label class="form-label" for="group_id">Grupă</label>
      <select id="group_id" name="group_id" class="form-control" data-submit-on-change>
        <?php foreach ($groups as $group): ?>
          <option value="<?= e($group['id']) ?>" <?= $group['id'] === $selectedGroupId ? 'selected' : '' ?>><?= e($group['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <noscript><button type="submit" class="btn btn--outline btn--sm">Schimbă grupa</button></noscript>
    </form>

    <form action="/teacher/attendance" method="GET" class="form-group">
      <input type="hidden" name="date" value="<?= e($week['sunday_date']) ?>">
      <input type="hidden" name="group_id" value="<?= e($selectedGroupId) ?>">
      <label class="form-label" for="lesson_id">Ședință din această săptămână (<?= count($lessons) ?> disponibile)</label>
      <select id="lesson_id" name="lesson_id" class="form-control" data-submit-on-change>
        <?php foreach ($lessons as $lesson): ?>
          <option value="<?= e($lesson['id']) ?>" <?= $lesson['id'] === $selectedLessonId ? 'selected' : '' ?>>
            <?= format_date_ro($lesson['lesson_date']) ?> (<?= e($lesson['start_time']) ?>–<?= e($lesson['end_time']) ?>) • <?= e($lesson['title']) ?>
          </option>
        <?php endforeach; ?>
        <?php if (empty($lessons)): ?>
          <option value="">Nicio ședință în săptămâna <?= e($week['formatted_range']) ?></option>
        <?php endif; ?>
      </select>
      <noscript><button type="submit" class="btn btn--outline btn--sm">Selectează ședința</button></noscript>
    </form>
  </div>
</section>

<?php if ($selectedLessonId): ?>
  <form action="/teacher/attendance" method="POST" class="attendance-form">
    <?= csrf_field() ?>
    <input type="hidden" name="group_id" value="<?= e($selectedGroupId) ?>">
    <input type="hidden" name="lesson_id" value="<?= e($selectedLessonId) ?>">
    <input type="hidden" name="date" value="<?= e($week['sunday_date']) ?>">

    <section class="card card--flush">
      <div class="panel-toolbar">
        <div>
          <div class="panel-toolbar__title">Elevi prezenți la ședință</div>
          <div class="panel-toolbar__meta"><?= count($students) ?> elevi în lista acestei ore</div>
        </div>
        <button type="submit" class="btn btn--sage btn--sm">Salvează prezența</button>
      </div>

      <?php if (!empty($students)): ?>
        <div class="attendance-list">
          <?php foreach ($students as $student): ?>
            <?php
            $record = $recordMap[$student['id']] ?? null;
            $currentStatus = $record['status'] ?? 'present';
            $currentNote = $record['note'] ?? '';
            $isGuest = !empty($student['is_guest']);
            $isPaid = isset($record['is_paid']) ? ((int)$record['is_paid'] === 1) : (!isset($student['is_paid']) || (int)$student['is_paid'] === 1);
            ?>
            <article class="attendance-row" data-attendance-row>
              <div class="attendance-person">
                <span class="avatar avatar--brand" aria-hidden="true"><?= e(initials($student['first_name'], $student['last_name'])) ?></span>
                <div class="attendance-person__copy">
                  <div class="content-row__title">
                    <?= e(trim($student['first_name'] . ' ' . ($student['father_initial'] ?? '') . ' ' . $student['last_name'])) ?>
                    <?php if ($isGuest): ?>
                      <span class="badge badge--amber">Recuperare / Invitat la această oră</span>
                    <?php endif; ?>
                    <?php if ($isPaid): ?>
                      <span class="badge badge--paid">Plătit</span>
                    <?php else: ?>
                      <span class="badge badge--unpaid">Neplătit</span>
                    <?php endif; ?>
                  </div>
                  <label class="sr-only" for="note-<?= e($student['id']) ?>">Mențiune pentru <?= e($student['first_name']) ?></label>
                  <input id="note-<?= e($student['id']) ?>" type="text" name="notes[<?= e($student['id']) ?>]" value="<?= e($currentNote) ?>" class="attendance-note" placeholder="Mențiune (ex: recuperare temă, exercițiu suplimentar)">
                </div>
              </div>

              <div class="attendance-actions-cell">
                <fieldset class="attendance-control">
                  <legend class="sr-only">Status prezență pentru <?= e($student['first_name'] . ' ' . $student['last_name']) ?></legend>
                  <?php foreach (['present' => 'Prezent', 'late' => 'Întârziat', 'excused' => 'Învoit', 'absent' => 'Absent'] as $status => $label): ?>
                    <label class="attendance-option attendance-option--<?= e($status) ?> <?= $currentStatus === $status ? 'is-selected' : '' ?>" data-attendance-option>
                      <input class="sr-only" type="radio" name="status[<?= e($student['id']) ?>]" value="<?= e($status) ?>" <?= $currentStatus === $status ? 'checked' : '' ?>>
                      <span><?= e($label) ?></span>
                    </label>
                  <?php endforeach; ?>
                </fieldset>

                <div class="attendance-paid-bar">
                  <label class="paid-check-control" title="Bifează dacă această ședință a fost plătită de elev/părinte">
                    <input type="checkbox" name="is_paid[<?= e($student['id']) ?>]" value="1" <?= $isPaid ? 'checked' : '' ?>>
                    <span class="paid-check-text">💳 Ședință Plătită</span>
                  </label>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <div>
            <div class="empty-state__icon" aria-hidden="true">◎</div>
            <div class="empty-state__title">Nu există elevi înscriși în această grupă</div>
            <p class="empty-state__text">Înscrie elevi în grupă din Tabul 2 (Grupe) sau adaugă un elev la recuperare folosind butonul de sus.</p>
          </div>
        </div>
      <?php endif; ?>
    </section>
  </form>
<?php else: ?>
  <div class="card empty-state">
    <div>
      <div class="empty-state__icon" aria-hidden="true">⏰</div>
      <div class="empty-state__title">Nicio ședință în această săptămână pentru grupa selectată</div>
      <p class="empty-state__text">Grupa nu are nicio oră programată în intervalul <?= e($week['formatted_range']) ?>. Folosește butoanele de sus pentru a naviga între săptămâni sau verifică orarul recurent.</p>
      <div class="empty-state__actions">
        <a href="/teacher/attendance?group_id=<?= e($selectedGroupId) ?>&date=today" class="btn btn--sage">Mergi la Săptămâna curentă</a>
        <a href="/teacher/calendar?date=<?= e($week['sunday_date']) ?>" class="btn btn--outline">Deschide Orar (Tab 3)</a>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if ($selectedLessonId): ?>
  <div id="modal-add-guest" class="modal-backdrop" aria-hidden="true">
    <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="add-guest-title">
      <h2 class="modal-title" id="add-guest-title">Adaugă elev la recuperare (strict la această ședință)</h2>
      <p class="modal-description">Elevul va fi inclus în prezența acestei ore specifice, fără a fi transferat permanent în altă grupă.</p>

      <form action="/teacher/attendance/add-guest" method="POST" class="form-stack">
        <?= csrf_field() ?>
        <input type="hidden" name="group_id" value="<?= e($selectedGroupId) ?>">
        <input type="hidden" name="lesson_id" value="<?= e($selectedLessonId) ?>">
        <input type="hidden" name="date" value="<?= e($week['sunday_date']) ?>">

        <div class="form-group">
          <label class="form-label" for="guest_student_id">Alege elevul</label>
          <select id="guest_student_id" name="student_id" class="form-control" required>
            <option value="">Selectează din catalog...</option>
            <?php foreach ($availableGuestStudents as $st): ?>
              <option value="<?= e($st['id']) ?>"><?= e($st['first_name'] . ' ' . $st['last_name']) ?> <?= $st['email'] ? ' • ' . e($st['email']) : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="guest_note">Motiv / Notă orar</label>
          <input type="text" id="guest_note" name="note" class="form-control" value="Recuperare oră programare">
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
          <button type="submit" class="btn btn--primary">Adaugă elevul la această oră</button>
        </div>
      </form>
    </section>
  </div>
<?php endif; ?>

<script src="/assets/js/attendance.js?v=<?= e((string)@filemtime(dirname(__DIR__, 3) . '/public/assets/js/attendance.js')) ?>"></script>
