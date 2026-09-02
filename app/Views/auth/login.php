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

  <div class="divider-label">sau acces direct demo</div>

  <section class="demo-login-panel" aria-labelledby="demo-login-title">
    <h2 class="sr-only" id="demo-login-title">Acces rapid demonstrativ</h2>
    <div class="demo-login-actions">
      <a href="/demo/parent/stu_matei_popescu" class="btn btn--sage btn--block">Login Părinte (Demo)</a>
      <a href="/demo/student" class="btn btn--outline btn--block">Login Elev (Demo)</a>
      <a href="/demo/teacher" class="btn btn--ghost btn--block">Login Profesor / Admin</a>
    </div>
  </section>

  <p class="form-hint text-center">Conturile de acces sunt create și distribuite exclusiv de profesoară din panoul de administrare.</p>
</div>
