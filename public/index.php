<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/app/Support/Autoloader.php';
\App\Support\Autoloader::register(APP_ROOT);
require_once APP_ROOT . '/app/Support/Helpers.php';
\App\Support\Session::start();
require_once APP_ROOT . '/routes/web.php';

$method = \App\Support\Request::method();
$uri = \App\Support\Request::uri();

try {
    \App\Support\Router::dispatch($method, $uri);
} catch (\Throwable $exception) {
    error_log('Unhandled Exception: ' . $exception->getMessage() . "\n" . $exception->getTraceAsString());
    $debug = filter_var((string)getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);

    if (!$debug) {
        \App\Support\Response::serverError();
        return;
    }

    http_response_code(500);
    $escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="ro">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="theme-color" content="#4A77DA">
      <title>Eroare de dezvoltare — Îndrumar</title>
      <?php foreach (['tokens', 'reset', 'base', 'layout', 'components', 'pages'] as $stylesheet): ?>
        <link rel="stylesheet" href="/assets/css/<?= $escape($stylesheet) ?>.css">
      <?php endforeach; ?>
    </head>
    <body class="auth-body">
      <main class="auth-shell debug-shell">
        <section class="card debug-card">
          <div class="page-kicker"><span class="badge badge--rose">Doar în dezvoltare</span></div>
          <h1 class="state-title">Eroare de execuție</h1>
          <p class="state-text"><strong>Mesaj:</strong> <?= $escape($exception->getMessage()) ?></p>
          <p class="state-text"><strong>Fișier:</strong> <?= $escape($exception->getFile()) ?>, linia <?= (int)$exception->getLine() ?></p>
          <pre class="debug-trace"><code><?= $escape($exception->getTraceAsString()) ?></code></pre>
        </section>
      </main>
    </body>
    </html>
    <?php
}
