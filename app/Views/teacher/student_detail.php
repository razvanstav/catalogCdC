<?php $title = $student['first_name'] . ' ' . $student['last_name']; ?>
<a href="/teacher/students" class="btn btn--ghost btn--sm">← Înapoi la elevi</a>

<header class="card">
  <div class="section-heading">
    <div class="row row--start">
      <span class="avatar avatar--lg avatar--brand" aria-hidden="true"><?= e(initials($student['first_name'], $student['last_name'])) ?></span>
      <div>
        <div class="page-kicker"><span class="badge badge--brand">Dosar individual</span></div>
        <h1 class="page-title"><?= e(trim($student['first_name'] . ' ' . ($student['father_initial'] ?? '') . ' ' . $student['last_name'])) ?></h1>
        <p class="page-subtitle"><?= e($student['email'] ?: 'E-mail nespecificat') ?> • <?= e($student['phone'] ?: 'Telefon nespecificat') ?></p>
      </div>
    </div>
    <div class="page-actions">
      <button type="button" class="btn btn--sage" data-modal-open="modal-create-feedback">Publică feedback</button>
    </div>
  </div>
</header>

<section class="detail-grid">
  <div class="stack stack--lg">
    <article class="card card--amber">
      <div class="card-header">
        <div class="card-header__copy">
          <h2 class="card-title">Notițe private ale profesorului</h2>
          <p class="card-description">Nu sunt expuse elevului sau părinților.</p>
        </div>
        <span class="badge badge--amber">Confidențial</span>
      </div>
      <form action="/teacher/students/<?= e($student['id']) ?>/notes" method="POST" class="form-stack">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label" for="private_notes">Observații interne</label>
          <textarea id="private_notes" name="private_notes" class="form-control" placeholder="Dificultăți, ritm de lucru, teme de reluat..." rows="6"><?= e($student['private_notes'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn--amber">Salvează notița privată</button>
        </div>
      </form>
    </article>

    <article class="card">
      <div class="card-header">
        <div class="card-header__copy">
          <h2 class="card-title">Istoric evaluări</h2>
          <p class="card-description">Scoruri, feedback public și note interne</p>
        </div>
        <span class="badge badge--neutral"><?= count($results) ?> rezultate</span>
      </div>
      <div class="stack">
        <?php foreach ($results as $result): ?>
          <article class="card card--flat result-card">
            <div>
              <div class="page-kicker">
                <span class="badge badge--brand"><?= e($result['group_name']) ?></span>
                <span class="badge badge--neutral"><?= format_date_ro($result['assessment_date']) ?></span>
              </div>
              <h3 class="card-title"><?= e($result['assessment_title']) ?></h3>
              <?php if (!empty($result['published_feedback'])): ?>
                <div class="feedback-card">
                  <div class="feedback-card__header"><span class="badge badge--sage">Feedback publicat</span></div>
                  <p class="feedback-card__text"><?= e($result['published_feedback']) ?></p>
                </div>
              <?php endif; ?>
              <?php if (!empty($result['private_teacher_notes'])): ?>
                <div class="private-note">
                  <div class="private-note__header"><span class="private-note__title">Notiță internă</span></div>
                  <p class="private-note__text"><?= e($result['private_teacher_notes']) ?></p>
                </div>
              <?php endif; ?>
            </div>
            <div class="result-score">
              <div class="result-score__value"><?= number_format((float)$result['score'], 2) ?></div>
              <div class="result-score__max">scor</div>
            </div>
          </article>
        <?php endforeach; ?>
        <?php if (empty($results)): ?><div class="empty-state"><div><p class="empty-state__text">Nu există evaluări consemnate încă.</p></div></div><?php endif; ?>
      </div>
    </article>
  </div>

  <aside class="stack stack--lg">
    <article class="card card--sage">
      <div class="card-header">
        <div class="card-header__copy"><h2 class="card-title">Aprecieri publicate</h2><p class="card-description">Vizibile familiei și elevului</p></div>
        <button type="button" class="btn btn--outline btn--sm" data-modal-open="modal-create-feedback">Adaugă</button>
      </div>
      <div class="stack">
        <?php foreach ($feedbacks as $feedback): ?>
          <article class="feedback-card">
            <div class="feedback-card__header"><span class="badge badge--sage">Publicat</span><small><?= format_date_ro($feedback['created_at']) ?></small></div>
            <p class="feedback-card__text"><?= e($feedback['content']) ?></p>
          </article>
        <?php endforeach; ?>
        <?php if (empty($feedbacks)): ?><div class="empty-state"><div><p class="empty-state__text">Nicio apreciere publicată încă.</p></div></div><?php endif; ?>
      </div>
    </article>

    <article class="card">
      <div class="card-header"><div class="card-header__copy"><h2 class="card-title">Părinți și tutori</h2><p class="card-description">Persoanele asociate elevului</p></div></div>
      <div class="stack">
        <?php foreach ($guardians as $guardian): ?>
          <div class="content-row">
            <span class="avatar avatar--sm avatar--sage" aria-hidden="true"><?= e(initials($guardian['first_name'], $guardian['last_name'])) ?></span>
            <div class="content-row__main">
              <div class="content-row__title"><?= e($guardian['first_name'] . ' ' . $guardian['last_name']) ?></div>
              <div class="content-row__meta"><?= e($guardian['relationship']) ?> • <?= e($guardian['phone'] ?: 'fără telefon') ?> • <?= e($guardian['email'] ?: 'fără e-mail') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </article>

    <article class="card">
      <div class="card-header"><div class="card-header__copy"><h2 class="card-title">Grupe active</h2><p class="card-description">Unde este înscris elevul</p></div></div>
      <div class="stack">
        <?php foreach ($groups as $group): ?>
          <?php $tone = ui_tone_class($group['id']); ?>
          <a href="/teacher/groups/<?= e($group['id']) ?>" class="content-row <?= e($tone) ?>">
            <span class="tone-dot" aria-hidden="true"></span>
            <div class="content-row__main"><div class="content-row__title"><?= e($group['name']) ?></div><div class="content-row__meta"><?= e(group_type_label($group['type'])) ?></div></div>
          </a>
        <?php endforeach; ?>
      </div>
    </article>
  </aside>
</section>

<div id="modal-create-feedback" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="feedback-modal-title">
    <h2 class="modal-title" id="feedback-modal-title">Publică feedback pentru <?= e($student['first_name']) ?></h2>
    <p class="modal-description">Mesajul devine vizibil elevului și familiei.</p>
    <form action="/teacher/feedback" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <input type="hidden" name="student_id" value="<?= e($student['id']) ?>">
      <div class="form-group">
        <label class="form-label" for="category">Categorie</label>
        <select id="category" name="category" class="form-control">
          <option value="progress">Progres și implicare</option>
          <option value="encouragement">Încurajare</option>
          <option value="attention">Recomandare blândă</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="content">Mesajul public</label>
        <textarea id="content" name="content" class="form-control" placeholder="Exemplu: A explicat foarte clar colegilor metoda folosită..." required></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--sage">Publică feedbackul</button>
      </div>
    </form>
  </section>
</div>
