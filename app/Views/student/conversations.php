<?php $title = 'Conversații'; ?>
<header class="page-heading">
  <div class="page-heading__copy">
    <div class="page-kicker"><span class="badge badge--sage">Dialog direct</span></div>
    <h1 class="page-title">Mesaje cu profesorul</h1>
    <p class="page-subtitle">Poți pune întrebări despre teme, proiecte sau orar direct cadrului didactic.</p>
  </div>
</header>

<section class="chat-window">
  <?php if ($activeConv): ?>
    <header class="chat-header">
      <div class="chat-person">
        <span class="avatar avatar--brand" aria-hidden="true"><?= e(initials($activeConv['teacher_first_name'], $activeConv['teacher_last_name'])) ?></span>
        <div class="chat-person__copy">
          <div class="chat-person__name">Prof. <?= e($activeConv['teacher_first_name'] . ' ' . $activeConv['teacher_last_name']) ?></div>
          <div class="chat-person__meta"><?= e($activeConv['teacher_title']) ?></div>
        </div>
      </div>
      <span class="badge badge--sage">Canal deschis</span>
    </header>

    <div class="chat-messages">
      <div class="chat-date">Fir de discuție</div>
      <?php foreach ($activeConv['messages'] as $message): ?>
        <?php $isMine = $message['sender_role'] === 'student'; ?>
        <div class="chat-bubble <?= $isMine ? 'chat-bubble--mine' : 'chat-bubble--other' ?>">
          <div class="chat-bubble__meta"><?= $isMine ? 'Tu' : 'Profesor' ?> • <?= format_datetime_ro($message['sent_at']) ?></div>
          <div><?= e($message['content']) ?></div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($activeConv['messages'])): ?>
        <div class="empty-state">
          <div>
            <div class="empty-state__icon" aria-hidden="true">💬</div>
            <div class="empty-state__title">Scrie-i profesorului</div>
            <p class="empty-state__text">Dacă ai o nelămurire legată de o temă sau o problemă, trimite un mesaj aici.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <form action="/student/conversations/message" method="POST" class="chat-form">
      <?= csrf_field() ?>
      <input type="hidden" name="conversation_id" value="<?= e($activeConv['id']) ?>">
      <label class="chat-composer">
        <span class="sr-only">Mesaj către profesor</span>
        <input type="text" name="content" placeholder="Scrie mesajul tău..." autocomplete="off" required>
        <button type="submit" class="btn btn--primary chat-send" aria-label="Trimite mesaj">➜</button>
      </label>
    </form>
  <?php else: ?>
    <div class="empty-state">
      <div>
        <div class="empty-state__icon" aria-hidden="true">💬</div>
        <div class="empty-state__title">Nicio conversație disponibilă</div>
        <p class="empty-state__text">Profesorul va deschide canalul de comunicare odată cu alocarea grupei.</p>
      </div>
    </div>
  <?php endif; ?>
</section>
