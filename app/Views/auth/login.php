<div class="auth-card">
  <div class="auth-brand">
    <img src="/assets/images/app-icon.svg" alt="Logo Îndrumar" class="auth-logo">
    <h1 class="auth-title">Catalog Cursuri Programare</h1>
    <p class="auth-subtitle">Spațiul de învățare pentru profesoară, elevi și părinți.</p>
  </div>

  <?php \App\Support\View::component('alert'); ?>

  <form action="/login" method="POST" class="form-stack">
    <?= csrf_field() ?>

    <div class="form-group">
      <label class="form-label" for="email">Nume utilizator sau e-mail</label>
      <input type="text" id="email" name="email" class="form-control" placeholder="utilizator sau email" autocomplete="username" required>
    </div>

    <div class="form-group">
      <label class="form-label" for="password">Parolă</label>
      <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" autocomplete="current-password" required>
    </div>

    <button type="submit" class="btn btn--primary btn--lg btn--block">Autentificare în Cont</button>
  </form>

  <p class="form-hint text-center">Conturile de acces sunt create și distribuite de profesoară din panoul de administrare.</p>
</div>
