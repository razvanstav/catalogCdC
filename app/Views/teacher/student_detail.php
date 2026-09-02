<?php $title = $student['first_name'] . ' ' . $student['last_name']; ?>
<a href="/teacher/students" class="btn btn--ghost btn--sm">← Înapoi la elevi</a>

<header class="card">
  <div class="section-heading">
    <div class="row row--start">
      <span class="avatar avatar--lg avatar--brand" aria-hidden="true"><?= e(initials($student['first_name'], $student['last_name'])) ?></span>
      <div>
        <div class="page-kicker"><span class="badge badge--brand">Dosar individual</span></div>
        <h1 class="page-title"><?= e(trim($student['first_name'] . ' ' . ($student['father_initial'] ?? '') . ' ' . $student['last_name'])) ?></h1>
        <p class="page-subtitle"><?= e($student['email'] ?: 'E-mail nespecificat') ?> • <?= e($student['phone'] ?: 'Telefon nespecificat') ?> • <strong>Utilizator:</strong> <?= e($student['username'] ?? 'fără cont utilizator') ?></p>
      </div>
    </div>
    <div class="page-actions">
      <button type="button" class="btn btn--outline" data-modal-open="modal-edit-student">✏️ Editează date elev</button>
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
        <label class="sr-only" for="private_notes">Notițe private</label>
        <textarea id="private_notes" name="notes" class="form-control" placeholder="Scrie aici observații pedagogice, ritmul de lucru, nevoi speciale..."><?= e($student['private_notes'] ?? '') ?></textarea>
        <button type="submit" class="btn btn--outline btn--sm">Salvează notița privată</button>
      </form>
    </article>

    <article class="card">
      <div class="card-header"><div class="card-header__copy"><h2 class="card-title">Feedback publicat</h2><p class="card-description">Mesajele vizibile familiei</p></div></div>
      <div class="stack">
        <?php foreach ($feedbacks as $feedback): ?>
          <div class="content-row">
            <span class="tone-dot" aria-hidden="true"></span>
            <div class="content-row__main">
              <div class="content-row__title"><?= e($feedback['content']) ?></div>
              <div class="content-row__meta"><?= e(feedback_category_label($feedback['category'])) ?> • <?= format_date_ro($feedback['created_at']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($feedbacks)): ?><div class="empty-state">Nu există feedback publicat încă.</div><?php endif; ?>
      </div>
    </article>

    <article class="card">
      <div class="card-header"><div class="card-header__copy"><h2 class="card-title">Rezultate la verificări</h2><p class="card-description">Notele și aprecierile acordate</p></div></div>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Verificare</th>
              <th>Data</th>
              <th>Notă / Punctaj</th>
              <th>Feedback public</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $result): ?>
              <tr>
                <td><strong><?= e($result['assessment_title']) ?></strong></td>
                <td><?= format_date_ro($result['assessment_date']) ?></td>
                <td><span class="badge badge--paid"><?= e((string)$result['score']) ?> / <?= e((string)$result['max_score']) ?></span></td>
                <td><?= e($result['published_feedback'] ?: '—') ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($results)): ?>
              <tr><td colspan="4" class="empty-state">Niciun rezultat înregistrat încă.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>
  </div>

  <aside class="stack stack--lg">
    <article class="card">
      <div class="card-header"><div class="card-header__copy"><h2 class="card-title">Stare financiară</h2><p class="card-description">Statutul abonamentului</p></div></div>
      <div class="row row--between">
        <div>
          <div class="content-row__title">Plata orelor</div>
          <div class="content-row__meta"><?= !empty($student['is_paid']) ? 'La zi cu plățile' : 'Necesită atenție' ?></div>
        </div>
        <form action="/teacher/students/<?= e($student['id']) ?>/toggle-paid" method="POST">
          <?= csrf_field() ?>
          <button type="submit" class="btn <?= !empty($student['is_paid']) ? 'btn--outline' : 'btn--amber' ?> btn--sm">
            <?= !empty($student['is_paid']) ? 'Marchează neplătit' : 'Marchează achitat' ?>
          </button>
        </form>
      </div>
    </article>

    <article class="card">
      <div class="card-header">
        <div class="card-header__copy">
          <h2 class="card-title">Părinți și tutori</h2>
          <p class="card-description">Date de contact, telefon și persoane asociate</p>
        </div>
        <button type="button" class="btn btn--outline btn--sm" data-modal-open="modal-link-guardian">＋ Asociază părinte</button>
      </div>
      <div class="stack">
        <?php foreach ($guardians as $guardian): ?>
          <?php $gid = $guardian['id'] ?? $guardian['guardian_id'] ?? '1'; ?>
          <div class="card card--flat">
            <div class="row row--between">
              <div class="row row--start">
                <span class="avatar avatar--sm avatar--sage" aria-hidden="true"><?= e(initials($guardian['first_name'], $guardian['last_name'])) ?></span>
                <div class="content-row__main">
                  <div class="content-row__title">
                    <?= e($guardian['first_name'] . ' ' . $guardian['last_name']) ?>
                    <span class="badge badge--neutral"><?= e($guardian['relationship'] ?: 'Părinte') ?></span>
                  </div>
                  <div class="content-row__meta">
                    <strong>📞 <?= e($guardian['phone'] ?: 'Fără telefon consemnat') ?></strong>
                    • <?= e($guardian['email'] ?: 'Fără e-mail') ?>
                  </div>
                </div>
              </div>
              <button type="button" class="btn btn--ghost btn--sm" data-modal-open="modal-edit-guardian-<?= e($gid) ?>">✏️ Editează contact</button>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($guardians)): ?>
          <div class="empty-state">
            <div>
              <p class="empty-state__text">Niciun părinte asociat acestui elev.</p>
              <button type="button" class="btn btn--primary btn--sm" data-modal-open="modal-link-guardian">Asociază un părinte</button>
            </div>
          </div>
        <?php endif; ?>
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

<div id="modal-edit-student" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="edit-student-title">
    <h2 class="modal-title" id="edit-student-title">Editează datele și accesul elevului</h2>
    <p class="modal-description">Actualizează numele, datele de contact, utilizatorul și parola de conectare.</p>

    <form action="/teacher/students/<?= e($student['id']) ?>/edit" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="edit_last_name">Nume de familie</label>
          <input type="text" id="edit_last_name" name="last_name" class="form-control" value="<?= e($student['last_name']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="edit_first_name">Prenume</label>
          <input type="text" id="edit_first_name" name="first_name" class="form-control" value="<?= e($student['first_name']) ?>" required>
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="edit_father_initial">Inițială tată</label>
          <input type="text" id="edit_father_initial" name="father_initial" maxlength="3" class="form-control" value="<?= e(rtrim($student['father_initial'] ?? '', '.')) ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="edit_phone">Telefon elev</label>
          <input type="tel" id="edit_phone" name="phone" class="form-control" value="<?= e($student['phone'] ?? '') ?>" placeholder="07xx xxx xxx">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="edit_email">E-mail elev</label>
        <input type="email" id="edit_email" name="email" class="form-control" value="<?= e($student['email'] ?? '') ?>" placeholder="elev@exemplu.ro">
      </div>

      <div class="card card--flat">
        <div class="form-group"><span class="badge badge--brand">Conectare elev (Cont acces)</span></div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="edit_username">Utilizator (username)</label>
            <input type="text" id="edit_username" name="username" class="form-control" value="<?= e($student['username'] ?? '') ?>" placeholder="ex: ion.popescu">
            <span class="form-hint">Numele cu care elevul se autentifică în catalog.</span>
          </div>
          <div class="form-group">
            <label class="form-label" for="edit_password">Parolă nouă login</label>
            <input type="password" id="edit_password" name="password" class="form-control" placeholder="Lasă gol pentru a păstra parola actuală">
            <span class="form-hint">Scrie aici dacă vrei să-i setezi o parolă nouă.</span>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Salvează modificările elevului</button>
      </div>
    </form>
  </section>
</div>

<?php foreach ($guardians as $guardian): ?>
  <?php $gid = $guardian['id'] ?? $guardian['guardian_id'] ?? '1'; ?>
  <div id="modal-edit-guardian-<?= e($gid) ?>" class="modal-backdrop" aria-hidden="true">
    <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="edit-guardian-title-<?= e($gid) ?>">
      <h2 class="modal-title" id="edit-guardian-title-<?= e($gid) ?>">Editează contact părinte: <?= e($guardian['first_name'] . ' ' . $guardian['last_name']) ?></h2>
      <p class="modal-description">Dacă părintele și-a schimbat numărul de telefon sau e-mailul, le poți actualiza imediat aici.</p>

      <form action="/teacher/guardians/<?= e($gid) ?>/edit" method="POST" class="form-stack">
        <?= csrf_field() ?>
        <input type="hidden" name="return_student_id" value="<?= e($student['id']) ?>">

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="g_last_name_<?= e($gid) ?>">Nume</label>
            <input type="text" id="g_last_name_<?= e($gid) ?>" name="last_name" class="form-control" value="<?= e($guardian['last_name']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="g_first_name_<?= e($gid) ?>">Prenume</label>
            <input type="text" id="g_first_name_<?= e($gid) ?>" name="first_name" class="form-control" value="<?= e($guardian['first_name']) ?>" required>
          </div>
        </div>

        <div class="card card--flat">
          <div class="form-group">
            <label class="form-label" for="g_phone_<?= e($gid) ?>">Număr de telefon părinte</label>
            <input type="tel" id="g_phone_<?= e($gid) ?>" name="phone" class="form-control" value="<?= e($guardian['phone'] ?? '') ?>" placeholder="07xx xxx xxx">
            <span class="form-hint">Numărul apelabil direct pentru urgențe și mesaje.</span>
          </div>
          <div class="form-group">
            <label class="form-label" for="g_email_<?= e($gid) ?>">E-mail părinte</label>
            <input type="email" id="g_email_<?= e($gid) ?>" name="email" class="form-control" value="<?= e($guardian['email'] ?? '') ?>" placeholder="parinte@familie.ro">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="g_rel_<?= e($gid) ?>">Relație / Calitate</label>
          <input type="text" id="g_rel_<?= e($gid) ?>" name="relationship" class="form-control" value="<?= e($guardian['relationship'] ?? 'Părinte') ?>" placeholder="Mamă, Tată, Tutore">
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
          <button type="submit" class="btn btn--primary">Salvează datele părintelui</button>
        </div>
      </form>
    </section>
  </div>
<?php endforeach; ?>

<div id="modal-link-guardian" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="link-guardian-title">
    <h2 class="modal-title" id="link-guardian-title">Asociază un părinte pentru <?= e($student['first_name']) ?></h2>
    <p class="modal-description">Alege un părinte existent din catalog sau înregistrează un părinte nou.</p>

    <form action="/teacher/students/<?= e($student['id']) ?>/link-guardian" method="POST" class="form-stack">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label" for="assoc_guardian_id">Alege dintre părinții existenți</label>
        <select id="assoc_guardian_id" name="guardian_id" class="form-control">
          <option value="">-- Sau introdu datele unui părinte nou mai jos --</option>
          <?php foreach (($allGuardians ?? []) as $ag): ?>
            <?php
            $agName = trim(($ag['first_name'] ?? '') . ' ' . ($ag['last_name'] ?? ''));
            $agPhone = $ag['phone'] ?? '';
            ?>
            <option value="<?= e($ag['id']) ?>"><?= e($agName) ?><?= $agPhone ? ' (📞 ' . e($agPhone) . ')' : '' ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="card card--flat">
        <div class="form-group"><span class="badge badge--brand">Sau părinte nou</span></div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="assoc_guardian_name">Nume complet părinte</label>
            <input type="text" id="assoc_guardian_name" name="guardian_name" class="form-control" placeholder="Ion Popescu">
          </div>
          <div class="form-group">
            <label class="form-label" for="assoc_guardian_phone">Telefon părinte</label>
            <input type="tel" id="assoc_guardian_phone" name="guardian_phone" class="form-control" placeholder="07xx xxx xxx">
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Asociază la elev</button>
      </div>
    </form>
  </section>
</div>

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
