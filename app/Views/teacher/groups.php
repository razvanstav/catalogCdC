<?php $title = 'Grupe și cursuri'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Organizare</span></div>
    <h1 class="page-title">Grupe și cursuri</h1>
    <p class="page-subtitle">Clase, meditații, ateliere și lecții individuale, organizate într-un singur loc.</p>
  </div>
  <div class="page-actions">
    <a href="/teacher/reports?tab=annual" class="btn btn--outline">📊 Istoric Grupe pe Ani</a>
    <button type="button" class="btn btn--primary" data-modal-open="modal-create-group">Creează o grupă</button>
  </div>
</header>

<section class="grid-2">
  <?php foreach ($groups as $group): ?>
    <?php $tone = ui_tone_class($group['id']); ?>
    <article class="card entity-card card--interactive <?= e($tone) ?>">
      <span class="entity-card__accent" aria-hidden="true"></span>
      <div class="entity-card__head">
        <span class="badge badge--brand"><?= e(group_type_label($group['type'])) ?></span>
        <span class="tone-dot" aria-hidden="true"></span>
      </div>
      <div>
        <h2 class="entity-card__title"><?= e($group['name']) ?></h2>
        <p class="entity-card__description"><?= e($group['description'] ?: 'Adaugă o descriere scurtă pentru această grupă.') ?></p>
      </div>
      <footer class="entity-card__footer">
        <small>Spațiu pentru prezență, teme și comunicare</small>
        <a href="/teacher/groups/<?= e($group['id']) ?>" class="btn btn--outline btn--sm">Deschide grupa</a>
      </footer>
    </article>
  <?php endforeach; ?>

  <?php if (empty($groups)): ?>
    <div class="card empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">＋</div>
        <div class="empty-state__title">Creează prima grupă</div>
        <p class="empty-state__text">Poate fi o clasă, o grupă de meditații, un atelier sau o lecție individuală.</p>
        <div class="empty-state__actions">
          <button type="button" class="btn btn--primary" data-modal-open="modal-create-group">Creează grupa</button>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<div id="modal-create-group" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-group-title">
    <h2 class="modal-title" id="create-group-title">Creează o grupă nouă</h2>
    <p class="modal-description">Configurează spațiul în care vei gestiona elevii, ședințele și materialele.</p>

    <form action="/teacher/groups" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label" for="name">Denumirea grupei</label>
        <input type="text" id="name" name="name" class="form-control" placeholder="Exemplu: Matematică — clasa a VII-a" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="type">Tipul grupei</label>
        <select id="type" name="type" class="form-control">
          <option value="school_class">Clasă de școală</option>
          <option value="tutoring_group" selected>Grupă de meditații</option>
          <option value="workshop">Atelier</option>
          <option value="individual_lesson">Lecție individuală</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="description">Descriere și obiective</label>
        <textarea id="description" name="description" class="form-control" placeholder="Ce lucrați și care sunt obiectivele grupei"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label" for="color_tag">Accent vizual</label>
        <select id="color_tag" name="color_tag" class="form-control">
          <option value="#4A77DA">Albastru didactic</option>
          <option value="#258B5A">Verde salvie</option>
          <option value="#B86A15">Chihlimbar cald</option>
          <option value="#826EE7">Lavandă</option>
          <option value="#D45D71">Roz discret</option>
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Salvează grupa</button>
      </div>
    </form>
  </section>
</div>
