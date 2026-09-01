<?php
$title = 'Acasă — ' . $child['first_name'];
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
      <span class="badge badge--sage">Rezumat de familie</span>
      <span class="badge badge--neutral">Pentru <?= e($child['first_name']) ?></span>
    </div>

    <h1 class="hero-card__title">Bună. Iată cum arată săptămâna lui <?= e($child['first_name']) ?>.</h1>
    <p class="hero-card__description">Prezență, teme, rezultate și feedback, explicate clar, fără aglomerație și fără comparații cu alți elevi.</p>

    <div class="hero-search">
      <span class="hero-search__copy">Prezență curentă: <?= (int)$digest['attendanceRate'] ?>%</span>
      <a href="/parent/conversations" class="btn btn--sage btn--sm">Scrie profesorului</a>
    </div>

    <nav class="hero-shortcuts" aria-label="Acțiuni rapide pentru familie">
      <a href="/parent/results" class="shortcut">
        <span class="shortcut__icon" aria-hidden="true">🎓</span>
        <span class="shortcut__label">Rezultate</span>
      </a>
      <a href="/parent/timetable" class="shortcut shortcut--sage">
        <span class="shortcut__icon" aria-hidden="true">◷</span>
        <span class="shortcut__label">Orar</span>
      </a>
      <a href="/parent/assignments" class="shortcut shortcut--amber">
        <span class="shortcut__icon" aria-hidden="true">✓</span>
        <span class="shortcut__label">Teme</span>
      </a>
      <a href="/parent/conversations" class="shortcut shortcut--lilac">
        <span class="shortcut__icon" aria-hidden="true">💬</span>
        <span class="shortcut__label">Conversații</span>
      </a>
    </nav>
  </div>

  <aside class="dashboard-side">
    <?php if (!empty($digest['recentFeedbacks'])): ?>
      <article class="feedback-card">
        <div class="feedback-card__header">
          <span class="badge badge--sage">Apreciere recentă</span>
          <small><?= format_date_ro($digest['recentFeedbacks'][0]['created_at']) ?></small>
        </div>
        <p class="feedback-card__text"><?= e($digest['recentFeedbacks'][0]['content']) ?></p>
      </article>
    <?php else: ?>
      <div class="promo-card">
        <h2 class="promo-card__title">Conversații simple, fără mesaje pierdute.</h2>
        <p class="promo-card__text">Folosește canalul direct cu profesorul pentru întrebări despre teme, program sau progres.</p>
        <a href="/parent/conversations" class="btn btn--outline promo-card__action">Deschide chatul</a>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <div class="card-header__copy">
          <h2 class="card-title">Pe scurt</h2>
          <p class="card-description">Ce merită urmărit acum</p>
        </div>
      </div>
      <div class="stack stack--sm">
        <div class="content-row">
          <div class="content-row__main">
            <div class="content-row__title"><?= count($groups) ?> grupe active</div>
            <div class="content-row__meta">Activitățile lui <?= e($child['first_name']) ?></div>
          </div>
          <span class="badge badge--brand">Active</span>
        </div>
        <div class="content-row">
          <div class="content-row__main">
            <div class="content-row__title"><?= count($digest['upcomingAssignments']) ?> teme apropiate</div>
            <div class="content-row__meta">Termenele următoare</div>
          </div>
          <span class="badge badge--amber">De urmărit</span>
        </div>
      </div>
    </div>
  </aside>
</section>

<section class="metric-grid" aria-label="Rezumatul copilului">
  <article class="metric-card metric-card--sage">
    <div class="metric-value"><?= (int)$digest['attendanceRate'] ?>%</div>
    <div class="metric-label">Rată de prezență</div>
    <div class="metric-note">din ședințele înregistrate</div>
  </article>
  <article class="metric-card">
    <div class="metric-value"><?= count($groups) ?></div>
    <div class="metric-label">Grupe și activități</div>
    <div class="metric-note">program activ</div>
  </article>
  <article class="metric-card metric-card--amber">
    <div class="metric-value"><?= count($digest['upcomingAssignments']) ?></div>
    <div class="metric-label">Teme în lucru</div>
    <div class="metric-note">cu termen apropiat</div>
  </article>
  <article class="metric-card">
    <div class="metric-value"><?= count($digest['recentResults']) ?></div>
    <div class="metric-label">Rezultate recente</div>
    <div class="metric-note">publicate de profesor</div>
  </article>
</section>

<section class="grid-3">
  <article class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Ultimele rezultate</h2>
        <p class="card-description">Scoruri și explicații formative</p>
      </div>
      <a href="/parent/results" class="btn btn--ghost btn--sm">Vezi toate</a>
    </div>

    <div class="stack">
      <?php foreach (array_slice($digest['recentResults'], 0, 4) as $result): ?>
        <div class="content-row">
          <div class="content-row__main">
            <div class="content-row__title"><?= e($result['assessment_title']) ?></div>
            <div class="content-row__meta"><?= e($result['group_name'] ?? '') ?></div>
          </div>
          <span class="badge badge--brand"><?= number_format((float)$result['score'], 2) ?></span>
        </div>
      <?php endforeach; ?>

      <?php if (empty($digest['recentResults'])): ?>
        <div class="empty-state">
          <div>
            <div class="empty-state__icon" aria-hidden="true">🎓</div>
            <div class="empty-state__title">Niciun rezultat recent</div>
            <p class="empty-state__text">Rezultatele publicate de profesor vor apărea aici.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </article>

  <article class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Grupe și activități</h2>
        <p class="card-description">Unde participă <?= e($child['first_name']) ?></p>
      </div>
      <a href="/parent/timetable" class="btn btn--ghost btn--sm">Orar</a>
    </div>

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
        <h2 class="card-title">Teme apropiate</h2>
        <p class="card-description">O singură listă, fără notificări inutile</p>
      </div>
      <a href="/parent/assignments" class="btn btn--ghost btn--sm">Vezi toate</a>
    </div>

    <div class="stack">
      <?php foreach (array_slice($digest['upcomingAssignments'], 0, 4) as $assignment): ?>
        <a href="/parent/assignments#asg-<?= e($assignment['id']) ?>" class="content-row">
          <span class="status-dot status-dot--amber" aria-hidden="true">✓</span>
          <div class="content-row__main">
            <div class="content-row__title"><?= e($assignment['title']) ?></div>
            <div class="content-row__meta">Termen: <?= format_date_ro($assignment['due_date']) ?> • Deschide detalii</div>
          </div>
          <span class="badge badge--neutral">Deschide</span>
        </a>
      <?php endforeach; ?>

      <?php if (empty($digest['upcomingAssignments'])): ?>
        <div class="empty-state">
          <div>
            <div class="empty-state__icon" aria-hidden="true">✓</div>
            <div class="empty-state__title">Totul este la zi</div>
            <p class="empty-state__text">Nu există teme apropiate în acest moment.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </article>
</section>
