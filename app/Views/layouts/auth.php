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
  <title><?= e($title ?? 'Autentificare') ?> — Îndrumar</title>

  <?php foreach (['tokens', 'reset', 'base', 'layout', 'components', 'pages'] as $stylesheet): ?>
    <link rel="stylesheet" href="/assets/css/<?= e($stylesheet) ?>.css?v=<?= e($assetVersion('/assets/css/' . $stylesheet . '.css')) ?>">
  <?php endforeach; ?>
</head>
<body class="auth-body">
  <main class="auth-shell">
    <div class="auth-panel">
      <?= $content ?>
    </div>
  </main>
</body>
</html>
