<?php
$user = \App\Support\Session::user();
$role = \App\Support\Session::role();
$children = $user['children'] ?? [];
$activeStudentId = \App\Support\Session::activeStudentId();
$roleLabels = ['teacher' => 'Profesor', 'parent' => 'Părinte', 'student' => 'Elev'];
?>
<header class="app-header">
  <div class="header-leading">
    <button type="button" class="mobile-menu-toggle" aria-label="Deschide meniul" aria-controls="app-sidebar" aria-expanded="false">
      <span class="mobile-menu-lines" aria-hidden="true"></span>
    </button>

    <?php if ($role === 'parent' && count($children) > 1): ?>
      <div class="child-switcher">
        <span class="child-switcher-label">Copil</span>
        <div class="child-switcher-list">
          <?php foreach ($children as $childOption): ?>
            <?php $isCurrent = $childOption['id'] === $activeStudentId; ?>
            <a href="/parent/child/<?= e($childOption['id']) ?>" class="btn btn--sm <?= $isCurrent ? 'btn--primary' : 'btn--outline' ?>" <?= $isCurrent ? 'aria-current="true"' : '' ?>>
              <?= e($childOption['first_name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <span class="header-context">Îndrumar • cabinetul tău digital</span>
    <?php endif; ?>
  </div>

  <div class="header-trailing">
    <div class="header-user">
      <div class="header-user-copy">
        <div class="header-user-name"><?= e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></div>
        <div class="header-user-role"><?= e($roleLabels[$role] ?? ucfirst((string)$role)) ?><?= isset($user['title']) ? ' • ' . e($user['title']) : '' ?></div>
      </div>
      <span class="header-avatar" aria-hidden="true"><?= e(initials($user['first_name'] ?? 'U', $user['last_name'] ?? '')) ?></span>
    </div>
  </div>
</header>
