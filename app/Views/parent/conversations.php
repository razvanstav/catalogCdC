<?php $title = 'Conversații'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--sage">Canal direct</span></div>
    <h1 class="page-title">Dialog cu profesorul</h1>
    <p class="page-subtitle">Mesaje clare, păstrate într-un singur fir, pentru întrebări despre program, teme și progres.</p>
  </div>
</header>

<section class="chat-window">
  <?php if ($activeConv): ?>
    <header class="chat-header">
      <div class="chat-person">
        <span class="avatar avatar--brand" aria-hidden="true"><?= e(initials($activeConv['teacher_first_name'], $activeConv['teacher_last_name'])) ?></span>
        <div class="chat-person__copy">
          <div class="chat-person__name">Prof. <?= e($activeConv['teacher_first_name'] . ' ' . $activeConv['teacher_last_name']) ?></div>
          <div class="chat-person__meta"><?= e($activeConv['teacher_title']) ?><?= !empty($activeConv['teacher_phone']) ? ' • ' . e($activeConv['teacher_phone']) : '' ?></div>
        </div>
      </div>
      <span class="badge badge--sage">Activ</span>
    </header>

    <div class="chat-messages">
      <div class="chat-date">Conversație</div>
      <?php foreach ($activeConv['messages'] as $message): ?>
        <?php $isMine = $message['sender_role'] === 'parent'; ?>
        <div class="chat-bubble <?= $isMine ? 'chat-bubble--mine' : 'chat-bubble--other' ?>">
          <div class="chat-bubble__meta"><?= $isMine ? 'Tu' : 'Profesor' ?> • <?= format_datetime_ro($message['sent_at']) ?></div>
          <div><?= e($message['content']) ?></div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($activeConv['messages'])): ?>
        <div class="empty-state">
          <div>
            <div class="empty-state__icon" aria-hidden="true">💬</div>
            <div class="empty-state__title">Începe conversația</div>
            <p class="empty-state__text">Poți trimite o întrebare scurtă și clară profesorului.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <form action="/parent/conversations/message" method="POST" class="chat-form">
      <?= csrf_field() ?>
      <input type="hidden" name="conversation_id" value="<?= e($activeConv['id']) ?>">
      <label class="chat-composer">
        <span class="sr-only">Mesaj către profesor</span>
        <input type="text" name="content" placeholder="Scrie un mesaj..." autocomplete="off" required>
        <button type="submit" class="btn btn--primary chat-send" aria-label="Trimite mesaj">➜</button>
      </label>
    </form>
  <?php else: ?>
    <div class="empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">💬</div>
        <div class="empty-state__title">Nicio conversație activă</div>
        <p class="empty-state__text">Canalul cu profesorul va apărea după asocierea contului.</p>
      </div>
    </div>
  <?php endif; ?>
</section>
