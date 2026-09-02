<?php
$role = \App\Support\Session::role();
$currentUri = \App\Support\Request::uri();

$icons = [
  'home' => '<path d="M3 11.5 12 4l9 7.5"></path><path d="M5.5 10.5V20h13v-9.5"></path><path d="M9.5 20v-6h5v6"></path>',
  'grid' => '<rect x="3" y="3" width="7" height="7" rx="2"></rect><rect x="14" y="3" width="7" height="7" rx="2"></rect><rect x="3" y="14" width="7" height="7" rx="2"></rect><rect x="14" y="14" width="7" height="7" rx="2"></rect>',
  'groups' => '<circle cx="9" cy="8" r="3"></circle><path d="M3 20v-1a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v1"></path><circle cx="17" cy="9" r="2.5"></circle><path d="M16 14h1a4 4 0 0 1 4 4v2"></path>',
  'attendance' => '<rect x="3" y="3" width="18" height="18" rx="4"></rect><path d="m8 12 2.5 2.5L16.5 8.5"></path>',
  'calendar' => '<rect x="3" y="5" width="18" height="16" rx="4"></rect><path d="M8 3v4M16 3v4M3 10h18"></path>',
  'document' => '<path d="M6 3h8l4 4v14H6z"></path><path d="M14 3v5h5M9 12h6M9 16h6"></path>',
  'result' => '<path d="m4 8 8-4 8 4-8 4z"></path><path d="m6 11 6 3 6-3v5l-6 3-6-3z"></path>',
  'spark' => '<path d="m12 3-1.1 3.1a5.3 5.3 0 0 1-3.2 3.2L4.5 10.5l3.2 1.2a5.3 5.3 0 0 1 3.2 3.2L12 18l1.1-3.1a5.3 5.3 0 0 1 3.2-3.2l3.2-1.2-3.2-1.2a5.3 5.3 0 0 1-3.2-3.2z"></path><path d="M5 3v4M3 5h4"></path>',
  'announce' => '<path d="m3 11 16-5v12L3 13z"></path><path d="M8 14.5V20h4l-1.5-4.5M21 10v4"></path>',
  'chat' => '<path d="M20 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h9a4 4 0 0 1 4 4z"></path><path d="M8 9h7M8 13h5"></path>',
  'reports' => '<path d="M4 20V10M10 20V5M16 20v-7M22 20V3"></path><path d="M2 20h22"></path>',
  'settings' => '<circle cx="12" cy="12" r="3"></circle><path d="M19 12a7 7 0 0 0-.1-1l2-1.5-2-3.4-2.4 1a7 7 0 0 0-1.7-1L14.5 3h-5l-.3 3.1a7 7 0 0 0-1.7 1l-2.4-1-2 3.4 2 1.5a7 7 0 0 0 0 2l-2 1.5 2 3.4 2.4-1a7 7 0 0 0 1.7 1l.3 3.1h5l.3-3.1a7 7 0 0 0 1.7-1l2.4 1 2-3.4-2-1.5a7 7 0 0 0 .1-1z"></path>',
  'clock' => '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>',
  'book' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>',
  'goal' => '<circle cx="12" cy="12" r="9"></circle><circle cx="12" cy="12" r="5"></circle><circle cx="12" cy="12" r="1"></circle>',
  'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="m16 17 5-5-5-5M21 12H9"></path>',
];

$icon = static function (string $name) use ($icons): string {
    $path = $icons[$name] ?? $icons['grid'];
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . $path . '</svg>';
};

$teacherNav = [
  ['/teacher/students', 'groups', '1. Elevi'],
  ['/teacher/groups', 'grid', '2. Grupe'],
  ['/teacher/calendar', 'clock', '3. Orar'],
  ['/teacher/attendance', 'attendance', '4. Ședințe'],
  ['/teacher/assessments', 'result', '5. Evaluări'],
  ['/teacher/dashboard', 'home', 'Panou principal'],
  ['/teacher/assignments', 'document', 'Teme & Materiale'],
  ['/teacher/conversations', 'chat', 'Conversații'],
  ['/teacher/reports', 'reports', 'Rapoarte & Istoric'],
  ['/teacher/settings', 'settings', 'Setări'],
];

$parentNav = [
  ['/parent/dashboard', 'home', 'Acasă'],
  ['/parent/timetable', 'clock', 'Orar'],
  ['/parent/attendance', 'attendance', 'Prezență'],
  ['/parent/assignments', 'document', 'Teme'],
  ['/parent/results', 'result', 'Rezultate'],
  ['/parent/feedback', 'spark', 'Aprecieri'],
  ['/parent/goals', 'goal', 'Obiective'],
  ['/parent/announcements', 'announce', 'Anunțuri'],
  ['/parent/conversations', 'chat', 'Conversații'],
];

$studentNav = [
  ['/student/dashboard', 'home', 'Acasă'],
  ['/student/timetable', 'clock', 'Orarul meu'],
  ['/student/assignments', 'document', 'Teme'],
  ['/student/materials', 'book', 'Materiale'],
  ['/student/results', 'result', 'Rezultate'],
  ['/student/feedback', 'spark', 'Aprecieri'],
  ['/student/goals', 'goal', 'Obiective'],
  ['/student/announcements', 'announce', 'Anunțuri'],
];

$navItems = $role === 'teacher' ? $teacherNav : ($role === 'parent' ? $parentNav : $studentNav);
$mobileItems = $role === 'teacher'
  ? [
      ['/teacher/students', 'groups', 'Elevi'],
      ['/teacher/groups', 'grid', 'Grupe'],
      ['/teacher/calendar', 'clock', 'Orar'],
      ['/teacher/attendance', 'attendance', 'Ședințe'],
      ['/teacher/assessments', 'result', 'Evaluări'],
    ]
  : ($role === 'parent'
      ? [
          ['/parent/dashboard', 'home', 'Acasă'],
          ['/parent/timetable', 'clock', 'Orar'],
          ['/parent/assignments', 'document', 'Teme'],
          ['/parent/conversations', 'chat', 'Chat'],
          ['/parent/results', 'result', 'Rezultate'],
        ]
      : [
          ['/student/dashboard', 'home', 'Acasă'],
          ['/student/timetable', 'clock', 'Orar'],
          ['/student/assignments', 'document', 'Teme'],
          ['/student/results', 'result', 'Rezultate'],
          ['/student/goals', 'goal', 'Obiective'],
        ]);
?>
<aside class="app-sidebar" id="app-sidebar" aria-label="Navigație principală">
  <div class="sidebar-top">
    <a class="sidebar-brand" href="/<?= e((string)$role) ?>/dashboard">
      <img src="/assets/images/app-icon.svg" alt="" class="sidebar-logo">
      <span class="sidebar-brand-copy">
        <span class="sidebar-brand-title">Îndrumar</span>
        <span class="sidebar-brand-subtitle">Spațiu educațional personal</span>
      </span>
    </a>

    <nav class="sidebar-nav">
      <?php foreach ($navItems as [$url, $iconName, $label]): ?>
        <?php $isActive = strpos($currentUri, $url) === 0; ?>
        <a href="<?= e($url) ?>" class="nav-link <?= $isActive ? 'is-active' : '' ?>" <?= $isActive ? 'aria-current="page"' : '' ?>>
          <span class="nav-icon"><?= $icon($iconName) ?></span>
          <span><?= e($label) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>

  <div class="sidebar-footer">
    <a href="/logout" class="nav-link nav-link--logout">
      <span class="nav-icon"><?= $icon('logout') ?></span>
      <span>Deconectare</span>
    </a>
  </div>
</aside>

<nav class="mobile-bottom-nav" aria-label="Navigație rapidă">
  <?php foreach ($mobileItems as [$url, $iconName, $label]): ?>
    <?php $isActive = strpos($currentUri, $url) === 0; ?>
    <a href="<?= e($url) ?>" class="mobile-nav-link <?= $isActive ? 'is-active' : '' ?>" <?= $isActive ? 'aria-current="page"' : '' ?>>
      <span class="mobile-nav-icon"><?= $icon($iconName) ?></span>
      <span><?= e($label) ?></span>
    </a>
  <?php endforeach; ?>
</nav>
