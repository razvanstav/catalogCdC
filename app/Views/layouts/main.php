<?php
$assetVersion = static function (string $assetPath): string {
    $fullPath = APP_ROOT . '/public' . $assetPath;
    return file_exists($fullPath) ? (string)filemtime($fullPath) : '1';
};
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#4A77DA">
  <meta name="color-scheme" content="light">
  <meta name="description" content="Îndrumar — platformă pentru profesoară, elevi și părinți">
  <title><?= e($title ?? 'Îndrumar') ?> — Îndrumar</title>

  <?php foreach (['tokens', 'reset', 'base', 'layout', 'components', 'pages'] as $stylesheet): ?>
    <link rel="stylesheet" href="/assets/css/<?= e($stylesheet) ?>.css?v=<?= e($assetVersion('/assets/css/' . $stylesheet . '.css')) ?>">
  <?php endforeach; ?>
  <link rel="manifest" href="/manifest.webmanifest?v=<?= e($assetVersion('/manifest.webmanifest')) ?>">
</head>
<body class="app-body">
  <a class="skip-link" href="#main-content">Sari la conținut</a>

  <div class="app-wrapper">
    <?php \App\Support\View::component('demobar'); ?>

    <?php if (\App\Support\Settings::isVacationMode()): ?>
      <aside class="vacation-banner" aria-label="Notificare mod vacanță">
        <div class="vacation-banner__content">
          <span class="vacation-banner__icon" aria-hidden="true">🌴</span>
          <span><strong>Mod Vacanță Activ (Stop Cursuri):</strong> <?= e(\App\Support\Settings::getVacationMessage()) ?></span>
        </div>
        <?php if (\App\Support\Session::role() === 'teacher'): ?>
          <form action="/teacher/settings/toggle-vacation" method="POST" class="inline-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn--sage btn--xs">Reia cursurile</button>
          </form>
        <?php endif; ?>
      </aside>
    <?php endif; ?>

    <div class="app-container">
      <?php \App\Support\View::component('sidebar'); ?>
      <button type="button" class="sidebar-scrim" aria-label="Închide meniul" tabindex="-1"></button>

      <div class="app-main">
        <?php \App\Support\View::component('header'); ?>

        <main class="app-content" id="main-content" tabindex="-1">
          <?php \App\Support\View::component('alert'); ?>
          <?= $content ?>
        </main>
      </div>
    </div>
  </div>

  <script src="/assets/js/app.js?v=<?= e($assetVersion('/assets/js/app.js')) ?>"></script>
</body>
</html>
