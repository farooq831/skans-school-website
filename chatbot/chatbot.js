/* SKANS chatbot: buttons, links and fixed answers (no AI, no server code).
 * Add to any page, before </body>:
 *   <script src="https://skansschool.edu.pk/chatbot/chatbot.js?v=2" defer></script>
 * Optional attributes on that tag:
 *   data-campus="golra"      force a campus (normally found automatically from the web address)
 *   data-position="right"    put the button on the right (default: left, so it never covers WhatsApp)
 *   data-container="#id"     show the chat inside an element instead of the page corner (demo pages)
 *   data-open="true"         open the chat as soon as the page loads (demo pages)
 * Data: campuses.js and faq.js in the same folder. Edit those, not this file.
 */
(function () {
  'use strict';
  if (window.SKANS_CHAT_LOADED) return;
  window.SKANS_CHAT_LOADED = true;

  var me = document.currentScript || (function () {
    var s = document.querySelectorAll('script[src*="chatbot.js"]'); return s[s.length - 1] || document.createElement('script');
  })();
  var src = me.src || '';
  var base = src.replace(/[^\/]*$/, '');                     // folder of chatbot.js
  var ver = (src.split('?')[1] || '');
  var forced = me.getAttribute('data-campus') || '';
  var side = me.getAttribute('data-position') === 'right' ? 'right' : 'left';
  var container = me.getAttribute('data-container') ? document.querySelector(me.getAttribute('data-container')) : null;
  var autoOpen = me.getAttribute('data-open') === 'true';
  var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var TOPICS = [
    { key: 'fees',       label: 'Fee Structure',               icon: '💰', page: 'fee-structure.php' },
    { key: 'admissions', label: 'Admissions & age guide',      icon: '📝', page: 'admissions.php' },
    { key: 'policy',     label: 'Admission Policy',            icon: '📋', page: 'admission-policy.php' },
    { key: 'programs',   label: 'Programs & classes',          icon: '🎓', page: 'departments.php' },
    { key: 'special',    label: 'Special Programs',            icon: '🤖', page: 'advanced-placement-program.php' },
    { key: 'facilities', label: 'Facilities',                  icon: '🏫', page: 'services.php' },
    { key: 'faculty',    label: 'Faculty',                     icon: '👩‍🏫', page: 'faculty.php' },
    { key: 'news',       label: 'News & Events',               icon: '📰', page: 'news-events.php' },
    { key: 'about',      label: 'About & Principal’s message', icon: '🏛️', page: 'about.php' },
    { key: 'contact',    label: 'Location & contact',          icon: '📍', page: 'contact-us.php' },
    { key: 'policies',   label: 'SKANS Policies',              icon: '📄', page: 'policies.php' }
  ];
  var MENU = ['fees', 'admissions', 'programs', 'facilities', 'faculty', 'news', 'contact'];   // topic cards

  /* ---------- load style + data (skipped when the page already has them, e.g. a one-file demo) ---------- */
  function load(file, done) {
    var s = document.createElement('script');
    s.src = base + file + (ver ? '?' + ver : ''); s.async = true;
    s.onload = done; s.onerror = done;
    document.head.appendChild(s);
  }
  if (!document.querySelector('style[data-skc], link[data-skc]') && base) {
    var css = document.createElement('link');
    css.rel = 'stylesheet'; css.href = base + 'chatbot.css' + (ver ? '?' + ver : ''); css.setAttribute('data-skc', '');
    document.head.appendChild(css);
  }
  var pending = 1;
  function ready() { if (--pending === 0) start(); }
  if (!window.SKANS_CHAT_CAMPUSES && base) { pending++; load('campuses.js', ready); }
  if (!window.SKANS_CHAT_FAQ && base) { pending++; load('faq.js', ready); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready); else setTimeout(ready, 0);

  /* ---------- helpers ---------- */
  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text != null) e.textContent = text;
    return e;
  }
  function store(k, v, session) {
    try { var s = session ? sessionStorage : localStorage; if (v === undefined) return s.getItem(k); s.setItem(k, v); } catch (e) { return null; }
  }
  function host(u) { try { return new URL(u, location.href).hostname.replace(/^www\./, '').toLowerCase(); } catch (e) { return ''; } }
  function topic(key) { for (var i = 0; i < TOPICS.length; i++) if (TOPICS[i].key === key) return TOPICS[i]; return null; }
  function pageUrl(c, key) {
    var t = topic(key); if (!c || !t) return '';
    return c.url.replace(/\/?$/, '/') + ((c.pages && c.pages[key]) || t.page);
  }
  var AVATAR = '<svg viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="19" fill="#192A4A"/><circle cx="20" cy="20" r="16.5" fill="none" stroke="#BD852C" stroke-width="1.6"/><text x="20" y="26.5" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="18" font-weight="700" fill="#fff">S</text></svg>';
  var SEND = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.4 20.4 21 12 3.4 3.6 3.4 10l12.6 2-12.6 2z"/></svg>';

  var campuses = [], faq = [], campus = null, onCampusSite = false;
  var root, launcher, nudge, box, log, input, opened = false;

  function start() {
    campuses = window.SKANS_CHAT_CAMPUSES || [];
    faq = window.SKANS_CHAT_FAQ || [];
    if (!campuses.length || !document.body) return;          // data missing: show nothing rather than a broken bot
    var here = host(location.href);
    campus = find(forced) || campuses.filter(function (c) { return host(c.url) === here; })[0] || null;
    onCampusSite = !!campus;
    if (!campus) campus = find(store('skans_chat_campus'));
    build();
    if (autoOpen) toggle(true);
    else if (store('skans_chat_hello', undefined, true) !== 'seen') setTimeout(showNudge, 2500);
  }
  function find(id) { for (var i = 0; i < campuses.length; i++) if (campuses[i].id === id) return campuses[i]; return null; }

  /* ---------- layout ---------- */
  function build() {
    root = el('div', 'skc-root skc-' + side + (container ? ' skc-contained' : ''));

    launcher = el('button', 'skc-launch');
    launcher.type = 'button';
    launcher.setAttribute('aria-label', 'Open SKANS help chat');
    launcher.setAttribute('aria-expanded', 'false');
    launcher.innerHTML = '<span class="skc-launch-face">' + AVATAR + '</span><span class="skc-wave" aria-hidden="true">👋</span>';

    nudge = el('div', 'skc-nudge'); nudge.hidden = true; nudge.setAttribute('role', 'status');
    var nudgeText = el('button', 'skc-nudge-text'); nudgeText.type = 'button';
    nudgeText.appendChild(el('strong', null, 'Hi there! 👋'));
    nudgeText.appendChild(el('span', null, onCampusSite ? 'Questions about ' + campus.name + '? Ask me.' : 'Need help with admissions or fees?'));
    var nudgeClose = el('button', 'skc-nudge-x', '×'); nudgeClose.type = 'button'; nudgeClose.setAttribute('aria-label', 'Dismiss');
    nudge.appendChild(nudgeText); nudge.appendChild(nudgeClose);

    box = el('section', 'skc-box');
    box.setAttribute('role', 'dialog'); box.setAttribute('aria-label', 'SKANS help'); box.hidden = true;
    var head = el('header', 'skc-head');
    var who = el('div', 'skc-who');
    var av = el('span', 'skc-avatar'); av.innerHTML = AVATAR;
    var title = el('div', 'skc-title');
    title.appendChild(el('strong', null, 'SKANS Help'));
    var sub = el('span', 'skc-sub'); sub.appendChild(el('i', 'skc-dot')); sub.appendChild(document.createTextNode('Instant answers · Mon–Sat'));
    title.appendChild(sub);
    who.appendChild(av); who.appendChild(title);
    var close = el('button', 'skc-close'); close.type = 'button'; close.setAttribute('aria-label', 'Close chat');
    close.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';
    head.appendChild(who); head.appendChild(close);

    log = el('div', 'skc-log'); log.setAttribute('aria-live', 'polite');
    var form = el('form', 'skc-form');
    input = el('input'); input.type = 'text'; input.id = 'skc-question'; input.placeholder = 'Type a question, e.g. fees';
    input.setAttribute('aria-label', 'Your question'); input.maxLength = 120; input.autocomplete = 'off';
    var send = el('button', 'skc-send'); send.type = 'submit'; send.setAttribute('aria-label', 'Send'); send.innerHTML = SEND;
    form.appendChild(input); form.appendChild(send);
    var foot = el('p', 'skc-foot', 'SKANS School System · answers from our websites');
    box.appendChild(head); box.appendChild(log); box.appendChild(form); box.appendChild(foot);

    root.appendChild(nudge); root.appendChild(launcher); root.appendChild(box);
    (container || document.body).appendChild(root);

    launcher.addEventListener('click', function () { toggle(true); });
    nudgeText.addEventListener('click', function () { toggle(true); });
    nudgeClose.addEventListener('click', function () { hideNudge(); });
    close.addEventListener('click', function () { toggle(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && opened) toggle(false); });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var q = input.value.trim(); if (!q) return;
      input.value = ''; user(q); search(q);
    });
  }
  function showNudge() { if (!opened && nudge) { nudge.hidden = false; requestAnimationFrame(function () { nudge.classList.add('skc-in'); }); } }
  function hideNudge() { if (nudge) { nudge.hidden = true; nudge.classList.remove('skc-in'); } store('skans_chat_hello', 'seen', true); }
  function toggle(open) {
    opened = open;
    box.hidden = !open; launcher.hidden = open;
    launcher.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      hideNudge();
      if (!log.childNodes.length) welcome();
      setTimeout(function () { try { input.focus({ preventScroll: true }); } catch (e) {} }, 60);
    } else { try { launcher.focus({ preventScroll: true }); } catch (e) {} }
  }

  /* ---------- message queue: a short "typing" pause before each reply ---------- */
  var queue = [], busy = false, gen = 0;
  function push(job) { queue.push(job); if (!busy) next(); }
  function next() {
    var job = queue.shift(); if (!job) { busy = false; return; }
    busy = true;
    if (job.typing && !calm) {
      var t = typingRow(), g = gen; log.appendChild(t); scroll();
      setTimeout(function () { if (g !== gen) return; t.remove(); job.run(); scroll(); next(); }, job.wait || 420);
    } else { job.run(); scroll(); next(); }
  }
  function typingRow() {
    var r = el('div', 'skc-row skc-row-bot');
    var a = el('span', 'skc-mini'); a.innerHTML = AVATAR;
    var b = el('div', 'skc-msg skc-bot skc-typing'); b.setAttribute('aria-label', 'typing');
    b.appendChild(el('i')); b.appendChild(el('i')); b.appendChild(el('i'));
    r.appendChild(a); r.appendChild(b); return r;
  }
  function scroll() { log.scrollTop = log.scrollHeight; }

  function user(text) {
    push({ run: function () { var r = el('div', 'skc-row skc-row-user'); r.appendChild(el('div', 'skc-msg skc-user', text)); log.appendChild(r); } });
  }
  /* bot(text, buttons, links, style): style "cards" = topic grid, "list" = full-width rows */
  function bot(text, buttons, links, style) {
    push({ typing: true, run: function () {
      var r = el('div', 'skc-row skc-row-bot');
      var a = el('span', 'skc-mini'); a.innerHTML = AVATAR;
      var m = el('div', 'skc-msg skc-bot');
      String(text).split('\n').forEach(function (line, i) { if (i) m.appendChild(el('br')); m.appendChild(document.createTextNode(line)); });
      r.appendChild(a); r.appendChild(m); log.appendChild(r);
      if ((links && links.length) || (buttons && buttons.length)) {
        var row = el('div', 'skc-actions' + (style ? ' skc-' + style : ''));
        (links || []).forEach(function (l) {
          if (!l || !l.href) return;
          var x = el('a', 'skc-link' + (l.cls ? ' ' + l.cls : ''));
          if (l.icon) x.appendChild(el('span', 'skc-ic', l.icon));
          x.appendChild(document.createTextNode(l.label)); x.href = l.href;
          if (/^https?:/.test(l.href) && host(l.href) !== host(location.href)) { x.target = '_blank'; x.rel = 'noopener'; }
          row.appendChild(x);
        });
        (buttons || []).forEach(function (b) {
          var x = el('button', 'skc-btn' + (b.cls ? ' ' + b.cls : '')); x.type = 'button';
          if (b.icon) x.appendChild(el('span', 'skc-ic', b.icon));
          var lab = el('span', 'skc-lab'); lab.appendChild(el('span', 'skc-t', b.label));
          if (b.note) lab.appendChild(el('span', 'skc-note', b.note));
          x.appendChild(lab);
          x.addEventListener('click', function () { user((b.icon ? b.icon + ' ' : '') + b.label); b.go(); });
          row.appendChild(x);
        });
        log.appendChild(row);
      }
    } });
  }
  function helpLinks() {
    var l = [];
    if (campus && campus.whatsapp) l.push({ label: 'WhatsApp', icon: '💬', href: 'https://wa.me/' + campus.whatsapp, cls: 'skc-wa' });
    if (campus && campus.tel) l.push({ label: 'Call ' + campus.phone, icon: '📞', href: 'tel:' + campus.tel, cls: 'skc-call' });
    if (campus && !campus.whatsapp && !campus.tel) l.push({ label: 'Contact ' + campus.name, icon: '📍', href: pageUrl(campus, 'contact') });
    return l;
  }
  function backButtons() {
    return [{ label: 'Main menu', icon: '☰', go: menu, cls: 'skc-quiet' }, { label: 'Common questions', icon: '❓', go: questions, cls: 'skc-quiet' }];
  }

  /* ---------- flows ---------- */
  function welcome() {
    if (campus) {
      bot('Assalam o Alaikum! 👋 Welcome to SKANS ' + campus.name + '. How can we help you today?');
      menu();
    } else {
      bot('Assalam o Alaikum! 👋 Welcome to SKANS School System. Which campus are you interested in?');
      pickCampus(true);
    }
  }
  function pickCampus(quiet) {
    if (!quiet) bot('Which campus would you like to know about?');
    bot('Choose a campus:', campuses.map(function (c) {
      return { label: c.name, note: c.classes || '', icon: '🏫', go: function () {
        campus = c; store('skans_chat_campus', c.id);
        bot('Great choice: ' + c.name + '. What would you like to know?'); menu(true); } };
    }), null, 'list');
  }
  function menu(quiet) {
    if (!campus) return pickCampus();
    var b = MENU.map(function (k) { var t = topic(k); return { label: t.label, icon: t.icon, go: function () { openTopic(k); } }; });
    b.push({ label: 'Common questions', icon: '❓', go: questions });
    bot(quiet === true ? 'Pick a topic:' : 'Choose a topic for ' + campus.name + ':', b, null, 'cards');
    bot('Looking for another campus?', [{ label: 'Change campus', icon: '🔁', go: function () { pickCampus(); }, cls: 'skc-quiet' }]);
  }
  function openTopic(key) {
    if (!campus) return pickCampus();
    var t = topic(key);
    var links = [{ label: 'Open ' + t.label, icon: '↗', href: pageUrl(campus, key), cls: 'skc-main' }];
    if (key === 'contact') links = links.concat(helpLinks());
    bot(t.label + ' of ' + campus.name + ': tap below to open the page.', backButtons(), links);
  }
  function relevant() {
    return faq.filter(function (f) { return !f.campus || f.campus === 'all' || (campus && f.campus === campus.id); });
  }
  function questions() {
    bot('Here are the questions parents ask most:', relevant().map(function (f) { return { label: f.q, go: function () { answer(f); } }; })
      .concat([{ label: 'Main menu', icon: '☰', go: menu, cls: 'skc-quiet' }]), null, 'list');
  }
  function answer(f) {
    if (f.topic && !campus) {
      bot(f.a);
      bot('Which campus? I will show you the right page.', campuses.map(function (c) {
        return { label: c.name, note: c.classes || '', icon: '🏫', go: function () { campus = c; store('skans_chat_campus', c.id); answer(f); } };
      }), null, 'list');
      return;
    }
    var links = [];
    if (f.topic && campus) { var t = topic(f.topic); if (t) links.push({ label: 'Open ' + t.label, icon: '↗', href: pageUrl(campus, f.topic), cls: 'skc-main' }); }
    if (f.link) links.push({ label: f.linkText || 'Open', icon: '↗', href: f.link, cls: 'skc-main' });
    bot(f.a, backButtons(), links.concat(helpLinks()));
  }

  /* ---------- search (keywords) ---------- */
  function words(s) { return String(s).toLowerCase().replace(/[^a-z0-9\s\-\.\/%]/g, ' ').split(/\s+/).filter(Boolean); }
  function search(q) {
    var text = ' ' + words(q).join(' ') + ' ';
    // a campus name typed: switch to it
    var named = campuses.filter(function (c) { var n = c.name.toLowerCase().replace(' campus', ''); return text.indexOf(n) >= 0 || text.indexOf(' ' + c.id + ' ') >= 0; })[0];
    if (named && (!campus || named.id !== campus.id)) { campus = named; store('skans_chat_campus', named.id); bot('Showing answers for ' + named.name + '.'); }

    var scored = relevant().map(function (f) {
      var score = 0;
      (f.keywords || []).forEach(function (k) { k = String(k).toLowerCase(); if (text.indexOf(' ' + k + ' ') >= 0 || (k.length > 4 && text.indexOf(k) >= 0)) score += k.split(' ').length + 1; });
      words(f.q).forEach(function (w) { if (w.length > 3 && text.indexOf(' ' + w + ' ') >= 0) score += 0.5; });
      return { f: f, score: score };
    }).filter(function (x) { return x.score >= 1; }).sort(function (a, b) { return b.score - a.score; });

    if (!scored.length) {
      bot('Sorry, I don’t have an answer for that yet. Please choose a topic, or message the campus directly.', backButtons(), helpLinks());
      return;
    }
    if (scored.length === 1 || scored[0].score >= scored[1].score + 2) return answer(scored[0].f);
    bot('Did you mean:', scored.slice(0, 3).map(function (x) { return { label: x.f.q, go: function () { answer(x.f); } }; })
      .concat([{ label: 'Main menu', icon: '☰', go: menu, cls: 'skc-quiet' }]), null, 'list');
  }

  /* ---------- for demo pages: switch campus and restart ---------- */
  window.SKANS_CHAT = {
    restart: function (campusId) {
      if (!root) return;
      gen++; queue = []; busy = false; log.innerHTML = '';
      campus = find(campusId) || null; onCampusSite = !!campus;
      if (opened) welcome(); else toggle(true);
    },
    open: function () { if (root) toggle(true); }
  };
})();
