<?php $title = 'Anunțuri'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Comunicare generală</span></div>
    <h1 class="page-title">Anunțuri și noutăți</h1>
    <p class="page-subtitle">Trimite modificări de program și informații utile unei grupe sau tuturor familiilor.</p>
  </div>
  <div class="page-actions"><button type="button" class="btn btn--primary" data-modal-open="modal-create-announcement">Publică anunț</button></div>
</header>

<section class="stack">
  <?php foreach ($announcements as $announcement): ?>
    <article class="card announcement-card <?= e(ui_tone_class($announcement['id'])) ?>">
      <span class="entity-card__accent" aria-hidden="true"></span>
      <div class="announcement-card__header">
        <span class="badge badge--brand"><?= e($announcement['group_name'] ?: 'Toate grupele') ?></span>
        <time class="content-row__meta"><?= format_date_ro($announcement['created_at']) ?></time>
      </div>
      <h2 class="announcement-card__title"><?= e($announcement['title']) ?></h2>
      <p class="announcement-card__body"><?= nl2br(e($announcement['content'])) ?></p>
    </article>
  <?php endforeach; ?>

  <?php if (empty($announcements)): ?>
    <div class="card empty-state"><div><div class="empty-state__icon">◌</div><div class="empty-state__title">Niciun anunț publicat</div><p class="empty-state__text">Publică un mesaj scurt pentru o grupă sau pentru toate familiile.</p></div></div>
  <?php endif; ?>
</section>

<div id="modal-create-announcement" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-announcement-title">
    <h2 class="modal-title" id="create-announcement-title">Publică un anunț</h2>
    <p class="modal-description">Mesajul va apărea în fluxul elevilor și părinților vizați.</p>
    <form action="/teacher/announcements" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group"><label class="form-label" for="announcement-group">Destinatari</label><select id="announcement-group" name="group_id" class="form-control"><option value="">Toți elevii și părinții</option><?php foreach ($groups as $group): ?><option value="<?= e($group['id']) ?>"><?= e($group['name']) ?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label class="form-label" for="announcement-title">Titlu</label><input type="text" id="announcement-title" name="title" class="form-control" placeholder="Exemplu: Modificare de program" required></div>
      <div class="form-group"><label class="form-label" for="announcement-content">Mesaj</label><textarea id="announcement-content" name="content" class="form-control" placeholder="Scrie informația clar și concis" required></textarea></div>
      <div class="modal-actions"><button type="button" class="btn btn--ghost" data-modal-close>Renunță</button><button type="submit" class="btn btn--primary">Publică anunțul</button></div>
    </form>
  </section>
</div>
