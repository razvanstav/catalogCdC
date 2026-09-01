<?php
$success = \App\Support\Session::getFlash('success');
$error = \App\Support\Session::getFlash('error');
?>
<?php if ($success): ?>
  <div class="alert alert--success" role="status">
    <span aria-hidden="true">✓</span>
    <span><?= e($success) ?></span>
  </div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert--error" role="alert">
    <span aria-hidden="true">!</span>
    <span><?= e($error) ?></span>
  </div>
<?php endif; ?>
