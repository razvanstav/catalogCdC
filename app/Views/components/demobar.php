<?php
$currentRole = \App\Support\Session::role();
$activeStudentId = \App\Support\Session::activeStudentId();
?>
<aside class="demo-bar" aria-label="Comutator conturi demonstrative">
  <div class="demo-bar-copy">
    <span class="demo-label">Mod demo</span>
    <span class="demo-note">Comută rapid între experiențele din aplicație</span>
  </div>

  <nav class="demo-bar-actions" aria-label="Alege un cont demonstrativ">
    <a href="/demo/teacher" class="demo-role <?= $currentRole === 'teacher' ? 'is-active' : '' ?>" <?= $currentRole === 'teacher' ? 'aria-current="true"' : '' ?>>Profesor</a>
    <a href="/demo/parent/stu_matei_popescu" class="demo-role <?= ($currentRole === 'parent' && $activeStudentId === 'stu_matei_popescu') ? 'is-active' : '' ?>" <?= ($currentRole === 'parent' && $activeStudentId === 'stu_matei_popescu') ? 'aria-current="true"' : '' ?>>Părinte Matei</a>
    <a href="/demo/parent/stu_sofia_popescu" class="demo-role <?= ($currentRole === 'parent' && $activeStudentId === 'stu_sofia_popescu') ? 'is-active' : '' ?>" <?= ($currentRole === 'parent' && $activeStudentId === 'stu_sofia_popescu') ? 'aria-current="true"' : '' ?>>Părinte Sofia</a>
    <a href="/demo/student" class="demo-role <?= $currentRole === 'student' ? 'is-active' : '' ?>" <?= $currentRole === 'student' ? 'aria-current="true"' : '' ?>>Elev</a>
  </nav>
</aside>
