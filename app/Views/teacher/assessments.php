<?php $title = 'Evaluări și rezultate'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Progres explicat</span></div>
    <h1 class="page-title">Evaluări și rezultate</h1>
    <p class="page-subtitle">Înregistrează punctajul, publică feedback util familiei și păstrează separat observațiile tale private.</p>
  </div>
  <div class="page-actions">
    <a href="/teacher/reports?tab=assessments" class="btn btn--outline">📊 Istoric Evaluări</a>
    <button type="button" class="btn btn--primary" data-modal-open="modal-create-assessment">Evaluare nouă</button>
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
    <h2 class="modal-title" id="create-assessment-title">Creează o evaluare / notare</h2>
    <p class="modal-description">Poate fi o evaluare după ședință, un test, o verificare de temă sau un proiect practic.</p>
    <form action="/teacher/assessments" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group"><label class="form-label" for="assessment-group">Grupa</label><select id="assessment-group" name="group_id" class="form-control" required><?php foreach ($groups as $group): ?><option value="<?= e($group['id']) ?>" <?= $group['id'] === $selectedGroupId ? 'selected' : '' ?>><?= e($group['name']) ?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label class="form-label" for="assessment-title">Titlul evaluării</label><input type="text" id="assessment-title" name="title" class="form-control" placeholder="Exemplu: Ședință practică — Algoritmi și structuri" required></div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label" for="assessment-type">Tip</label><select id="assessment-type" name="assessment_type" class="form-control"><option value="homework_check">Verificare temă / ședință</option><option value="project">Proiect practic</option><option value="test">Test scris</option><option value="oral">Evaluare orală</option></select></div>
        <div class="form-group"><label class="form-label" for="assessment-date">Data</label><input type="date" id="assessment-date" name="assessment_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
      </div>
      <div class="form-group"><label class="form-label" for="max-score">Notă maximă</label><input type="number" id="max-score" name="max_score" min="1" max="10" step="1" value="5" class="form-control"></div>
      <div class="modal-actions"><button type="button" class="btn btn--ghost" data-modal-close>Renunță</button><button type="submit" class="btn btn--primary">Creează evaluarea</button></div>
    </form>
  </section>
</div>
