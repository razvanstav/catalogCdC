<?php
$title = 'Teme și materiale';
$submissionsMap = $submissionsMap ?? [];
?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--amber">Resurse de învățare</span></div>
    <h1 class="page-title">Teme și materiale</h1>
    <p class="page-subtitle">Publică instrucțiuni clare și resurse ușor de deschis de pe telefon, fără să aglomerezi fluxul elevilor.</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn--secondary" data-modal-open="modal-create-material">Adaugă material</button>
    <button type="button" class="btn btn--primary" data-modal-open="modal-create-assignment">Creează temă</button>
  </div>
</header>

<div class="tab-list" data-tab-group="learning-tabs" aria-label="Teme și materiale">
  <button type="button" class="tab-button is-active" data-tab-target="assignments-panel">Teme (<?= count($assignments) ?>)</button>
  <button type="button" class="tab-button" data-tab-target="materials-panel">Materiale (<?= count($materials) ?>)</button>
</div>

<section id="assignments-panel" class="tab-panel" data-tab-panel="learning-tabs">
  <div class="grid-2">
    <?php foreach ($assignments as $assignment): ?>
      <?php $subs = $submissionsMap[$assignment['id']] ?? []; ?>
      <article class="card entity-card card--interactive <?= e(ui_tone_class($assignment['group_id'] ?? $assignment['id'])) ?>">
        <span class="entity-card__accent" aria-hidden="true"></span>
        <div class="entity-card__head">
          <span class="badge badge--brand"><?= e($assignment['group_name']) ?></span>
          <span class="badge badge--amber">Până la <?= format_date_ro($assignment['due_date']) ?></span>
        </div>
        <div>
          <h2 class="entity-card__title"><?= e($assignment['title']) ?></h2>
          <p class="entity-card__description"><?= e($assignment['description'] ?: 'Fără instrucțiuni suplimentare.') ?></p>
        </div>

        <?php if (!empty($assignment['attachment_url'])): ?>
          <?php $isImg = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $assignment['attachment_url']); ?>
          <div class="attachment-preview-box">
            <?php if ($isImg): ?>
              <a href="<?= e($assignment['attachment_url']) ?>" target="_blank" rel="noreferrer" title="Deschide poza mărită">
                <img src="<?= e($assignment['attachment_url']) ?>" alt="Cerință atașată" class="attachment-image">
              </a>
            <?php endif; ?>
            <div class="attachment-actions">
              <a href="<?= e($assignment['attachment_url']) ?>" download class="btn btn--outline btn--xs">
                💾 Descarcă <?= $isImg ? 'poza atașată' : 'fișierul atașat' ?>
              </a>
              <a href="<?= e($assignment['attachment_url']) ?>" target="_blank" rel="noreferrer" class="btn btn--ghost btn--xs">
                🔍 Deschide în filă nouă
              </a>
            </div>
          </div>
        <?php endif; ?>

        <div class="submissions-section">
          <div class="submissions-section__title">Rezolvări trimise de elevi (<?= count($subs) ?>)</div>
          <?php foreach ($subs as $sub): ?>
            <div class="submission-item">
              <div>
                <strong><?= e($sub['first_name'] . ' ' . $sub['last_name']) ?></strong>
                <?php if (!empty($sub['submission_text'])): ?>
                  <div class="content-row__meta"><?= e($sub['submission_text']) ?></div>
                <?php endif; ?>
              </div>
              <div>
                <?php if (!empty($sub['file_url'])): ?>
                  <a href="<?= e($sub['file_url']) ?>" target="_blank" rel="noreferrer" class="submission-file-badge">
                    💾 <?= e($sub['file_name'] ?: 'Descarcă fișier') ?>
                  </a>
                <?php else: ?>
                  <span class="badge badge--neutral">Rezolvare text</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($subs)): ?>
            <small class="content-row__meta">Niciun elev nu a trimis încă rezolvarea.</small>
          <?php endif; ?>
        </div>

        <footer class="entity-card__footer"><small>Publicată pe <?= format_date_ro($assignment['assigned_date']) ?></small><span class="badge badge--sage">Vizibilă familiei</span></footer>
      </article>
    <?php endforeach; ?>
    <?php if (empty($assignments)): ?><div class="card empty-state"><div><div class="empty-state__icon">✓</div><div class="empty-state__title">Nu există teme active</div><p class="empty-state__text">Poți publica o temă nouă pentru orice grupă.</p></div></div><?php endif; ?>
  </div>
</section>

<section id="materials-panel" class="tab-panel" data-tab-panel="learning-tabs" hidden>
  <div class="grid-3">
    <?php foreach ($materials as $material): ?>
      <article class="card entity-card card--interactive <?= e(ui_tone_class($material['group_id'] ?? $material['id'])) ?>">
        <span class="entity-card__accent" aria-hidden="true"></span>
        <div class="entity-card__head"><span class="badge badge--sage"><?= strtoupper(e($material['file_type'])) ?></span><span class="badge badge--neutral"><?= e($material['group_name']) ?></span></div>
        <div><h2 class="entity-card__title"><?= e($material['title']) ?></h2><p class="entity-card__description">Resursă de studiu partajată cu elevii grupei.</p></div>
        <footer class="entity-card__footer"><small>Material didactic</small><?php if (!empty($material['url'])): ?><a href="<?= e($material['url']) ?>" target="_blank" rel="noreferrer" class="btn btn--outline btn--sm">Deschide resursa</a><?php endif; ?></footer>
      </article>
    <?php endforeach; ?>
    <?php if (empty($materials)): ?><div class="card empty-state"><div><div class="empty-state__icon">＋</div><div class="empty-state__title">Nu există materiale</div><p class="empty-state__text">Adaugă un PDF, un video sau un link util.</p></div></div><?php endif; ?>
  </div>
</section>

<div id="modal-create-assignment" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-assignment-title">
    <h2 class="modal-title" id="create-assignment-title">Creează o temă</h2>
    <p class="modal-description">Tema va apărea în conturile elevilor și părinților din grupa selectată.</p>
    <form action="/teacher/assignments" method="POST" enctype="multipart/form-data" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group"><label class="form-label" for="assignment-group">Grupa</label><select id="assignment-group" name="group_id" class="form-control" required><?php foreach ($groups as $group): ?><option value="<?= e($group['id']) ?>"><?= e($group['name']) ?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label class="form-label" for="assignment-title">Titlul temei</label><input type="text" id="assignment-title" name="title" class="form-control" placeholder="Exemplu: Exercițiile 1–10" required></div>
      <div class="form-group"><label class="form-label" for="assignment-due">Termen</label><input type="date" id="assignment-due" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+5 days')) ?>" required></div>
      <div class="form-group"><label class="form-label" for="assignment-description">Instrucțiuni</label><textarea id="assignment-description" name="description" class="form-control" placeholder="Spune clar ce trebuie rezolvat"></textarea></div>
      <div class="form-group">
        <label class="form-label" for="assignment-attachment">Poză sau Fișă atașată la instrucțiuni (opțional)</label>
        <input type="file" id="assignment-attachment" name="attachment" class="form-control" accept="image/*,.pdf,.png,.jpg,.jpeg">
        <small class="form-hint">Atașează o captură de ecran, poză cu enunțul sau o schemă de cod.</small>
      </div>
      <div class="modal-actions"><button type="button" class="btn btn--ghost" data-modal-close>Renunță</button><button type="submit" class="btn btn--primary">Publică tema</button></div>
    </form>
  </section>
</div>

<div id="modal-create-material" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-material-title">
    <h2 class="modal-title" id="create-material-title">Adaugă un material</h2>
    <p class="modal-description">Distribuie un document, un tutorial video sau un link util.</p>
    <form action="/teacher/materials" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group"><label class="form-label" for="material-group">Grupa</label><select id="material-group" name="group_id" class="form-control" required><?php foreach ($groups as $group): ?><option value="<?= e($group['id']) ?>"><?= e($group['name']) ?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label class="form-label" for="material-title">Titlul materialului</label><input type="text" id="material-title" name="title" class="form-control" placeholder="Exemplu: Sinteză de trigonometrie" required></div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label" for="material-type">Tip</label><select id="material-type" name="file_type" class="form-control"><option value="pdf">PDF</option><option value="video">Video</option><option value="link">Link web</option><option value="doc">Document</option></select></div>
        <div class="form-group"><label class="form-label" for="material-url">Adresă web</label><input type="url" id="material-url" name="url" class="form-control" placeholder="https://..."></div>
      </div>
      <div class="modal-actions"><button type="button" class="btn btn--ghost" data-modal-close>Renunță</button><button type="submit" class="btn btn--secondary">Salvează materialul</button></div>
    </form>
  </section>
</div>
