/**
 * Kost.io UI unifier. Only the visual navigation and top bar are normalized.
 * Page-specific forms, tables, actions, routes and JS behavior are untouched.
 */
(() => {
  'use strict';
  function init() {
    const template = document.getElementById('kostio-ui-shell-template');
    if (!(template instanceof HTMLTemplateElement)) return;

    const sidebar = document.querySelector([
      'aside.rp-sidebar',
      'aside.owner14-sidebar',
      'aside.finance-sidebar',
      'aside.sb',
      'aside.sidebar'
    ].join(', '));

    const header = document.querySelector([
      'header.rp-topbar',
      'header.owner14-topbar',
      'header.finance-topbar',
      'header.topbar',
      'header.top',
      'div.header'
    ].join(', '));

    if (!sidebar || !header) return;

    const navMarkup = template.content.querySelector('[data-kio-sidebar-content]');
    const headerMarkup = template.content.querySelector('[data-kio-header-content]');
    if (!navMarkup || !headerMarkup) return;

    // Make DOM operations only after all elements are available.
    // Save original chrome for simple debugging / rollback in DevTools.
    sidebar.dataset.kioPreviousClass = sidebar.className;
    header.dataset.kioPreviousClass = header.className;
    sidebar.replaceChildren(navMarkup.cloneNode(true));
    header.replaceChildren(headerMarkup.cloneNode(true));
    sidebar.classList.add('kio-sidebar');
    header.classList.add('kio-header');
    document.body.classList.add('kio-ready');
    template.remove();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
