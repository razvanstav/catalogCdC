<?php
$title = 'Conversații cu părinții';
$allGuardians = $allGuardians ?? [];
?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--brand">Mesagerie directă</span><span class="badge badge--neutral"><?= count($conversations) ?> conversații</span></div>
    <h1 class="page-title">Conversații cu părinții</h1>
    <p class="page-subtitle">Un fir clar pentru fiecare familie, căutare instantanee și comunicare directă.</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn--primary" data-modal-open="modal-new-message">＋ Mesaj nou / Caută părinte</button>
  </div>
</header>

<section class="chat-layout">
  <aside class="card thread-panel" aria-label="Lista conversațiilor">
    <div class="card-header">
      <div class="card-header__copy"><h2 class="card-title">Familii</h2><p class="card-description">Alege sau caută conversația</p></div>
    </div>
    <div class="thread-search-box">
      <label for="search-threads" class="sr-only">Caută părinte sau elev</label>
      <input type="search" id="search-threads" class="form-control form-control--sm" placeholder="🔍 Caută părinte sau elev..." data-live-filter=".thread-list .thread-item" autocomplete="off">
    </div>
    <nav class="thread-list">
      <?php foreach ($conversations as $conversation): ?>
        <?php
        $isSelected = $activeConv && $activeConv['id'] === $conversation['id'];
        $messages = $conversation['messages'] ?? [];
        $lastMessage = $messages ? $messages[count($messages) - 1] : null;
        ?>
        <a href="/teacher/conversations?conv_id=<?= e($conversation['id']) ?>" class="thread-item <?= $isSelected ? 'is-active' : '' ?>" <?= $isSelected ? 'aria-current="page"' : '' ?>>
          <span class="avatar avatar--sm <?= e(ui_tone_class($conversation['id'])) ?>" aria-hidden="true"><?= e(initials($conversation['guardian_first_name'], $conversation['guardian_last_name'])) ?></span>
          <span class="thread-item__copy">
            <span class="thread-item__name"><?= e($conversation['guardian_first_name'] . ' ' . $conversation['guardian_last_name']) ?></span>
            <span class="thread-item__preview"><?= !empty($conversation['student_first_name']) ? e('Părinte ' . $conversation['student_first_name']) . ' • ' : '' ?><?= e($lastMessage['content'] ?? 'Începe conversația') ?></span>
          </span>
        </a>
      <?php endforeach; ?>
      <?php if (empty($conversations)): ?><div class="empty-state"><div><p class="empty-state__text">Nu există încă fire de conversație.</p></div></div><?php endif; ?>
    </nav>
  </aside>

  <div class="chat-window">
    <?php if ($activeConv): ?>
      <header class="chat-header">
        <div class="chat-person">
          <span class="avatar avatar--brand" aria-hidden="true"><?= e(initials($activeConv['guardian_first_name'], $activeConv['guardian_last_name'])) ?></span>
          <div class="chat-person__copy">
            <div class="chat-person__name"><?= e($activeConv['guardian_first_name'] . ' ' . $activeConv['guardian_last_name']) ?></div>
            <div class="chat-person__meta"><?= !empty($activeConv['student_first_name']) ? e('Părinte pentru ' . $activeConv['student_first_name'] . ' ' . $activeConv['student_last_name']) . ' • ' : '' ?><?= e($activeConv['guardian_phone'] ?: 'Telefon nespecificat') ?></div>
          </div>
        </div>
        <span class="badge badge--brand">Canal privat</span>
      </header>

      <div class="chat-messages" data-chat-messages>
        <div class="chat-date">Istoric conversație</div>
        <?php foreach ($activeConv['messages'] as $message): ?>
          <?php $isMine = $message['sender_role'] === 'teacher'; ?>
          <div class="chat-bubble <?= $isMine ? 'chat-bubble--mine' : 'chat-bubble--other' ?>">
            <div class="chat-bubble__meta"><?= $isMine ? 'Tu' : e($activeConv['guardian_first_name']) ?> • <?= format_datetime_ro($message['sent_at']) ?></div>
            <div><?= e($message['content']) ?></div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($activeConv['messages'])): ?><div class="empty-state"><div><div class="empty-state__icon">✦</div><div class="empty-state__title">Începe conversația</div><p class="empty-state__text">Scrie un mesaj scurt, clar și calm.</p></div></div><?php endif; ?>
      </div>

      <form action="/teacher/conversations/message" method="POST" class="chat-form">
        <?= csrf_field() ?>
        <input type="hidden" name="conversation_id" value="<?= e($activeConv['id']) ?>">
        <label class="sr-only" for="teacher-message">Mesaj</label>
        <div class="chat-composer"><input id="teacher-message" type="text" name="content" placeholder="Scrie un răspuns..." autocomplete="off" required><button type="submit" class="btn btn--primary chat-send" aria-label="Trimite mesaj">➜</button></div>
      </form>
    <?php else: ?>
      <div class="empty-state empty-state--fill"><div><div class="empty-state__icon">◌</div><div class="empty-state__title">Selectează o conversație</div><p class="empty-state__text">Mesajele vor apărea aici.</p></div></div>
    <?php endif; ?>
  </div>
</section>

<!-- Modal Mesaj Nou Către Părinte -->
<div id="modal-new-message" class="modal-backdrop" aria-hidden="true">
  <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="new-msg-title">
    <h2 class="modal-title" id="new-msg-title">Mesaj nou către părinte</h2>
    <p class="modal-description">Alege familia din listă sau caută după numele părintelui / elevului.</p>
    <form action="/teacher/conversations/start" method="POST" class="form-stack">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label" for="select-guardian">Părinte / Familie</label>
        <select id="select-guardian" name="guardian_id" class="form-control" required>
          <option value="">-- Alege părintele --</option>
          <?php foreach ($allGuardians as $g): ?>
            <option value="<?= e($g['guardian_id']) ?>" data-student="<?= e($g['student_id'] ?? '') ?>">
              <?= e($g['guardian_first_name'] . ' ' . $g['guardian_last_name']) ?>
              <?= !empty($g['student_first_name']) ? ' (Părinte ' . e($g['student_first_name'] . ' ' . $g['student_last_name']) . ')' : '' ?>
              <?= !empty($g['guardian_phone']) ? ' • ' . e($g['guardian_phone']) : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="new-msg-content">Mesajul tău</label>
        <textarea id="new-msg-content" name="content" class="form-control" placeholder="Scrie aici mesajul..." required></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn--ghost" data-modal-close>Renunță</button>
        <button type="submit" class="btn btn--primary">Trimite mesajul</button>
      </div>
    </form>
  </section>
</div>
