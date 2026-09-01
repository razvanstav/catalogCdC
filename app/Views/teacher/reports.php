<?php
$title = 'Rapoarte & Istoric Anual';
$groups = $groups ?? [];
$students = $students ?? [];
$activeTab = $activeTab ?? 'annual';
$availableYears = $availableYears ?? [date('Y'), (string)(date('Y') - 1)];
$selectedYear = $selectedYear ?? date('Y');
$annualOverview = $annualOverview ?? [
    'year' => $selectedYear,
    'student_count' => count($students),
    'group_count' => count($groups),
    'lesson_count' => 0,
    'attendance_rate' => 100,
    'paid_count' => 0,
    'paid_rate' => 100,
    'avg_score' => 5.0,
    'groups' => $groups,
];
$selectedAsmYear = $selectedAsmYear ?? 'all';
$selectedAsmGroup = $selectedAsmGroup ?? '';
$assessmentHistory = $assessmentHistory ?? [];
$startDate = $startDate ?? date('Y-01-01');
$endDate = $endDate ?? date('Y-12-31');
$attGroup = $attGroup ?? '';
$attendanceIntervalReport = $attendanceIntervalReport ?? [
    'start_date' => $startDate,
    'end_date' => $endDate,
    'total_lessons' => 0,
    'total_records' => 0,
    'present_count' => 0,
    'excused_count' => 0,
    'absent_count' => 0,
    'paid_count' => 0,
    'unpaid_count' => 0,
    'attendance_rate' => 100,
    'student_stats' => [],
];
?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Statistici & Istoric</span></div>
    <h1 class="page-title">Rapoarte și Istoric Anual</h1>
    <p class="page-subtitle">Analiză de la an la an, istoric de evaluări, grupe, prezență și plăți pe orice interval.</p>
  </div>
</header>

<div class="tabs" role="tablist" aria-label="Rapoarte și Istoric" data-tab-group="reports-tabs">
  <button type="button" class="tab-btn <?= $activeTab === 'annual' ? 'is-active' : '' ?>" role="tab" data-tab-target="panel-annual" aria-selected="<?= $activeTab === 'annual' ? 'true' : 'false' ?>">📊 Istoric Anual & Grupe</button>
  <button type="button" class="tab-btn <?= $activeTab === 'assessments' ? 'is-active' : '' ?>" role="tab" data-tab-target="panel-assessments" aria-selected="<?= $activeTab === 'assessments' ? 'true' : 'false' ?>">📋 Istoric Evaluări</button>
  <button type="button" class="tab-btn <?= $activeTab === 'attendance' ? 'is-active' : '' ?>" role="tab" data-tab-target="panel-attendance" aria-selected="<?= $activeTab === 'attendance' ? 'true' : 'false' ?>">📅 Prezență & Plăți pe Interval</button>
  <button type="button" class="tab-btn <?= $activeTab === 'student' ? 'is-active' : '' ?>" role="tab" data-tab-target="panel-student" aria-selected="<?= $activeTab === 'student' ? 'true' : 'false' ?>">👤 Fișă Individuală Elev</button>
</div>

<!-- ======================================================== -->
<!-- TAB 1: ISTORIC ANUAL & GRUPE                             -->
<!-- ======================================================== -->
<section id="panel-annual" class="tab-panel" data-tab-panel="reports-tabs" <?= $activeTab !== 'annual' ? 'hidden' : '' ?>>
  <div class="report-filter-bar">
    <form action="/teacher/reports" method="GET" class="row row--start">
      <input type="hidden" name="tab" value="annual">
      <div class="form-group">
        <label class="form-label" for="filter-year">Selectează Anul de analiză:</label>
        <select id="filter-year" name="year" class="form-control form-control--sm" data-submit-on-change>
          <option value="all" <?= $selectedYear === 'all' ? 'selected' : '' ?>>Toți anii (Cumulat)</option>
          <?php foreach ($availableYears as $yr): ?>
            <option value="<?= e($yr) ?>" <?= $selectedYear === $yr ? 'selected' : '' ?>>Anul <?= e($yr) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>

  <div class="metric-grid">
    <article class="metric-card metric-card--brand">
      <div class="metric-value"><?= e((string)$annualOverview['student_count']) ?></div>
      <div class="metric-label">Copii înscriși</div>
      <div class="metric-note"><?= $selectedYear === 'all' ? 'total istoric' : 'în anul ' . e($selectedYear) ?></div>
    </article>
    <article class="metric-card">
      <div class="metric-value"><?= e((string)$annualOverview['group_count']) ?></div>
      <div class="metric-label">Grupe active</div>
      <div class="metric-note">organizate</div>
    </article>
    <article class="metric-card">
      <div class="metric-value"><?= e((string)$annualOverview['lesson_count']) ?></div>
      <div class="metric-label">Ședințe ținute</div>
      <div class="metric-note">efectuate</div>
    </article>
    <article class="metric-card metric-card--sage">
      <div class="metric-value"><?= e((string)$annualOverview['paid_count']) ?></div>
      <div class="metric-label">Ședințe plătite</div>
      <div class="metric-note"><?= e((string)$annualOverview['paid_rate']) ?>% rată încasare</div>
    </article>
    <article class="metric-card metric-card--amber">
      <div class="metric-value"><?= e((string)$annualOverview['avg_score']) ?></div>
      <div class="metric-label">Media notelor</div>
      <div class="metric-note">pe scara 1–5</div>
    </article>
  </div>

  <section class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Situația grupelor în <?= $selectedYear === 'all' ? 'toți anii' : 'anul ' . e($selectedYear) ?></h2>
        <p class="card-description">Numărul de elevi, ședințe și evaluări pe fiecare grupă</p>
      </div>
    </div>

    <div class="report-table-wrapper">
      <table class="report-table">
        <thead>
          <tr>
            <th>Grupa</th>
            <th>Tip grupă</th>
            <th>Număr elevi</th>
            <th>Ședințe ținute</th>
            <th>Evaluări date</th>
            <th>Acțiuni</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($annualOverview['groups'] as $grp): ?>
            <tr>
              <td>
                <strong><?= e($grp['name']) ?></strong>
              </td>
              <td>
                <span class="badge badge--neutral"><?= e(group_type_label($grp['type'])) ?></span>
              </td>
              <td>
                <strong><?= (int)$grp['student_count'] ?> elevi</strong>
              </td>
              <td>
                <?= (int)$grp['lesson_count'] ?> ședințe
              </td>
              <td>
                <?= (int)$grp['assessment_count'] ?> evaluări
              </td>
              <td>
                <a href="/teacher/groups/<?= e($grp['id']) ?>" class="btn btn--outline btn--xs">Dosar grupă</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>

<!-- ======================================================== -->
<!-- TAB 2: ISTORIC EVALUĂRI & NOTE                           -->
<!-- ======================================================== -->
<section id="panel-assessments" class="tab-panel" data-tab-panel="reports-tabs" <?= $activeTab !== 'assessments' ? 'hidden' : '' ?>>
  <div class="report-filter-bar">
    <form action="/teacher/reports" method="GET" class="form-grid">
      <input type="hidden" name="tab" value="assessments">
      <div class="form-group">
        <label class="form-label" for="asm-year">Anul:</label>
        <select id="asm-year" name="asm_year" class="form-control form-control--sm" data-submit-on-change>
          <option value="all" <?= $selectedAsmYear === 'all' ? 'selected' : '' ?>>Toți anii</option>
          <?php foreach ($availableYears as $yr): ?>
            <option value="<?= e($yr) ?>" <?= $selectedAsmYear === $yr ? 'selected' : '' ?>>Anul <?= e($yr) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="asm-group">Grupa:</label>
        <select id="asm-group" name="asm_group_id" class="form-control form-control--sm" data-submit-on-change>
          <option value="">Toate grupele</option>
          <?php foreach ($groups as $g): ?>
            <option value="<?= e($g['id']) ?>" <?= $selectedAsmGroup === $g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>

  <div class="stack stack--lg">
    <?php foreach ($assessmentHistory as $asm): ?>
      <article class="card">
        <div class="card-header">
          <div class="card-header__copy">
            <div class="page-kicker">
              <span class="badge badge--brand"><?= e($asm['group_name']) ?></span>
              <span class="badge badge--neutral"><?= format_date_ro($asm['assessment_date']) ?></span>
            </div>
            <h2 class="card-title"><?= e($asm['title']) ?></h2>
            <p class="card-description"><?= count($asm['results']) ?> elevi notați • Media notelor: <strong><?= $asm['avg_score'] ? number_format((float)$asm['avg_score'], 2) : '—' ?></strong> (pe scara 1–5)</p>
          </div>
          <a href="/teacher/assessments?group_id=<?= e($asm['group_id']) ?>&assessment_id=<?= e($asm['id']) ?>" class="btn btn--outline btn--sm">Editează notele</a>
        </div>

        <div class="report-table-wrapper">
          <table class="report-table">
            <thead>
              <tr>
                <th>Elev</th>
                <th>Notă (1–5)</th>
                <th>Feedback Publicat</th>
                <th>Notiță Internă a Profesoarei</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($asm['results'] as $res): ?>
                <tr>
                  <td><strong><?= e(trim($res['first_name'] . ' ' . ($res['father_initial'] ?? '') . ' ' . $res['last_name'])) ?></strong></td>
                  <td><span class="badge badge--brand">Nota <?= number_format((float)$res['score'], 1) ?></span></td>
                  <td><?= e($res['published_feedback'] ?: '—') ?></td>
                  <td><em><?= e($res['private_teacher_notes'] ?: '—') ?></em></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($asm['results'])): ?>
                <tr><td colspan="4"><small class="content-row__meta">Nu există încă note înregistrate pentru această evaluare.</small></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </article>
    <?php endforeach; ?>

    <?php if (empty($assessmentHistory)): ?>
      <div class="card empty-state">
        <div>
          <div class="empty-state__icon" aria-hidden="true">✓</div>
          <div class="empty-state__title">Nicio evaluare găsită</div>
          <p class="empty-state__text">Alege un alt an sau o altă grupă din filtrele de mai sus.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ======================================================== -->
<!-- TAB 3: PREZENȚĂ & PLĂȚI PE INTERVAL                      -->
<!-- ======================================================== -->
<section id="panel-attendance" class="tab-panel" data-tab-panel="reports-tabs" <?= $activeTab !== 'attendance' ? 'hidden' : '' ?>>
  <div class="report-filter-bar">
    <form action="/teacher/reports" method="GET" class="form-grid">
      <input type="hidden" name="tab" value="attendance">
      <div class="form-group">
        <label class="form-label" for="att-start">De la data:</label>
        <input type="date" id="att-start" name="start_date" value="<?= e($startDate) ?>" class="form-control form-control--sm">
      </div>
      <div class="form-group">
        <label class="form-label" for="att-end">Până la data:</label>
        <input type="date" id="att-end" name="end_date" value="<?= e($endDate) ?>" class="form-control form-control--sm">
      </div>
      <div class="form-group">
        <label class="form-label" for="att-grp">Grupa:</label>
        <select id="att-grp" name="att_group_id" class="form-control form-control--sm">
          <option value="">Toate grupele</option>
          <?php foreach ($groups as $g): ?>
            <option value="<?= e($g['id']) ?>" <?= $attGroup === $g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group form-group--action">
        <button type="submit" class="btn btn--primary btn--sm">Filtrează intervalul</button>
      </div>
    </form>
  </div>

  <div class="metric-grid">
    <article class="metric-card">
      <div class="metric-value"><?= e((string)$attendanceIntervalReport['total_lessons']) ?></div>
      <div class="metric-label">Ședințe în interval</div>
      <div class="metric-note"><?= format_date_ro($startDate) ?> – <?= format_date_ro($endDate) ?></div>
    </article>
    <article class="metric-card metric-card--sage">
      <div class="metric-value"><?= e((string)$attendanceIntervalReport['attendance_rate']) ?>%</div>
      <div class="metric-label">Rată prezență</div>
      <div class="metric-note"><?= e((string)$attendanceIntervalReport['present_count']) ?> prezențe totale</div>
    </article>
    <article class="metric-card metric-card--brand">
      <div class="metric-value"><?= e((string)$attendanceIntervalReport['paid_count']) ?></div>
      <div class="metric-label">Ședințe plătite</div>
      <div class="metric-note">încasate în interval</div>
    </article>
    <article class="metric-card metric-card--amber">
      <div class="metric-value"><?= e((string)$attendanceIntervalReport['unpaid_count']) ?></div>
      <div class="metric-label">Ședințe neplătite</div>
      <div class="metric-note">în intervalul selectat</div>
    </article>
  </div>

  <section class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Situație prezență & plăți pe fiecare elev</h2>
        <p class="card-description">Interval: <?= format_date_ro($startDate) ?> – <?= format_date_ro($endDate) ?></p>
      </div>
    </div>

    <div class="report-table-wrapper">
      <table class="report-table">
        <thead>
          <tr>
            <th>Elev</th>
            <th>Grupa</th>
            <th>Ședințe totale</th>
            <th>Prezențe</th>
            <th>Învoiri</th>
            <th>Absențe</th>
            <th>Ședințe Plătite</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($attendanceIntervalReport['student_stats'] as $st): ?>
            <tr>
              <td><strong><?= e(trim($st['first_name'] . ' ' . ($st['father_initial'] ?? '') . ' ' . $st['last_name'])) ?></strong></td>
              <td><span class="badge badge--neutral"><?= e($st['group_name']) ?></span></td>
              <td><?= (int)$st['total_sessions'] ?> ore</td>
              <td><span class="badge badge--sage"><?= (int)$st['attended_count'] ?> prezente</span></td>
              <td><?= (int)$st['excused_count'] ?> învoiri</td>
              <td><?= (int)$st['absent_count'] ?> absențe</td>
              <td>
                <span class="badge <?= (int)$st['unpaid_count'] === 0 ? 'badge--sage' : 'badge--amber' ?>">
                  💳 <?= (int)$st['paid_count'] ?> plătite <?= (int)$st['unpaid_count'] > 0 ? ' (' . (int)$st['unpaid_count'] . ' neplătite)' : '' ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($attendanceIntervalReport['student_stats'])): ?>
            <tr><td colspan="7"><small class="content-row__meta">Nu există înregistrări în acest interval.</small></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>

<!-- ======================================================== -->
<!-- TAB 4: FIȘĂ INDIVIDUALĂ ELEV (RAPORT PENTRU PĂRINȚI)     -->
<!-- ======================================================== -->
<section id="panel-student" class="tab-panel" data-tab-panel="reports-tabs" <?= $activeTab !== 'student' ? 'hidden' : '' ?>>
  <div class="split-layout report-layout">
    <aside class="card report-nav">
      <div class="report-nav__title">Selectează elevul</div>
      <nav class="report-student-list">
        <?php foreach ($students as $student): ?>
          <?php $isSelected = $student['id'] === $selectedStudentId; ?>
          <a href="/teacher/reports?tab=student&student_id=<?= e($student['id']) ?>" class="report-nav__link <?= $isSelected ? 'is-active' : '' ?>">
            <span class="person-line">
              <span class="avatar avatar--sm <?= e(ui_tone_class($student['id'])) ?>" aria-hidden="true"><?= e(initials($student['first_name'], $student['last_name'])) ?></span>
              <span><?= e($student['first_name'] . ' ' . $student['last_name']) ?></span>
            </span>
            <span aria-hidden="true">›</span>
          </a>
        <?php endforeach; ?>
      </nav>
    </aside>

    <section>
      <?php if ($digest): ?>
        <article class="card report-sheet">
          <header class="report-sheet__header">
            <div>
              <div class="page-kicker"><span class="badge badge--sage">Previzualizare</span></div>
              <h2 class="section-title">Sumar pentru <?= e($digest['student']['first_name'] . ' ' . $digest['student']['last_name']) ?></h2>
              <p class="section-subtitle">Informații relevante pentru o conversație bună în familie.</p>
            </div>
            <button type="button" class="btn btn--sage" data-feedback-message="Sumarul a fost marcat ca transmis către familie.">Marchează ca transmis</button>
          </header>

          <div class="metric-grid metric-grid--three">
            <div class="metric-card metric-card--sage"><div class="metric-value"><?= e((string)$digest['attendanceRate']) ?>%</div><div class="metric-label">Prezență</div><div class="metric-note">în intervalul curent</div></div>
            <div class="metric-card"><div class="metric-value"><?= count($digest['recentResults']) ?></div><div class="metric-label">Evaluări recente</div><div class="metric-note">rezultate publicate</div></div>
            <div class="metric-card metric-card--brand"><div class="metric-value"><?= e((string)$digest['completedGoalsCount']) ?></div><div class="metric-label">Obiective atinse</div><div class="metric-note">pași finalizați</div></div>
          </div>

          <section class="report-section">
            <div class="section-heading"><div class="section-heading__copy"><h3 class="section-title">Aprecieri recente</h3><p class="section-subtitle">Mesaje formative publicate</p></div></div>
            <div class="grid-2">
              <?php foreach ($digest['recentFeedbacks'] as $feedback): ?><article class="feedback-card"><p class="feedback-card__text">„<?= e($feedback['content']) ?>”</p></article><?php endforeach; ?>
              <?php if (empty($digest['recentFeedbacks'])): ?><div class="card card--flat"><p class="card-description">Nu există aprecieri recente.</p></div><?php endif; ?>
            </div>
          </section>

          <section class="report-section">
            <div class="section-heading"><div class="section-heading__copy"><h3 class="section-title">Rezultate recente</h3><p class="section-subtitle">Evaluări cu context și feedback</p></div></div>
            <div class="stack">
              <?php foreach ($digest['recentResults'] as $result): ?>
                <div class="content-row">
                  <div class="content-row__main"><div class="content-row__title"><?= e($result['assessment_title']) ?></div><div class="content-row__meta"><?= e($result['published_feedback'] ?: 'Fără feedback publicat') ?></div></div>
                  <div class="result-score"><div class="result-score__value"><?= number_format((float)$result['score'], 2) ?></div><div class="result-score__max">puncte</div></div>
                </div>
              <?php endforeach; ?>
            </div>
          </section>
        </article>
      <?php else: ?>
        <div class="card empty-state"><div><div class="empty-state__icon">◎</div><div class="empty-state__title">Selectează un elev</div><p class="empty-state__text">Raportul de progres va fi generat aici.</p></div></div>
      <?php endif; ?>
    </section>
  </div>
</section>
