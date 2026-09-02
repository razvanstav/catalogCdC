<?php
$title = 'Setări & Administrare Conturi';
$user = \App\Support\Session::user();
$allUsers = $allUsers ?? [];
$groups = $groups ?? [];
$students = $students ?? [];
?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Administrare & Profil</span></div>
    <h1 class="page-title">Setări și Creare Conturi</h1>
    <p class="page-subtitle">Gestionează profilul tău didactic și creează conturi protejate cu user și parolă pentru elevi și părinți.</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn--brand" data-modal-open="modal-create-teacher-user">+ Cont Profesor Nou</button>
    <button type="button" class="btn btn--primary" data-modal-open="modal-create-student-user">+ Cont Elev Nou</button>
    <button type="button" class="btn btn--sage" data-modal-open="modal-create-parent-user">+ Cont Părinte Nou</button>
  </div>
</header>

<div class="tab-list" data-tab-group="settings-tabs" aria-label="Opțiuni setări">
  <button type="button" class="tab-button is-active" data-tab-target="tab-accounts">Conturi Utilizatori (<?= count($allUsers) ?>)</button>
  <button type="button" class="tab-button" data-tab-target="tab-vacation">Mod Vacanță / Stop Site</button>
  <button type="button" class="tab-button" data-tab-target="tab-profile">Profil Didactic</button>
</div>

<section id="tab-accounts" class="tab-panel" data-tab-panel="settings-tabs">
  <div class="card card--flush">
    <div class="panel-toolbar">
      <div>
        <div class="panel-toolbar__title">Utilizatori cu acces în platformă</div>
        <div class="panel-toolbar__meta">Elevii și părinții se autentifică folosind credențialele create de tine aici</div>
      </div>
    </div>

    <div class="stack">
      <?php foreach ($allUsers as $u): ?>
        <?php
        $roleLabel = $u['role'] === 'teacher' ? 'Profesor' : ($u['role'] === 'parent' ? 'Părinte' : 'Elev');
        $roleBadgeClass = $u['role'] === 'teacher' ? 'badge--brand' : ($u['role'] === 'parent' ? 'badge--sage' : 'badge--neutral');
        ?>
        <div class="content-row">
          <span class="avatar avatar--brand" aria-hidden="true"><?= e(initials($u['first_name'], $u['last_name'])) ?></span>
          <div class="content-row__main">
            <div class="row row--start">
              <span class="content-row__title"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></span>
              <span class="badge <?= e($roleBadgeClass) ?>"><?= e($roleLabel) ?></span>
            </div>
            <div class="content-row__meta">User login: <strong><?= e($u['username'] ?: '-') ?></strong> • Email: <?= e($u['email']) ?> <?= $u['phone'] ? ' • Tel: ' . e($u['phone']) : '' ?></div>
          </div>

          <div class="row row--center">
            <form action="/teacher/settings/reset-password" method="POST" class="inline-form">
              <?= csrf_field() ?>
              <input type="hidden" name="user_id" value="<?= e($u['id']) ?>">
              <input type="hidden" name="new_password" value="parola123">
              <button type="submit" class="btn btn--outline btn--xs" title="Resetează parola la parola123">Reset Parolă</button>
            </form>

            <?php if ($u['role'] !== 'teacher'): ?>
              <form action="/teacher/settings/delete-user" method="POST" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= e($u['id']) ?>">
                <button type="submit" class="btn btn--ghost btn--xs" title="Șterge contul">Șterge</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php $isVacation = \App\Support\Settings::isVacationMode(); ?>
<section id="tab-vacation" class="tab-panel" data-tab-panel="settings-tabs" hidden>
  <div class="card <?= $isVacation ? 'card--amber' : '' ?>">
    <div class="card-header">
      <div class="card-header__copy">
        <h2 class="card-title">Mod Vacanță (Pauză Generală Cursuri / Vacanță de Vară)</h2>
        <p class="card-description">Oprește sau reia dintr-un singur loc toate cursurile, ședințele și notificările din platformă.</p>
      </div>
      <span class="badge <?= $isVacation ? 'badge--amber' : 'badge--sage' ?>">
        <?= $isVacation ? '🌴 Vacanță Activă' : '🟢 Cursuri Active' ?>
      </span>
    </div>

    <form action="/teacher/settings/toggle-vacation" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <input type="hidden" name="return_url" value="/teacher/settings">
      
      <div class="form-group">
        <label class="form-label" for="vacation_message">Mesaj afișat elevilor și părinților în perioada de vacanță</label>
        <input type="text" id="vacation_message" name="vacation_message" class="form-control" value="<?= e(\App\Support\Settings::getVacationMessage()) ?>" placeholder="ex: Suntem în vacanța de vară! Ședințele se reiau în curând.">
      </div>

      <div class="form-actions">
        <?php if ($isVacation): ?>
          <button type="submit" class="btn btn--sage">▶️ Dezactivează Modul Vacanță & Reia Cursurile</button>
        <?php else: ?>
          <button type="submit" class="btn btn--amber">🌴 Activează Modul Vacanță (Oprește Tot Site-ul)</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>

<section id="tab-profile" class="tab-panel" data-tab-panel="settings-tabs" hidden>
  <div class="settings-layout">
    <aside class="card profile-summary">
      <span class="avatar avatar--xl avatar--brand" aria-hidden="true"><?= e(initials($user['first_name'] ?? '', $user['last_name'] ?? '')) ?></span>
      <h2 class="profile-summary__name"><?= e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h2>
      <p class="profile-summary__role"><?= e($teacher['title'] ?? 'Profesor') ?></p>
      <span class="badge badge--sage">Profil activ</span>
      <div class="profile-summary__meta">
        <strong>Cabinet:</strong> <?= e($workspace['name'] ?? 'Cabinet Didactic') ?>
      </div>
    </aside>

    <section class="card settings-card">
      <form action="/teacher/settings" method="POST" class="form-stack">
        <?= csrf_field() ?>
        
        <div class="card-header">
          <div class="card-header__copy">
            <h2 class="card-title">Date Personale, Contact & Conectare</h2>
            <p class="card-description">Poți modifica oricând datele tale de identificare, cabinetul și credențialele de login.</p>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="first-name">Prenume</label>
            <input type="text" id="first-name" name="first_name" class="form-control" value="<?= e($user['first_name'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="last-name">Nume de familie</label>
            <input type="text" id="last-name" name="last_name" class="form-control" value="<?= e($user['last_name'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="teacher-username">Nume utilizator login (username)</label>
            <input type="text" id="teacher-username" name="username" class="form-control" value="<?= e($user['username'] ?? '') ?>" placeholder="ex: profesor" required>
            <span class="form-hint">Numele scurt cu care te autentifici în aplicație.</span>
          </div>
          <div class="form-group">
            <label class="form-label" for="teacher-email">E-mail</label>
            <input type="email" id="teacher-email" name="email" class="form-control" value="<?= e($user['email'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="teacher-phone">Telefon</label>
            <input type="tel" id="teacher-phone" name="phone" class="form-control" value="<?= e($user['phone'] ?? $teacher['phone'] ?? '') ?>" placeholder="07xx xxx xxx">
          </div>
          <div class="form-group">
            <label class="form-label" for="teacher-title">Titlu sau specializare didactică</label>
            <input type="text" id="teacher-title" name="title" class="form-control" value="<?= e($teacher['title'] ?? 'Profesor Programare') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="workspace-name">Nume Cabinet Didactic (Workspace)</label>
          <input type="text" id="workspace-name" name="workspace_name" class="form-control" value="<?= e($workspace['name'] ?? 'Cabinet Didactic') ?>" placeholder="Cabinet Didactic — Prof. ...">
          <span class="form-hint">Numele afișat în sistem pentru spațiul tău educațional.</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="teacher-new-password">Schimbare parolă login</label>
          <input type="password" id="teacher-new-password" name="new_password" class="form-control" placeholder="Lasă gol pentru a păstra parola actuală">
          <span class="form-hint">Completează doar dacă dorești să-ți schimbi parola de acces.</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="teacher-bio">Despre tine și abordarea pedagogică</label>
          <textarea id="teacher-bio" name="bio" class="form-control" rows="3" placeholder="Scurtă prezentare didactică..."><?= e($teacher['bio'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn--primary">Salvează toate modificările mele</button>
        </div>
      </form>
    </section>
  </div>
</section>

<!-- Modal Creare Cont Elev -->
<div id="modal-create-student-user" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-student-user-title">
    <h2 class="modal-title" id="create-student-user-title">Creează Cont de Elev</h2>
    <p class="modal-description">Generează contul cu care elevul se va conecta pe platformă.</p>
    <form action="/teacher/settings/create-student-account" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="stu_first_name">Prenume</label>
          <input type="text" id="stu_first_name" name="first_name" class="form-control" placeholder="Matei" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="stu_last_name">Nume</label>
          <input type="text" id="stu_last_name" name="last_name" class="form-control" placeholder="Popescu" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="stu_email">E-mail / Nume utilizator conectare</label>
        <input type="email" id="stu_email" name="email" class="form-control" placeholder="matei@elev.ro" required>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="stu_password">Parolă inițială</label>
          <input type="text" id="stu_password" name="password" class="form-control" value="parola123" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="stu_group_id">Înscrie în grupă (opțional)</label>
          <select id="stu_group_id" name="group_id" class="form-control">
            <option value="">Fără grupă momentan</option>
            <?php foreach ($groups as $g): ?>
              <option value="<?= e($g['id']) ?>"><?= e($g['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Creează contul elevului</button>
      </div>
    </form>
  </section>
</div>

<!-- Modal Creare Cont Părinte -->
<div id="modal-create-parent-user" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-parent-user-title">
    <h2 class="modal-title" id="create-parent-user-title">Creează Cont de Părinte</h2>
    <p class="modal-description">Generează contul părintelui și asociază-l cu elevul său.</p>
    <form action="/teacher/settings/create-parent-account" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="par_first_name">Prenume părinte</label>
          <input type="text" id="par_first_name" name="first_name" class="form-control" placeholder="Radu" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="par_last_name">Nume părinte</label>
          <input type="text" id="par_last_name" name="last_name" class="form-control" placeholder="Popescu" required>
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="par_email">E-mail / Username conectare</label>
          <input type="email" id="par_email" name="email" class="form-control" placeholder="radu.popescu@familie.ro" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="par_phone">Telefon</label>
          <input type="tel" id="par_phone" name="phone" class="form-control" placeholder="07xx xxx xxx">
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="par_password">Parolă inițială</label>
          <input type="text" id="par_password" name="password" class="form-control" value="parola123" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="par_student_id">Copil asociat (Elev)</label>
          <select id="par_student_id" name="student_id" class="form-control" required>
            <option value="">Alege elevul...</option>
            <?php foreach ($students as $st): ?>
              <option value="<?= e($st['id']) ?>"><?= e($st['first_name'] . ' ' . $st['last_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--sage">Creează contul părintelui</button>
      </div>
    </form>
  </section>
</div>

<!-- Modal Creare Cont Profesor Nou -->
<div id="modal-create-teacher-user" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-teacher-user-title">
    <h2 class="modal-title" id="create-teacher-user-title">Adaugă un Profesor Nou</h2>
    <p class="modal-description">Noul profesor va avea propriul său cabinet didactic dedicat, pornind cu catalogul și orarul complet de la zero.</p>
    <form action="/teacher/settings/create-teacher-account" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="tch_first_name">Prenume profesor</label>
          <input type="text" id="tch_first_name" name="first_name" class="form-control" placeholder="Elena" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="tch_last_name">Nume de familie</label>
          <input type="text" id="tch_last_name" name="last_name" class="form-control" placeholder="Marinescu" required>
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="tch_username">Nume utilizator (username conectare)</label>
          <input type="text" id="tch_username" name="username" class="form-control" placeholder="elena.marinescu" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="tch_email">E-mail profesor</label>
          <input type="email" id="tch_email" name="email" class="form-control" placeholder="elena.marinescu@indrumar.ro" required>
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="tch_phone">Telefon</label>
          <input type="tel" id="tch_phone" name="phone" class="form-control" placeholder="07xx xxx xxx">
        </div>
        <div class="form-group">
          <label class="form-label" for="tch_title">Titlu / Specializare</label>
          <input type="text" id="tch_title" name="title" class="form-control" placeholder="Profesor Matematică & Robotică" value="Profesor">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="tch_workspace_name">Nume Cabinet Didactic (Catalog de la zero)</label>
        <input type="text" id="tch_workspace_name" name="workspace_name" class="form-control" placeholder="Cabinet Didactic — Prof. Elena Marinescu">
        <span class="form-hint">Noul profesor va avea spațiul complet izolat (elevi, grupe, orar și evaluări proprii).</span>
      </div>
      <div class="form-group">
        <label class="form-label" for="tch_password">Parolă de acces</label>
        <input type="text" id="tch_password" name="password" class="form-control" value="parola123" required>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Creează contul de profesor</button>
      </div>
    </form>
  </section>
</div>

