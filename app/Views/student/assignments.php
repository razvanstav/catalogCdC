<?php $title = 'Temele mele'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--amber">De pregătit</span></div>
    <h1 class="page-title">Temele mele</h1>
    <p class="page-subtitle">Instrucțiuni clare și încărcare directă a rezolvărilor cu fișier sau text.</p>
  </div>
</header>

<section class="grid-2">
  <?php foreach ($assignments as $assignment): ?>
    <?php
    $tone = ui_tone_class($assignment['group_name'] ?? $assignment['id']);
    $hasSubmitted = !empty($assignment['submission_id']);
    $modalId = 'modal-view-asg-' . $assignment['id'];
    ?>
    <article id="asg-<?= e($assignment['id']) ?>" class="card entity-card <?= e($tone) ?>">
      <span class="entity-card__accent" aria-hidden="true"></span>
      <div class="entity-card__head">
        <span class="badge badge--brand"><?= e($assignment['group_name']) ?></span>
        <span class="badge badge--amber">Termen: <?= format_date_ro($assignment['due_date']) ?></span>
      </div>

      <div>
        <h2 class="entity-card__title"><?= e($assignment['title']) ?></h2>
        
        <div class="assignment-instruction-box">
          <div class="assignment-instruction-title">Cerință & Instrucțiuni de lucru</div>
          <div class="assignment-instruction-text"><?= e($assignment['description'] ?: 'Urmează instrucțiunile primite la curs.') ?></div>
        </div>
      </div>

      <?php if (!empty($assignment['attachment_url'])): ?>
        <?php $isImg = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $assignment['attachment_url']); ?>
        <div class="attachment-preview-box">
          <?php if ($isImg): ?>
            <a href="<?= e($assignment['attachment_url']) ?>" target="_blank" rel="noreferrer" title="Apasă pentru a deschide poza mărită">
              <img src="<?= e($assignment['attachment_url']) ?>" alt="Cerință atașată de profesoară" class="attachment-image">
            </a>
          <?php endif; ?>
          <div class="attachment-actions">
            <a href="<?= e($assignment['attachment_url']) ?>" download class="btn btn--outline btn--xs">
              💾 Descarcă <?= $isImg ? 'poza cerinței' : 'fișa de lucru' ?>
            </a>
            <a href="<?= e($assignment['attachment_url']) ?>" target="_blank" rel="noreferrer" class="btn btn--ghost btn--xs">
              🔍 Deschide în filă nouă
            </a>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($hasSubmitted): ?>
        <div class="submission-box">
          <div class="row row--start">
            <span class="badge badge--sage">✓ Rezolvare trimisă</span>
            <small class="content-row__meta"><?= format_date_ro($assignment['submitted_at']) ?></small>
          </div>
          <?php if (!empty($assignment['file_url'])): ?>
            <div>
              <a href="<?= e($assignment['file_url']) ?>" download class="submission-file-badge">
                💾 Descarcă fișierul trimis: <?= e($assignment['file_name'] ?: 'rezolvare') ?>
              </a>
            </div>
          <?php endif; ?>
          <?php if (!empty($assignment['submission_text'])): ?>
            <p class="card-description"><?= e($assignment['submission_text']) ?></p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="submissions-section">
        <div class="submissions-section__title"><?= $hasSubmitted ? 'Reîncarcă o altă versiune' : 'Încarcă rezolvarea temei' ?></div>
        <form action="/student/assignments/<?= e($assignment['id']) ?>/submit" method="POST" enctype="multipart/form-data" class="form-stack">
          <?= csrf_field() ?>
          <div class="form-group">
            <label class="form-label" for="file-<?= e($assignment['id']) ?>">Alege fișierul rezolvării (cod .py/.cpp/.js, arhivă .zip, poză sau PDF)</label>
            <input type="file" id="file-<?= e($assignment['id']) ?>" name="solution_file" class="form-control" accept=".py,.js,.html,.css,.cpp,.java,.zip,.rar,.pdf,.png,.jpg,.jpeg,.txt">
          </div>
          <div class="form-group">
            <label class="form-label" for="text-<?= e($assignment['id']) ?>">Explicații / Cod text (opțional)</label>
            <textarea id="text-<?= e($assignment['id']) ?>" name="submission_text" class="form-control" placeholder="Scrie aici observații sau întrebări pentru profesoară..."></textarea>
          </div>
          <button type="submit" class="btn btn--sage btn--sm"><?= $hasSubmitted ? 'Reîncarcă rezolvarea' : 'Trimite rezolvarea temei' ?></button>
        </form>
      </div>

      <footer class="entity-card__footer">
        <small>Publicată la <?= format_date_ro($assignment['assigned_date']) ?></small>
        <button type="button" class="btn btn--ghost btn--xs" data-modal-open="<?= e($modalId) ?>">Deschide modal</button>
      </footer>
    </article>

    <!-- Modal Detalii Temă pentru Elev -->
    <div id="<?= e($modalId) ?>" class="modal-backdrop" aria-hidden="true">
      <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="title-<?= e($assignment['id']) ?>">
        <h2 class="modal-title" id="title-<?= e($assignment['id']) ?>"><?= e($assignment['title']) ?></h2>
        <p class="modal-description"><?= e($assignment['group_name']) ?> • Termen: <?= format_date_ro($assignment['due_date']) ?></p>
        
        <div class="assignment-instruction-box">
          <div class="assignment-instruction-title">Cerință & Instrucțiuni</div>
          <div class="assignment-instruction-text"><?= e($assignment['description'] ?: 'Fără instrucțiuni suplimentare.') ?></div>
        </div>

        <?php if (!empty($assignment['attachment_url'])): ?>
          <div class="attachment-preview-box">
            <?php if ($isImg): ?>
              <img src="<?= e($assignment['attachment_url']) ?>" alt="Cerință atașată" class="attachment-image">
            <?php endif; ?>
            <div class="attachment-actions">
              <a href="<?= e($assignment['attachment_url']) ?>" download class="btn btn--outline btn--sm">
                💾 Descarcă <?= $isImg ? 'poza cerinței' : 'fișierul atașat' ?>
              </a>
              <a href="<?= e($assignment['attachment_url']) ?>" target="_blank" rel="noreferrer" class="btn btn--ghost btn--sm">
                🔍 Deschide în filă nouă
              </a>
            </div>
          </div>
        <?php endif; ?>

        <div class="modal-actions">
          <button type="button" class="btn btn--primary" data-modal-close>Am înțeles / Închide</button>
        </div>
      </section>
    </div>
  <?php endforeach; ?>

  <?php if (empty($assignments)): ?>
    <div class="card empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">✓</div>
        <div class="empty-state__title">Temele sunt la zi</div>
        <p class="empty-state__text">Nu ai nicio activitate restantă în acest moment.</p>
      </div>
    </div>
  <?php endif; ?>
</section>
