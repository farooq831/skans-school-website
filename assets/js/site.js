// assets/js/site.js : main menu (phone panel + dropdowns). Nothing else.
(function () {
  var toggle = document.querySelector('.sk-menu-toggle');
  var nav = document.getElementById('sk-nav');
  var subToggles = document.querySelectorAll('.sk-nav__toggle');
  var desktop = window.matchMedia('(min-width: 1100px)');

  function closeSubs(except) {
    subToggles.forEach(function (btn) {
      if (btn !== except) btn.setAttribute('aria-expanded', 'false');
    });
  }

  function setMenu(open) {
    if (!toggle || !nav) return;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    nav.classList.toggle('is-open', open);
    document.body.classList.toggle('sk-menu-open', open);
  }

  if (toggle) {
    toggle.addEventListener('click', function () {
      setMenu(toggle.getAttribute('aria-expanded') !== 'true');
    });
  }

  subToggles.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var open = btn.getAttribute('aria-expanded') === 'true';
      if (desktop.matches) closeSubs(btn);
      btn.setAttribute('aria-expanded', open ? 'false' : 'true');
    });
  });

  // Desktop: clicking elsewhere closes an open dropdown.
  document.addEventListener('click', function (event) {
    if (desktop.matches && !event.target.closest('.sk-nav__item--has-sub')) closeSubs(null);
  });

  // Escape closes dropdowns and the phone menu.
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    closeSubs(null);
    if (toggle && toggle.getAttribute('aria-expanded') === 'true') {
      setMenu(false);
      toggle.focus();
    }
  });

  // Switching between phone and desktop width resets the menu.
  desktop.addEventListener('change', function () {
    setMenu(false);
    closeSubs(null);
  });
})();

// Faculty cards: the arrow button opens a details card over the photo (click, tap or keyboard).
(function () {
  function setOpen(btn, open) {
    var panel = document.getElementById(btn.getAttribute('aria-controls'));
    if (!panel) return;
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    panel.hidden = !open;
    var label = btn.querySelector('.sk-visually-hidden');
    if (label) label.textContent = label.textContent.replace(open ? 'View details' : 'Hide details', open ? 'Hide details' : 'View details');
  }
  document.addEventListener('click', function (event) {
    var toggle = event.target.closest('[data-teacher-toggle]');
    if (toggle) {
      setOpen(toggle, toggle.getAttribute('aria-expanded') !== 'true');
      return;
    }
    var close = event.target.closest('[data-teacher-close]');
    if (close) {
      var panel = close.closest('.sk-teacher__panel');
      var btn = document.querySelector('[aria-controls="' + panel.id + '"]');
      if (btn) { setOpen(btn, false); btn.focus(); }
    }
  });
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('[data-teacher-toggle][aria-expanded="true"]').forEach(function (btn) { setOpen(btn, false); });
  });
})();

// Home page announcement pop-up: opens shortly after load, once per visit for each announcement.
(function () {
  var dialog = document.getElementById('sk-ann-popup');
  if (!dialog) return;
  var key = dialog.getAttribute('data-key');
  var seen = false;
  try { seen = sessionStorage.getItem(key) === '1'; } catch (e) {}
  if (seen) return;
  var lastFocus = null;

  function remember() { try { sessionStorage.setItem(key, '1'); } catch (e) {} }
  function open() {
    lastFocus = document.activeElement;
    if (typeof dialog.showModal === 'function') dialog.showModal(); else dialog.setAttribute('open', '');
    document.body.classList.add('sk-popup-open');
    var close = dialog.querySelector('.sk-popup__close');
    if (close) close.focus();
  }
  function shut() {
    if (typeof dialog.close === 'function' && dialog.open) dialog.close(); else dialog.removeAttribute('open');
  }
  dialog.addEventListener('close', function () {
    remember();
    document.body.classList.remove('sk-popup-open');
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  });
  dialog.querySelectorAll('[data-popup-close]').forEach(function (b) { b.addEventListener('click', shut); });
  // A click on the dark area outside the box closes it.
  dialog.addEventListener('click', function (event) { if (event.target === dialog) shut(); });
  // Opening the full announcement also counts as "seen".
  dialog.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', remember); });

  window.addEventListener('load', function () { setTimeout(open, 700); });
})();

// Fade-up on scroll: elements with class "sk-reveal" appear when they come into view (once).
(function () {
  var items = document.querySelectorAll('.sk-reveal');
  if (!items.length || !('IntersectionObserver' in window)) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  document.documentElement.classList.add('sk-reveal-on');
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
    });
  }, { threshold: 0.2 });
  items.forEach(function (el) { io.observe(el); });
})();

/* Long text in Times New Roman (7 Oct 2026): any paragraph or list point on the page that runs
   to MORE than 3 lines gets the class "sk-long" (style in site.css). Short text (labels, dates,
   buttons, one-line notes) keeps the normal font. Measured again when the window is resized and
   when hidden text appears ("Show more", "Read more"). */
(function () {
    var MIN_LINES = 4; // "more than 3 lines"
    var root = document.getElementById('main');
    if (!root) return;
    var SELECTOR = 'p, li, dd, blockquote';
    var SKIP = 'form, button, .sk-btn, nav, .sk-filter, .sk-level__classes, .sk-chip, .sk-badge, .sk-date, .sk-eyebrow, .sk-subnav, .sk-crumbs';

    function lines(el) {
        var cs = getComputedStyle(el);
        var lh = parseFloat(cs.lineHeight);
        if (isNaN(lh)) lh = parseFloat(cs.fontSize) * 1.2; // "normal"
        var h = el.getBoundingClientRect().height - parseFloat(cs.paddingTop) - parseFloat(cs.paddingBottom);
        return h > 0 ? Math.round(h / lh) : 0;
    }
    function measure() {
        var els = root.querySelectorAll(SELECTOR);
        var todo = [];
        els.forEach(function (el) {
            if (el.closest(SKIP) || el.querySelector('p, li, ul, ol')) return; // only the innermost text block
            todo.push(el);
        });
        // Measure everything in the normal font first, then switch (no layout ping-pong).
        todo.forEach(function (el) { el.classList.remove('sk-long'); });
        var long = todo.filter(function (el) {
            if (el.offsetParent === null) return false;                           // hidden right now
            if (/times/i.test(getComputedStyle(el).fontFamily)) return false;     // already Times New Roman (own size)
            return lines(el) >= MIN_LINES;
        });
        long.forEach(function (el) { el.classList.add('sk-long'); });
    }
    var timer;
    function later() { clearTimeout(timer); timer = setTimeout(measure, 120); }

    measure();
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(measure);
    window.addEventListener('load', measure);
    var lastW = window.innerWidth;
    window.addEventListener('resize', function () { if (window.innerWidth !== lastW) { lastW = window.innerWidth; later(); } });
    document.addEventListener('toggle', later, true); // <details> "Read more" opened
    if ('MutationObserver' in window) {
        new MutationObserver(later).observe(root, { subtree: true, attributes: true, attributeFilter: ['hidden', 'open'] });
    }
})();
