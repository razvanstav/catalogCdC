<?php $title = 'Evaluări și rezultate'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Evaluări & Note</span></div>
    <h1 class="page-title">Evaluări & Note (1–5)</h1>
    <p class="page-subtitle">Evaluează elevii în cadrul ședințelor avute. Alege ședința din ziua respectivă, notează de la 1 la 5 și trimite feedback rapid.</p>
  </div>
  <div class="page-actions">
    <a href="/teacher/reports?tab=assessments" class="btn btn--outline">📊 Istoric Evaluări</a>
    <button type="button" class="btn btn--primary" data-modal-open="modal-create-assessment">＋ Evaluare nouă pe ședință</button>
  </div>
</header>

<section class="card card--flat">
  <form action="/teacher/assessments" method="GET" class="form-grid assessment-filters">
    <div class="form-group">
      <label class="form-label" for="group_id">Grupa</label>
      <select id="group_id" name="group_id" class="form-control" data-submit-on-change>
        <?php foreach ($groups as $group): ?><option value="<?= e($group['id']) ?>" <?= $group['id'] === $selectedGroupId ? 'selected' : '' ?>><?= e($group['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" for="assessment_id">Evaluarea activă</label>
      <select id="assessment_id" name="assessment_id" class="form-control" data-submit-on-change>
        <?php foreach ($assessments as $assessment): ?><option value="<?= e($assessment['id']) ?>" <?= $assessment['id'] === $selectedAssessmentId ? 'selected' : '' ?>><?= e($assessment['title']) ?> • <?= format_date_ro($assessment['assessment_date']) ?></option><?php endforeach; ?>
        <?php if (empty($assessments)): ?><option value="">Nicio evaluare creată</option><?php endif; ?>
      </select>
    </div>
  </form>
</section>

<form action="/teacher/assessments/results" method="POST">
  <?= csrf_field() ?>
  <input type="hidden" name="group_id" value="<?= e($selectedGroupId) ?>">
  <input type="hidden" name="assessment_id" value="<?= e($selectedAssessmentId) ?>">

  <section class="card card--flush">
    <div class="panel-toolbar">
      <div><div class="panel-toolbar__title">Rezultatele elevilor</div><div class="panel-toolbar__meta"><?= count($students) ?> elevi în evaluarea selectată</div></div>
      <button type="submit" class="btn btn--primary btn--sm" <?= empty($students) ? 'disabled' : '' ?>>Salvează rezultatele</button>
    </div>

    <?php if (!empty($students)): ?>
      <div class="assessment-list">
        <?php foreach ($students as $student): ?>
          <?php
          $result = $resultMap[$student['id']] ?? null;
          $score = $result ? (float)$result['score'] : '';
          $publicFeedback = $result['published_feedback'] ?? '';
          $privateNotes = $result['private_teacher_notes'] ?? '';
          $currentScoreInt = (int)round((float)$score);
          ?>
          <article class="assessment-student">
            <div class="assessment-student__head">
              <div class="person-line">
                <span class="avatar avatar--brand" aria-hidden="true"><?= e(initials($student['first_name'], $student['last_name'])) ?></span>
                <div>
                  <div class="content-row__title"><?= e(trim($student['first_name'] . ' ' . ($student['father_initial'] ?? '') . ' ' . $student['last_name'])) ?></div>
                  <div class="content-row__meta">Notă (1–5) și feedback rapid după ședință</div>
                </div>
              </div>
              <div class="score-control">
                <label class="score-control__label" for="score-<?= e($student['id']) ?>">Notă (1–5)</label>
                <div class="rating-group" data-rating-group>
                  <input id="score-<?= e($student['id']) ?>" type="number" step="0.5" min="1" max="5" name="scores[<?= e($student['id']) ?>]" value="<?= e((string)$score) ?>" class="assessment-score-input" placeholder="—">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" class="rating-btn <?= ($currentScoreInt >= $i) ? 'is-active' : '' ?>" data-rating-value="<?= $i ?>" title="Nota <?= $i ?>">
                      <?= $i ?>
                    </button>
                  <?php endfor; ?>
                </div>
              </div>
            </div>

            <div class="assessment-fields">
              <div class="assessment-field assessment-field--public">
                <label class="assessment-field__label" for="public-feedback-<?= e($student['id']) ?>">Feedback vizibil părintelui și elevului</label>
                <input id="public-feedback-<?= e($student['id']) ?>" type="text" name="published_feedback[<?= e($student['id']) ?>]" value="<?= e($publicFeedback) ?>" class="form-control" placeholder="Alege un text rapid de mai jos sau scrie aici...">
                <div class="chip-group">
                  <button type="button" class="chip-btn" data-insert-feedback="Excelentă atenție și logică algoritmică." data-target="public-feedback-<?= e($student['id']) ?>">🌟 Atenție & Logică</button>
                  <button type="button" class="chip-btn" data-insert-feedback="A finalizat exercițiile independent." data-target="public-feedback-<?= e($student['id']) ?>">💻 Lucru independent</button>
                  <button type="button" class="chip-btn" data-insert-feedback="Ritm alert, a rezolvat cerințele bonus." data-target="public-feedback-<?= e($student['id']) ?>">⚡ Bonus rezolvat</button>
                  <button type="button" class="chip-btn" data-insert-feedback="A identificat și depanat erorile de cod cu succes." data-target="public-feedback-<?= e($student['id']) ?>">🔍 Depanare (Debugging)</button>
                  <button type="button" class="chip-btn" data-insert-feedback="Necesită recapitulare pe bucle și condiții." data-target="public-feedback-<?= e($student['id']) ?>">🔄 Recapitulare bucle</button>
                  <button type="button" class="chip-btn" data-insert-feedback="Temă realizată complet și explicată clar." data-target="public-feedback-<?= e($student['id']) ?>">📚 Temă completă</button>
                </div>
              </div>
              <div class="assessment-field assessment-field--private">
                <label class="assessment-field__label" for="private-note-<?= e($student['id']) ?>">Notiță privată a profesoarei</label>
                <input id="private-note-<?= e($student['id']) ?>" type="text" name="private_notes[<?= e($student['id']) ?>]" value="<?= e($privateNotes) ?>" class="form-control" placeholder="Exemplu: De lucrat mai mult la sintaxa funcțiilor.">
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state"><div><div class="empty-state__icon">◎</div><div class="empty-state__title">Nu există elevi de evaluat</div><p class="empty-state__text">Selectează o grupă cu elevi sau creează mai întâi o evaluare.</p></div></div>
    <?php endif; ?>
  </section>
</form>

<div id="modal-create-assessment" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-assessment-title">
    <h2 class="modal-title" id="create-assessment-title">Creează o evaluare în cadrul unei ședințe</h2>
    <p class="modal-description">Alege ședința din ziua respectivă pentru care dai note și feedback. Poți adăuga oricâte evaluări dorești în aceeași zi.</p>
    <form action="/teacher/assessments" method="POST" class="form-stack">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label" for="assessment-date">Data</label>
        <input type="date" id="assessment-date" name="assessment_date" class="form-control" value="<?= e($selectedDate ?? date('Y-m-d')) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="assessment-lesson">Alege ședința din ziua respectivă</label>
        <select id="assessment-lesson" name="lesson_id" class="form-control">
          <option value="">-- Alege ședința de evaluat --</option>
          <?php if (!empty($lessonsForDate)): ?>
            <optgroup label="Ședințe din data selectată">
              <?php foreach ($lessonsForDate as $l): ?>
                <option value="<?= e($l['id']) ?>" <?= ($selectedLessonId ?? '') === $l['id'] ? 'selected' : '' ?>>
                  <?= e($l['start_time'] . '–' . $l['end_time']) ?> • <?= e($l['group_name']) ?> — <?= e($l['title']) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endif; ?>
          <?php if (!empty($allRecentLessons)): ?>
            <optgroup label="Alte ședințe recente">
              <?php foreach ($allRecentLessons as $rl): ?>
                <option value="<?= e($rl['id']) ?>" <?= ($selectedLessonId ?? '') === $rl['id'] ? 'selected' : '' ?>>
                  <?= format_date_ro($rl['lesson_date']) ?> (<?= e($rl['start_time']) ?>) • <?= e($rl['group_name']) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endif; ?>
          <option value="">-- Fără ședință specifică (evaluare pe toată grupa) --</option>
        </select>
        <span class="form-hint">Dacă alegi o ședință, grupa se asociază automat.</span>
      </div>

      <div class="form-group">
        <label class="form-label" for="assessment-group">Grupa (dacă nu ai ales o ședință mai sus)</label>
        <select id="assessment-group" name="group_id" class="form-control">
          <?php foreach ($groups as $group): ?>
            <option value="<?= e($group['id']) ?>" <?= $group['id'] === $selectedGroupId ? 'selected' : '' ?>><?= e($group['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="assessment-title">Titlul evaluării</label>
        <input type="text" id="assessment-title" name="title" class="form-control" placeholder="Exemplu: Verificare Bucle & Condiționale sau Evaluare Ședință" required>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="assessment-type">Tip</label>
          <select id="assessment-type" name="assessment_type" class="form-control">
            <option value="homework_check">Verificare temă / activitate la clasă</option>
            <option value="test">Test scris / verificare cod</option>
            <option value="project">Proiect practic</option>
            <option value="oral">Evaluare orală</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="max-score">Scară notare</label>
          <input type="number" id="max-score" name="max_score" min="1" max="5" step="1" value="5" class="form-control">
          <span class="form-hint">Note de la 1 la 5</span>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Creează evaluarea</button>
      </div>
    </form>
  </section>
</div>
