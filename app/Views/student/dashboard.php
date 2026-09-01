<?php
$title = 'Acasă — ' . $student['first_name'];
$activeGoals = array_filter($goals, static fn(array $goal): bool => !(bool)$goal['is_completed']);
$isVacation = \App\Support\Settings::isVacationMode();
?>
<?php if ($isVacation): ?>
  <article class="vacation-card">
    <h2 class="vacation-card__title">🌴 Suntem în Vacanță!</h2>
    <p class="vacation-card__text"><?= e(\App\Support\Settings::getVacationMessage()) ?></p>
    <span class="badge badge--amber">Activitatea cursurilor este oprită pe perioada vacanței</span>
  </article>
<?php endif; ?>
<section class="dashboard-hero">
  <div class="hero-card">
    <div class="page-kicker">
      <span class="badge badge--brand">Spațiul tău</span>
      <span class="badge badge--neutral">Progres, nu presiune</span>
    </div>

    <h1 class="hero-card__title">Salut, <?= e($student['first_name']) ?>.</h1>
    <p class="hero-card__description">Ai tot ce îți trebuie pentru următoarea lecție: orar, teme, materiale, rezultate și obiective personale.</p>

    <div class="hero-search">
      <span class="hero-search__copy"><?= count($assignments) ?> teme active • <?= count($activeGoals) ?> obiective în lucru</span>
      <a href="/student/timetable" class="btn btn--sage btn--sm">Vezi orarul</a>
    </div>

    <nav class="hero-shortcuts" aria-label="Acțiuni rapide pentru elev">
      <a href="/student/timetable" class="shortcut shortcut--sage">
        <span class="shortcut__icon" aria-hidden="true">◷</span>
        <span class="shortcut__label">Orar</span>
      </a>
      <a href="/student/assignments" class="shortcut shortcut--amber">
        <span class="shortcut__icon" aria-hidden="true">✓</span>
        <span class="shortcut__label">Teme</span>
      </a>
      <a href="/student/results" class="shortcut">
        <span class="shortcut__icon" aria-hidden="true">🎓</span>
        <span class="shortcut__label">Rezultate</span>
      </a>
      <a href="/student/goals" class="shortcut shortcut--lilac">
        <span class="shortcut__icon" aria-hidden="true">◎</span>
        <span class="shortcut__label">Obiective</span>
      </a>
    </nav>
  </div>

  <aside class="dashboard-side">
    <?php if (!empty($feedbacks)): ?>
      <article class="feedback-card">
        <div class="feedback-card__header">
          <span class="badge badge--sage">Apreciere recentă</span>
          <small><?= format_date_ro($feedbacks[0]['created_at']) ?></small>
        </div>
        <p class="feedback-card__text"><?= e($feedbacks[0]['content']) ?></p>
      </article>
    <?php endif; ?>

    <div class="promo-card">
      <h2 class="promo-card__title">Lucrează în pași mici și clari.</h2>
      <p class="promo-card__text">Alege următoarea temă sau un obiectiv și concentrează-te doar pe acel lucru.</p>
      <a href="/student/goals" class="btn btn--outline promo-card__action">Deschide obiectivele</a>
    </div>
  </aside>
</section>

<section class="metric-grid" aria-label="Rezumat personal">
  <article class="metric-card">
    <div class="metric-value"><?= count($groups) ?></div>
    <div class="metric-label">Grupe active</div>
    <div class="metric-note">activitățile tale</div>
  </article>
  <article class="metric-card metric-card--amber">
    <div class="metric-value"><?= count($assignments) ?></div>
    <div class="metric-label">Teme active</div>
    <div class="metric-note">de pregătit</div>
  </article>
  <article class="metric-card metric-card--sage">
    <div class="metric-value"><?= count($feedbacks) ?></div>
    <div class="metric-label">Aprecieri</div>
    <div class="metric-note">primite de la profesor</div>
  </article>
  <article class="metric-card">
    <div class="metric-value"><?= count($activeGoals) ?></div>
    <div class="metric-label">Obiective deschise</div>
    <div class="metric-note">în progres</div>
  </article>
</section>

<section class="grid-3">
  <article class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Următoarea ședință</h2>
        <p class="card-description">Ce urmează în program</p>
      </div>
      <a href="/student/timetable" class="btn btn--ghost btn--sm">Orar</a>
    </div>

    <?php if ($nextLesson): ?>
      <div class="content-row">
        <span class="status-dot" aria-hidden="true">◷</span>
        <div class="content-row__main">
          <div class="content-row__title"><?= e($nextLesson['title']) ?></div>
          <div class="content-row__meta"><?= format_date_ro($nextLesson['lesson_date']) ?> • <?= e($nextLesson['start_time']) ?>–<?= e($nextLesson['end_time']) ?></div>
        </div>
        <span class="badge badge--brand">Curând</span>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div>
          <div class="empty-state__icon" aria-hidden="true">◷</div>
          <div class="empty-state__title">Nicio ședință apropiată</div>
          <p class="empty-state__text">Programul următor va apărea aici.</p>
        </div>
      </div>
    <?php endif; ?>

    <div class="stack">
      <?php foreach ($groups as $group): ?>
        <?php $tone = ui_tone_class($group['id']); ?>
        <div class="content-row <?= e($tone) ?>">
          <span class="tone-dot" aria-hidden="true"></span>
          <div class="content-row__main">
            <div class="content-row__title"><?= e($group['name']) ?></div>
            <div class="content-row__meta"><?= e(group_type_label($group['type'])) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Teme de predat</h2>
        <p class="card-description">În ordinea priorității</p>
      </div>
      <a href="/student/assignments" class="btn btn--ghost btn--sm">Toate</a>
    </div>

    <div class="stack">
      <?php foreach (array_slice($assignments, 0, 5) as $assignment): ?>
        <a href="/student/assignments#asg-<?= e($assignment['id']) ?>" class="content-row">
          <span class="status-dot status-dot--amber" aria-hidden="true">✓</span>
          <div class="content-row__main">
            <div class="content-row__title"><?= e($assignment['title']) ?></div>
            <div class="content-row__meta">Termen: <?= format_date_ro($assignment['due_date']) ?> • Deschide tema</div>
          </div>
          <span class="badge badge--neutral">Deschide</span>
        </a>
      <?php endforeach; ?>

      <?php if (empty($assignments)): ?>
        <div class="empty-state">
          <div>
            <div class="empty-state__icon" aria-hidden="true">✓</div>
            <div class="empty-state__title">Temele sunt la zi</div>
            <p class="empty-state__text">Poți continua cu materialele sau obiectivele tale.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </article>

  <article class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Obiectivele mele</h2>
        <p class="card-description">Progres urmărit fără comparații</p>
      </div>
      <a href="/student/goals" class="btn btn--ghost btn--sm">Gestionează</a>
    </div>

    <div class="stack">
      <?php foreach (array_slice($goals, 0, 5) as $goal): ?>
        <div class="check-item <?= $goal['is_completed'] ? 'is-complete' : '' ?>">
          <div class="check-item__main">
            <span class="check-control <?= $goal['is_completed'] ? 'is-complete' : '' ?>" aria-hidden="true"><?= $goal['is_completed'] ? '✓' : '○' ?></span>
            <div class="content-row__main">
              <div class="content-row__title"><?= e($goal['title']) ?></div>
              <?php if (!empty($goal['target_date'])): ?>
                <div class="content-row__meta">Țintă: <?= format_date_ro($goal['target_date']) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <span class="badge <?= $goal['is_completed'] ? 'badge--sage' : 'badge--neutral' ?>"><?= $goal['is_completed'] ? 'Atins' : 'În lucru' ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
</section>
