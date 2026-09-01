<?php
$title = 'Acasă';
$teacher = \App\Support\Session::user();
$isVacation = \App\Support\Settings::isVacationMode();
$recentLessons = $recentLessons ?? $lessons ?? [];
$currentWeekSchedule = $currentWeekSchedule ?? [];
?>
<section class="dashboard-hero">
  <div class="hero-card">
    <div class="page-kicker">
      <?php if ($isVacation): ?>
        <span class="badge badge--amber">🌴 Mod Vacanță Activ</span>
      <?php else: ?>
        <span class="badge badge--sage">🟢 Cursuri Active</span>
      <?php endif; ?>
      <span class="badge badge--neutral">Tot ce contează astăzi</span>
    </div>

    <h1 class="hero-card__title">Bună, <?= e($teacher['first_name'] ?? 'profesoară') ?>.</h1>
    <p class="hero-card__description">Organizează grupele, marchează prezența și plata ședințelor, fără zgomot administrativ.</p>

    <div class="hero-search">
      <span class="hero-search__copy">Cu ce începi azi?</span>
      <a href="/teacher/attendance" class="btn btn--sage btn--sm">Deschide prezența</a>
    </div>

    <nav class="hero-shortcuts" aria-label="Acțiuni rapide">
      <a href="/teacher/groups" class="shortcut">
        <span class="shortcut__icon" aria-hidden="true">👥</span>
        <span class="shortcut__label">Grupe</span>
      </a>
      <a href="/teacher/lessons" class="shortcut shortcut--sage">
        <span class="shortcut__icon" aria-hidden="true">🗓</span>
        <span class="shortcut__label">Ședințe</span>
      </a>
      <a href="/teacher/conversations" class="shortcut shortcut--lilac">
        <span class="shortcut__icon" aria-hidden="true">💬</span>
        <span class="shortcut__label">Conversații</span>
      </a>
      <a href="/teacher/settings" class="shortcut shortcut--amber">
        <span class="shortcut__icon" aria-hidden="true">⚙️</span>
        <span class="shortcut__label">Conturi & Setări</span>
      </a>
    </nav>
  </div>

  <aside class="dashboard-side">
    <div class="card <?= $isVacation ? 'card--amber' : '' ?>">
      <div class="card-header">
        <div class="card-header__copy">
          <h2 class="card-title"><?= $isVacation ? '🌴 Mod Vacanță Activ' : 'Control Activitate & Vacanță' ?></h2>
          <p class="card-description"><?= $isVacation ? 'Toate cursurile sunt oprite temporar (Vacanță).' : 'Oprește totul cu 1 click când începe vacanța.' ?></p>
        </div>
      </div>
      <form action="/teacher/settings/toggle-vacation" method="POST" class="form-stack">
        <?= csrf_field() ?>
        <input type="hidden" name="return_url" value="/teacher/dashboard">
        <?php if ($isVacation): ?>
          <button type="submit" class="btn btn--sage btn--block">▶️ Oprește Vacanța & Reia Cursurile</button>
        <?php else: ?>
          <button type="submit" class="btn btn--amber btn--block" title="Oprește temporar ședințele (Vacanță de Vară / Iarnă / Pauză)">🌴 Activează Mod Vacanță (Stop Tot)</button>
        <?php endif; ?>
      </form>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-header__copy">
          <h2 class="card-title">Rezumat rapid</h2>
          <p class="card-description">Cabinetul tău didactic</p>
        </div>
      </div>
      <div class="stack stack--sm">
        <div class="content-row">
          <div class="content-row__main">
            <div class="content-row__title"><?= count($groups) ?> grupe active</div>
            <div class="content-row__meta">Program săptămânal recurent</div>
          </div>
          <span class="badge badge--brand">Active</span>
        </div>
        <div class="content-row">
          <div class="content-row__main">
            <div class="content-row__title"><?= count($students) ?> elevi</div>
            <div class="content-row__meta">Prezență & plată per ședință</div>
          </div>
          <span class="badge badge--sage">Înscriși</span>
        </div>
      </div>
    </div>
  </aside>
</section>

<section class="metric-grid" aria-label="Indicatori principali">
  <article class="metric-card">
    <div class="metric-value"><?= count($groups) ?></div>
    <div class="metric-label">Grupe și cursuri</div>
    <div class="metric-note">toate active</div>
  </article>
  <article class="metric-card metric-card--sage">
    <div class="metric-value"><?= count($students) ?></div>
    <div class="metric-label">Elevi înscriși</div>
    <div class="metric-note">vizibilitate individuală</div>
  </article>
  <article class="metric-card metric-card--amber">
    <div class="metric-value"><?= count($lessons) ?></div>
    <div class="metric-label">Ședințe</div>
    <div class="metric-note">în istoricul curent</div>
  </article>
  <article class="metric-card">
    <div class="metric-value"><?= count($feedbacks) ?></div>
    <div class="metric-label">Aprecieri publicate</div>
    <div class="metric-note">către elevi și părinți</div>
  </article>
</section>

<section class="grid-2">
  <article class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Grupele tale</h2>
        <p class="card-description">Acces rapid la elevi, program și materiale</p>
      </div>
      <a href="/teacher/groups" class="btn btn--ghost btn--sm">Vezi toate</a>
    </div>

    <div class="stack">
      <?php foreach (array_slice($groups, 0, 4) as $group): ?>
        <?php $tone = ui_tone_class($group['id']); ?>
        <a href="/teacher/groups/<?= e($group['id']) ?>" class="content-row <?= e($tone) ?>">
          <span class="tone-dot" aria-hidden="true"></span>
          <span class="content-row__main">
            <span class="content-row__title"><?= e($group['name']) ?></span>
            <span class="content-row__meta"><?= e(group_type_label($group['type'])) ?></span>
          </span>
          <span class="badge badge--neutral">Deschide</span>
        </a>
      <?php endforeach; ?>

      <?php if (empty($groups)): ?>
        <div class="empty-state">
          <div>
            <div class="empty-state__icon" aria-hidden="true">＋</div>
            <div class="empty-state__title">Nu ai creat încă o grupă</div>
            <p class="empty-state__text">Creează prima grupă și adaugă elevii cu care lucrezi.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </article>

  <article class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Ședințe recente & cronologice</h2>
        <p class="card-description">Activități programate și cele care tocmai au trecut</p>
      </div>
      <a href="/teacher/lessons" class="btn btn--ghost btn--sm">Toate ședințele</a>
    </div>

    <div class="stack">
      <?php foreach (array_slice($recentLessons, 0, 5) as $lesson): ?>
        <?php
        $isPast = ($lesson['lesson_date'] < date('Y-m-d'));
        $isToday = ($lesson['lesson_date'] === date('Y-m-d'));
        $dateLabel = format_date_long_ro($lesson['lesson_date']);
        $tone = ui_tone_class($lesson['group_id'] ?? $lesson['group_name']);
        ?>
        <a href="/teacher/attendance?group_id=<?= e($lesson['group_id']) ?>&lesson_id=<?= e($lesson['id']) ?>" class="content-row <?= e($tone) ?>">
          <span class="lesson-badge-date">📅 <?= e($dateLabel) ?></span>
          <div class="content-row__main">
            <div class="content-row__title"><?= e($lesson['title']) ?></div>
            <div class="content-row__meta"><?= e($lesson['group_name']) ?> • <?= e($lesson['start_time']) ?>–<?= e($lesson['end_time']) ?></div>
          </div>
          <?php if ($isToday): ?>
            <span class="badge badge--sage">Astăzi</span>
          <?php elseif ($isPast): ?>
            <span class="badge badge--neutral">Efectuată</span>
          <?php else: ?>
            <span class="badge badge--brand">Urmează</span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>

      <?php if (empty($recentLessons)): ?>
        <div class="empty-state">
          <div>
            <div class="empty-state__icon" aria-hidden="true">◷</div>
            <div class="empty-state__title">Nicio ședință înregistrată</div>
            <p class="empty-state__text">Generează sau adaugă ședințele din pagina Ședințe.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </article>

  <article class="card card--sage">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Aprecieri recente</h2>
        <p class="card-description">Mesaje pozitive deja publicate</p>
      </div>
      <a href="/teacher/feedback" class="btn btn--ghost btn--sm">Adaugă</a>
    </div>

    <div class="stack">
      <?php foreach (array_slice($feedbacks, 0, 3) as $feedback): ?>
        <article class="feedback-card">
          <div class="feedback-card__header">
            <span class="badge badge--sage">Pentru <?= e($feedback['first_name']) ?></span>
            <small><?= format_date_ro($feedback['created_at']) ?></small>
          </div>
          <p class="feedback-card__text"><?= e($feedback['content']) ?></p>
        </article>
      <?php endforeach; ?>

      <?php if (empty($feedbacks)): ?>
        <div class="empty-state">
          <div>
            <div class="empty-state__icon" aria-hidden="true">✦</div>
            <div class="empty-state__title">Primul mesaj pozitiv te așteaptă</div>
            <p class="empty-state__text">Publică o observație care arată elevului ce a făcut bine.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </article>

  <article class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Prezență & Plăți Ședințe</h2>
        <p class="card-description">Marchează prezența și bifa de plată pentru fiecare oră</p>
      </div>
      <a href="/teacher/attendance" class="btn btn--sage btn--sm">Deschide catalogul</a>
    </div>

    <div class="stack">
      <?php foreach (array_slice($groups, 0, 4) as $grp): ?>
        <?php $tone = ui_tone_class($grp['id']); ?>
        <a href="/teacher/attendance?group_id=<?= e($grp['id']) ?>" class="content-row <?= e($tone) ?>">
          <span class="tone-dot" aria-hidden="true"></span>
          <span class="content-row__main">
            <span class="content-row__title"><?= e($grp['name']) ?></span>
            <span class="content-row__meta">Catalog prezență & bifa de plată per ședință</span>
          </span>
          <span class="badge badge--sage">Prezență</span>
        </a>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="card">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Orar săptămâna curentă</h2>
        <p class="card-description">Program complet (Duminică – Sâmbătă)</p>
      </div>
      <a href="/teacher/calendar" class="btn btn--ghost btn--sm">Calendar</a>
    </div>

    <div class="stack">
      <?php if (!empty($currentWeekSchedule)): ?>
        <?php foreach ($currentWeekSchedule as $day): ?>
          <div class="week-schedule-day <?= $day['is_today'] ? 'week-schedule-day--today' : '' ?>">
            <div class="week-schedule-day__head">
              <span class="week-schedule-day__title">
                <?= e($day['day_name']) ?> <small class="content-row__meta">(<?= e($day['formatted_date']) ?>)</small>
              </span>
              <?php if ($day['is_today']): ?>
                <span class="badge badge--brand">Astăzi</span>
              <?php endif; ?>
            </div>

            <?php if (!empty($day['lessons'])): ?>
              <?php foreach ($day['lessons'] as $les): ?>
                <div class="week-schedule-day__session">
                  <div>
                    <strong><?= e($les['group_name']) ?></strong>
                    <div class="content-row__meta"><?= e($les['title']) ?> • <?= e($les['start_time']) ?>–<?= e($les['end_time']) ?></div>
                  </div>
                  <a href="/teacher/attendance?group_id=<?= e($les['group_id']) ?>&lesson_id=<?= e($les['id']) ?>" class="btn btn--sage btn--xs">Catalog</a>
                </div>
              <?php endforeach; ?>
            <?php elseif (!empty($day['schedules'])): ?>
              <?php foreach ($day['schedules'] as $sch): ?>
                <div class="week-schedule-day__session">
                  <div>
                    <strong><?= e($sch['group_name']) ?></strong>
                    <div class="content-row__meta">Recurent • <?= e($sch['start_time']) ?>–<?= e($sch['end_time']) ?></div>
                  </div>
                  <a href="/teacher/attendance?group_id=<?= e($sch['group_id']) ?>" class="btn btn--outline btn--xs">Catalog</a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <small class="content-row__meta">Fără ședințe programate</small>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <?php foreach (array_slice($schedules, 0, 4) as $schedule): ?>
          <div class="content-row">
            <span class="tone-dot" aria-hidden="true"></span>
            <div class="content-row__main">
              <div class="content-row__title"><?= e(day_name_ro((int)$schedule['day_of_week'])) ?>, <?= e($schedule['start_time']) ?>–<?= e($schedule['end_time']) ?></div>
              <div class="content-row__meta"><?= e($schedule['group_name']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </article>
</section>
