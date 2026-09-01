/**
 * Îndrumar — progressive enhancement, without a frontend framework.
 */
(() => {
  'use strict';

  const focusableSelector = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])'
  ].join(',');

  let lastFocusedElement = null;

  document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initModals();
    initTabs();
    initAutoSubmit();
    initFeedbackActions();
    initReloadActions();
    initChatScroll();
    initQuickFeedback();
    initRatingButtons();
    initLiveFilter();
    registerServiceWorker();
  });

  function initLiveFilter() {
    document.querySelectorAll('[data-live-filter]').forEach(input => {
      const targetSelector = input.getAttribute('data-live-filter');
      input.addEventListener('input', () => {
        const query = input.value.toLowerCase().trim();
        document.querySelectorAll(targetSelector).forEach(item => {
          const text = (item.textContent || '').toLowerCase();
          item.hidden = (query !== '' && !text.includes(query));
        });
      });
    });
  }

  function initQuickFeedback() {
    document.querySelectorAll('[data-insert-feedback]').forEach(chip => {
      chip.addEventListener('click', () => {
        const targetId = chip.getAttribute('data-target');
        const text = chip.getAttribute('data-insert-feedback');
        const target = document.getElementById(targetId);
        if (!target) return;
        if (target.value && target.value.trim().length > 0) {
          target.value = target.value.trim() + ' ' + text;
        } else {
          target.value = text;
        }
        target.focus();
      });
    });
  }

  function initRatingButtons() {
    document.querySelectorAll('[data-rating-group]').forEach(group => {
      const input = group.querySelector('input[type="number"], input[type="hidden"]');
      group.querySelectorAll('[data-rating-value]').forEach(btn => {
        btn.addEventListener('click', () => {
          const val = btn.getAttribute('data-rating-value');
          if (input) input.value = val;
          group.querySelectorAll('[data-rating-value]').forEach(b => {
            const bVal = parseInt(b.getAttribute('data-rating-value'), 10);
            b.classList.toggle('is-active', bVal <= parseInt(val, 10));
          });
        });
      });
    });
  }

  function initSidebar() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.app-sidebar');
    const scrim = document.querySelector('.sidebar-scrim');
    if (!toggle || !sidebar) return;

    const open = () => {
      sidebar.classList.add('is-open');
      scrim?.classList.add('is-open');
      document.body.classList.add('nav-open');
      toggle.setAttribute('aria-expanded', 'true');
      window.setTimeout(() => sidebar.querySelector('a, button')?.focus(), 80);
    };

    const close = (restoreFocus = true) => {
      sidebar.classList.remove('is-open');
      scrim?.classList.remove('is-open');
      document.body.classList.remove('nav-open');
      toggle.setAttribute('aria-expanded', 'false');
      if (restoreFocus) toggle.focus();
    };

    toggle.addEventListener('click', () => {
      sidebar.classList.contains('is-open') ? close() : open();
    });
    scrim?.addEventListener('click', () => close());
    sidebar.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 64rem)').matches) close(false);
      });
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && sidebar.classList.contains('is-open')) close();
    });
    window.addEventListener('resize', () => {
      if (!window.matchMedia('(max-width: 64rem)').matches && sidebar.classList.contains('is-open')) close(false);
    });
  }

  function initModals() {
    document.querySelectorAll('[data-modal-open]').forEach(trigger => {
      trigger.addEventListener('click', () => openModal(trigger.getAttribute('data-modal-open')));
    });

    document.querySelectorAll('[data-modal-close]').forEach(trigger => {
      trigger.addEventListener('click', () => closeModal(trigger.closest('.modal-backdrop')));
    });

    document.querySelectorAll('.modal-backdrop').forEach(modal => {
      modal.setAttribute('aria-hidden', modal.classList.contains('is-open') ? 'false' : 'true');
      modal.addEventListener('click', event => {
        if (event.target === modal) closeModal(modal);
      });
    });

    document.addEventListener('keydown', event => {
      const activeModal = document.querySelector('.modal-backdrop.is-open');
      if (!activeModal) return;
      if (event.key === 'Escape') {
        event.preventDefault();
        closeModal(activeModal);
      } else if (event.key === 'Tab') {
        trapFocus(event, activeModal);
      }
    });
  }

  function openModal(id) {
    const modal = typeof id === 'string' ? document.getElementById(id) : id;
    if (!modal) return;
    lastFocusedElement = document.activeElement;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    const target = modal.querySelector('[autofocus], input:not([type="hidden"]), select, textarea, button, a[href], .modal-card');
    window.requestAnimationFrame(() => target?.focus());
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.modal-backdrop.is-open')) document.body.classList.remove('modal-open');
    if (lastFocusedElement instanceof HTMLElement) lastFocusedElement.focus();
  }

  function trapFocus(event, container) {
    const items = [...container.querySelectorAll(focusableSelector)].filter(item => item.offsetParent !== null);
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  function initTabs() {
    document.querySelectorAll('[data-tab-group]').forEach((tabList, groupIndex) => {
      const groupName = tabList.getAttribute('data-tab-group');
      const tabs = [...tabList.querySelectorAll('[data-tab-target]')];
      const panels = [...document.querySelectorAll(`[data-tab-panel="${groupName}"]`)];
      if (!tabs.length) return;

      tabList.setAttribute('role', 'tablist');
      tabs.forEach((tab, index) => {
        const targetId = tab.getAttribute('data-tab-target');
        const panel = document.getElementById(targetId);
        const selected = tab.classList.contains('is-active') || (!tabs.some(item => item.classList.contains('is-active')) && index === 0);
        const id = tab.id || `tab-${groupIndex}-${index}`;
        tab.id = id;
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-controls', targetId || '');
        tab.setAttribute('aria-selected', selected ? 'true' : 'false');
        tab.tabIndex = selected ? 0 : -1;
        if (panel) {
          panel.setAttribute('role', 'tabpanel');
          panel.setAttribute('aria-labelledby', id);
          panel.hidden = !selected;
        }

        tab.addEventListener('click', () => activateTab(tab, tabs, panels));
        tab.addEventListener('keydown', event => {
          let next = index;
          if (event.key === 'ArrowRight' || event.key === 'ArrowDown') next = (index + 1) % tabs.length;
          else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') next = (index - 1 + tabs.length) % tabs.length;
          else if (event.key === 'Home') next = 0;
          else if (event.key === 'End') next = tabs.length - 1;
          else return;
          event.preventDefault();
          tabs[next].focus();
          activateTab(tabs[next], tabs, panels);
        });
      });
    });
  }

  function activateTab(active, tabs, panels) {
    const target = active.getAttribute('data-tab-target');
    tabs.forEach(tab => {
      const selected = tab === active;
      tab.classList.toggle('is-active', selected);
      tab.setAttribute('aria-selected', selected ? 'true' : 'false');
      tab.tabIndex = selected ? 0 : -1;
    });
    panels.forEach(panel => { panel.hidden = panel.id !== target; });
  }

  function initAutoSubmit() {
    document.querySelectorAll('[data-submit-on-change]').forEach(control => {
      control.addEventListener('change', () => control.form?.requestSubmit());
    });
  }

  function initFeedbackActions() {
    document.querySelectorAll('[data-feedback-message]').forEach(button => {
      button.addEventListener('click', () => {
        const message = button.getAttribute('data-feedback-message') || 'Acțiunea a fost înregistrată.';
        const live = getLiveRegion();
        live.textContent = message;
        button.textContent = 'Transmis ✓';
        button.disabled = true;
      });
    });
  }

  function initReloadActions() {
    document.querySelectorAll('[data-reload-page]').forEach(button => {
      button.addEventListener('click', () => window.location.reload());
    });
  }

  function initChatScroll() {
    document.querySelectorAll('[data-chat-messages]').forEach(container => {
      container.scrollTop = container.scrollHeight;
    });
  }

  function getLiveRegion() {
    let region = document.getElementById('app-live-region');
    if (!region) {
      region = document.createElement('div');
      region.id = 'app-live-region';
      region.className = 'sr-only';
      region.setAttribute('role', 'status');
      region.setAttribute('aria-live', 'polite');
      document.body.appendChild(region);
    }
    return region;
  }

  async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) return;
    try {
      const registration = await navigator.serviceWorker.register('/service-worker.js?v=5', { updateViaCache: 'none' });
      registration.update();
    } catch (error) {
      console.info('Service worker indisponibil în acest mediu.', error);
    }
  }

  // Compatibility for any extension that still calls these global helpers.
  window.openModal = openModal;
  window.closeModal = id => closeModal(typeof id === 'string' ? document.getElementById(id) : id);
})();
