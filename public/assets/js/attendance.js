/** Îndrumar — fast attendance controls. */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-attendance-row]').forEach(row => {
      row.querySelectorAll('[data-attendance-option] input[type="radio"]').forEach(input => {
        input.addEventListener('change', () => updateRow(row, input));
      });
    });

    document.querySelectorAll('[data-mark-all-present]').forEach(button => {
      button.addEventListener('click', () => {
        document.querySelectorAll('[data-attendance-row]').forEach(row => {
          const input = row.querySelector('input[type="radio"][value="present"]');
          if (!input) return;
          input.checked = true;
          updateRow(row, input);
        });
      });
    });
  });

  function updateRow(row, selectedInput) {
    row.querySelectorAll('[data-attendance-option]').forEach(option => option.classList.remove('is-selected'));
    selectedInput.closest('[data-attendance-option]')?.classList.add('is-selected');
  }
})();
