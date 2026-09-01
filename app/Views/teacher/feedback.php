<?php $title = 'Feedback pentru familii'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--sage">Aprecieri formative</span></div>
    <h1 class="page-title">Feedback pentru familii</h1>
    <p class="page-subtitle">Trimite mesaje scurte, concrete și calde, astfel încât părintele să vadă progresul, nu doar dificultățile.</p>
  </div>
  <div class="page-actions"><button type="button" class="btn btn--sage" data-modal-open="modal-create-feedback">Trimite o apreciere</button></div>
</header>

<section class="grid-2">
  <?php foreach ($feedbacks as $feedback): ?>
    <article class="feedback-card">
      <div class="feedback-card__header">
        <div class="person-line">
          <span class="avatar avatar--sage" aria-hidden="true"><?= e(initials($feedback['first_name'], $feedback['last_name'])) ?></span>
          <div><div class="content-row__title"><?= e($feedback['first_name'] . ' ' . $feedback['last_name']) ?></div><div class="content-row__meta">Publicat pe <?= format_date_ro($feedback['created_at']) ?></div></div>
        </div>
        <span class="badge badge--sage"><?= e(ucfirst($feedback['category'])) ?></span>
      </div>
      <p class="feedback-card__text">„<?= e($feedback['content']) ?>”</p>
      <footer class="entity-card__footer"><small>Vizibil elevului și familiei</small><span class="badge badge--sage">Publicat</span></footer>
    </article>
  <?php endforeach; ?>

  <?php if (empty($feedbacks)): ?>
    <div class="card empty-state"><div><div class="empty-state__icon">✦</div><div class="empty-state__title">Prima apreciere poate începe aici</div><p class="empty-state__text">Un mesaj scurt și specific ajută familia să susțină progresul.</p><div class="empty-state__actions"><button type="button" class="btn btn--sage" data-modal-open="modal-create-feedback">Scrie feedback</button></div></div></div>
  <?php endif; ?>
</section>

<div id="modal-create-feedback" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-feedback-title">
    <h2 class="modal-title" id="create-feedback-title">Trimite o apreciere</h2>
    <p class="modal-description">Mesajul va fi vizibil în contul elevului și al părintelui.</p>
    <form action="/teacher/feedback" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group"><label class="form-label" for="feedback-student">Elev</label><select id="feedback-student" name="student_id" class="form-control" required><?php foreach ($students as $student): ?><option value="<?= e($student['id']) ?>"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label class="form-label" for="feedback-category">Categorie</label><select id="feedback-category" name="category" class="form-control"><option value="progress">Progres și implicare</option><option value="encouragement">Încurajare</option><option value="attention">Recomandare calmă</option></select></div>
      <div class="form-group">
        <label class="form-label" for="feedback-content">Mesaj</label>
        <textarea id="feedback-content" name="content" class="form-control" placeholder="Alege un text rapid de mai jos sau scrie mesajul..." required></textarea>
        <div class="chip-group">
          <button type="button" class="chip-btn" data-insert-feedback="Foarte atent și implicat pe parcursul întregii ședințe." data-target="feedback-content">🌟 Implicare activă</button>
          <button type="button" class="chip-btn" data-insert-feedback="A finalizat proiectul practic cu succes și autonomie." data-target="feedback-content">💻 Finalizare proiect</button>
          <button type="button" class="chip-btn" data-insert-feedback="Excelentă gândire logică și rapiditate în scrierea algoritmilor." data-target="feedback-content">🧠 Logică algoritmică</button>
          <button type="button" class="chip-btn" data-insert-feedback="A rezolvat cerințele bonus cu multă creativitate." data-target="feedback-content">⚡ Cerințe bonus</button>
          <button type="button" class="chip-btn" data-insert-feedback="A identificat și depanat erorile de cod independent." data-target="feedback-content">🔍 Depanare erori</button>
          <button type="button" class="chip-btn" data-insert-feedback="Recomandăm 15-20 minute de exercițiu suplimentar la bucle și condiții." data-target="feedback-content">🔄 Recomandare exercițiu</button>
        </div>
        <span class="form-hint">Apasă pe oricare buton pentru a introduce instant textul de feedback.</span>
      </div>
      <div class="modal-actions"><button type="button" class="btn btn--ghost" data-modal-close>Renunță</button><button type="submit" class="btn btn--sage">Publică feedbackul</button></div>
    </form>
  </section>
</div>
