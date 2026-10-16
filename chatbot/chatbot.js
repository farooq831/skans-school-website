/* SKANS chatbot: buttons, links and fixed answers (no AI, no server code).
 * Add to any page, before </body>:
 *   <script src="https://skansschool.edu.pk/chatbot/chatbot.js?v=3" defer></script>
 * Optional attributes on that tag:
 *   data-campus="golra"      force a campus (normally found automatically from the web address)
 *   data-position="right"    put the button on the right (default: left, so it never covers WhatsApp)
 *   data-container="#id"     show the chat inside an element instead of the page corner (demo pages)
 *   data-open="true"         open the chat as soon as the page loads (demo pages)
 * Data: campuses.js and faq.js in the same folder. Edit those, not this file. (version 3, October 2026)
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
    // the hour number makes browsers pick up edits to campuses.js / faq.js within an hour
    s.src = base + file + '?' + (ver ? ver + '&' : '') + 'h=' + Math.floor(Date.now() / 36e5); s.async = true;
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
  /* answer text for the current campus: "per" answers first, then {campus} {classes} {phone} filled in */
  function says(f) {
    var a = (f.per && campus && (f.per[campus.id] || f.per['*'])) || f.a;
    return String(a).replace(/\{campus\}/g, campus ? campus.name : 'SKANS School System')
      .replace(/\{classes\}/g, (campus && campus.classes) || 'the classes shown on the Programs page')
      .replace(/\{phone\}/g, (campus && campus.phone) || 'the campus office');
  }
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
    faq = (window.SKANS_CHAT_FAQ || []).concat((window.SKANS_CHAT_SMALLTALK || []).map(function (f) { f.small = true; return f; }));
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
      bot('Assalam o Alaikum! 👋 Welcome to SKANS ' + campus.name + '. Pick a topic, or type your question below.');
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
    // on a campus website the chat stays with that campus; the main site can switch
    if (!onCampusSite) bot('Looking for another campus?', [{ label: 'Change campus', icon: '🔁', go: function () { pickCampus(); }, cls: 'skc-quiet' }]);
  }
  function openTopic(key) {
    if (!campus) return pickCampus();
    var t = topic(key);
    var links = [{ label: 'Open ' + t.label, icon: '↗', href: pageUrl(campus, key), cls: 'skc-main' }];
    if (key === 'contact') links = links.concat(helpLinks());
    bot(t.label + ' of ' + campus.name + ': tap below to open the page.', backButtons(), links);
  }
  /* questions for this campus. only "main": questions that compare campuses (main site only);
     only "campus": questions for one campus website */
  function relevant() {
    return faq.filter(function (f) {
      if (f.campus && f.campus !== 'all' && !(campus && f.campus === campus.id)) return false;
      if (f.only === 'main' && onCampusSite) return false;
      if (f.only === 'campus' && !onCampusSite) return false;
      return true;
    });
  }
  /* the short list on the "Common questions" button: only entries with "show": true */
  function popular() { return relevant().filter(function (f) { return f.show && !f.small; }); }
  function questions() {
    bot('Questions parents ask most. You can also type any question below.', popular().map(function (f) { return { label: f.q, go: function () { answer(f); } }; })
      .concat([{ label: 'Main menu', icon: '☰', go: menu, cls: 'skc-quiet' }]), null, 'list');
  }
  function answer(f) {
    if (f.small) { bot(says(f), backButtons(), f.help ? helpLinks() : null); return; }
    if (f.topic && !campus) {
      bot(says(f));
      bot('Which campus? I will show you the right page.', campuses.map(function (c) {
        return { label: c.name, note: c.classes || '', icon: '🏫', go: function () { campus = c; store('skans_chat_campus', c.id); answer(f); } };
      }), null, 'list');
      return;
    }
    var links = [];
    if (f.topic && campus) { var t = topic(f.topic); if (t) links.push({ label: 'Open ' + t.label, icon: '↗', href: pageUrl(campus, f.topic), cls: 'skc-main' }); }
    if (f.link) links.push({ label: f.linkText || 'Open', icon: '↗', href: f.link, cls: 'skc-main' });
    bot(says(f), backButtons(), links.concat(helpLinks()));
    if (f.feeTable && campus) feesData(campus, function (d) { if (d) feeClasses(d); });
  }

  /* ---------- real fees: every campus website publishes chat-fees.php (its Fee Structure table) ---------- */
  var feeCache = {};
  function get(url, done) {                               // GET a text file; null when it fails (404, offline, blocked)
    var x = new XMLHttpRequest(), finished = false;
    function end(t) { if (!finished) { finished = true; done(t); } }
    try {
      x.open('GET', url, true); x.timeout = 6000;
      x.onload = function () { end(x.status >= 200 && x.status < 300 ? x.responseText : null); };
      x.onerror = x.ontimeout = function () { end(null); };
      x.send();
    } catch (e) { end(null); }
  }
  /* the fee table of a campus: 1. its chat-fees.php (if uploaded), 2. else read its Fee Structure page */
  function feesData(c, done) {
    if (feeCache.hasOwnProperty(c.id)) return done(feeCache[c.id]);
    function end(d) { feeCache[c.id] = d; done(d); }
    get(c.url.replace(/\/?$/, '/') + 'chat-fees.php', function (t) {
      var d = null; try { d = JSON.parse(t); } catch (e) {}
      if (d && d.classes && d.classes.length && d.columns) return end(d);
      get(pageUrl(c, 'fees'), function (html) { end(html ? fromPage(html) : null); });
    });
  }
  /* reads the fee table on the Fee Structure page (the same numbers visitors see there) */
  function fromPage(html) {
    try {
      var doc = new DOMParser().parseFromString(html, 'text/html');
      var table = doc.querySelector('table.sk-table'); if (!table) return null;
      var keys = { registration: /registration/i, admission: /admission/i, security: /security/i, annual: /annual/i, tuition: /tuition/i, lab: /lab/i };
      var columns = [], currency = '';
      Array.prototype.slice.call(table.querySelectorAll('thead th'), 1).forEach(function (th, i) {
        var small = th.querySelector('small'), period = small ? small.textContent.trim() : '';
        var label = th.textContent.replace(period, '').trim(), key = 'c' + i;
        for (var k in keys) if (keys[k].test(label)) { key = k; break; }
        columns.push({ key: key, label: label, period: period });
      });
      var classes = Array.prototype.map.call(table.querySelectorAll('tbody tr'), function (tr) {
        var name = tr.querySelector('th'), row = { 'class': name ? name.textContent.trim() : '' };
        Array.prototype.forEach.call(tr.querySelectorAll('td'), function (td, i) {
          var t = td.textContent.trim(), n = t.replace(/[^0-9]/g, '');
          if (!currency) { var m = /^([^0-9\s]+\.?)\s*[0-9]/.exec(t); if (m) currency = m[1]; }
          if (columns[i]) row[columns[i].key] = n ? parseInt(n, 10) : null;
        });
        return row;
      }).filter(function (r) { return r['class']; });
      var session = '';
      Array.prototype.forEach.call(doc.querySelectorAll('.sk-eyebrow'), function (e) { var m = /session:\s*(.+)$/i.exec(e.textContent.trim()); if (m) session = m[1]; });
      return classes.length && columns.length ? { session: session, currency: currency || 'Rs.', columns: columns, classes: classes } : null;
    } catch (e) { return null; }
  }
  var NUM = { one: 1, two: 2, three: 3, four: 4, five: 5, six: 6, seven: 7, eight: 8, nine: 9, ten: 10, eleven: 11, twelve: 12 };
  function feeQuestion(text) { return /\s(fee|fees|fess|feez|tuition|charges|cost|price|kitni|kharcha|paisay|dues)\s|\show much\s/.test(text); }
  /* which row of the fee table the parent means: "Nursery", "pg", "kg", "pre 9", "class 5", "5th", "grade five" */
  function classIn(text, rows) {
    var key = function (s) { return ' ' + words(s).join(' ') + ' '; };
    var full = rows.filter(function (r) { return text.indexOf(key(r['class'])) >= 0; })
      .sort(function (a, b) { return b['class'].length - a['class'].length; })[0];
    if (full) return full;
    var short = [[/\s(pg|play group|playgroup|play)\s/, /play/], [/\s(kg|k\.g|kindergarten|kindergarden|prep)\s/, /kinder|^kg|prep/],
                 [/\s(nursery|nursry|nursary)\s/, /nurs/], [/\spre[\s-]?9\s|\spre nine\s/, /pre[\s-]?9/],
                 [/\s(o level|olevel|o-level|igcse)\s/, /o level|igcse/], [/\s(a level|alevel|a-level)\s/, /a level/]];
    for (var i = 0; i < short.length; i++) {
      if (!short[i][0].test(text)) continue;
      var hit = rows.filter(function (r) { return short[i][1].test(r['class'].toLowerCase()); })[0];
      if (hit) return hit;
    }
    // a class number: "class 5", "class5", "5 class", "5th class", "5th", "class five", "five class"
    var ways = [/\s(?:class|grade|cls|jamat)\s?(\d{1,2}|[a-z]+)\s/, /\s(\d{1,2}|[a-z]+?)(?:st|nd|rd|th)?\s(?:class|grade|jamat)\s/, /\s(\d{1,2})(?:st|nd|rd|th)\s/];
    var n = NaN;
    for (var w = 0; w < ways.length && !n; w++) { var m = ways[w].exec(text); if (m) n = NUM[m[1]] || parseInt(m[1], 10); }
    if (!n) return null;
    return rows.filter(function (r) { var k = /^(?:class|grade)\s*(\d+)/i.exec(r['class']); return k && +k[1] === n; })[0] || null;
  }
  function money(cur, n) { return cur + ' ' + n.toLocaleString('en-US'); }
  function feeReply(row, d) {
    var cur = d.currency || 'Rs.', lines = [];
    d.columns.forEach(function (c) {
      var v = row[c.key];
      if (typeof v === 'number') lines.push('• ' + c.label + ': ' + money(cur, v) + (c.period ? ' (' + c.period + ')' : ''));
    });
    var links = [{ label: 'Open Fee Structure', icon: '↗', href: pageUrl(campus, 'fees'), cls: 'skc-main' }].concat(helpLinks());
    var more = [{ label: 'Another class', icon: '🔎', go: function () { feeClasses(d); } }].concat(backButtons());
    if (!lines.length) { bot('The fees for ' + row['class'] + ' are not on the website yet. Please ask the ' + campus.name + ' office.', more, links); return; }
    var first = ['admission', 'security', 'registration', 'tuition'];   // first payment rule (Fee Structure page)
    var total = first.every(function (k) { return typeof row[k] === 'number'; }) ? first.reduce(function (s, k) { return s + row[k]; }, 0) : 0;
    bot('Fees for ' + row['class'] + ' at ' + campus.name + (d.session ? ' (session ' + d.session + ')' : '') + ':\n' + lines.join('\n')
      + (total ? '\nFirst payment at admission: ' + money(cur, total) + ' (admission + security + registration + one month’s tuition).' : ''), more, links);
  }
  function feeClasses(d) {
    bot('Choose a class to see its fees:', d.classes.map(function (r) { return { label: r['class'], go: function () { feeReply(r, d); } }; }));
  }

  /* ---------- search (keywords, with room for one typing mistake in longer words) ---------- */
  function words(s) { return String(s).toLowerCase().replace(/[’']/g, '').replace(/[^a-z0-9\s\-\.\/%]/g, ' ').split(/\s+/).filter(Boolean); }
  function near(a, b) {                                   // same word with at most one letter wrong, missing or extra
    if (Math.abs(a.length - b.length) > 1) return false;
    var i = 0, j = 0, d = 0;
    while (i < a.length && j < b.length) {
      if (a[i] === b[j]) { i++; j++; continue; }
      if (++d > 1) return false;
      if (a.length > b.length) i++; else if (b.length > a.length) j++; else { i++; j++; }
    }
    return d + (a.length - i) + (b.length - j) <= 1;
  }
  function score(f, text, list) {
    var s = 0;
    (f.keywords || []).forEach(function (k) {
      k = String(k).toLowerCase();
      if (text.indexOf(' ' + k + ' ') >= 0 || (k.length > 4 && text.indexOf(k) >= 0)) s += k.split(' ').length + 1;
      else if (k.length >= 6 && k.indexOf(' ') < 0 && list.some(function (w) { return w.length >= 5 && near(w, k); })) s += 1;
    });
    words(f.q).forEach(function (w) { if (w.length > 3 && text.indexOf(' ' + w + ' ') >= 0) s += 0.5; });
    return s;
  }
  function search(q) {
    var list = words(q), text = ' ' + list.join(' ') + ' ';
    // a campus name typed
    // full names first ("golra senior" before the id "golra"), the longest name wins
    var named = campuses.filter(function (c) { return text.indexOf(' ' + c.name.toLowerCase().replace(' campus', '') + ' ') >= 0; })
      .sort(function (a, b) { return b.name.length - a.name.length; })[0]
      || campuses.filter(function (c) { return text.indexOf(' ' + c.id + ' ') >= 0; })[0];
    if (named && (!campus || named.id !== campus.id)) {
      if (onCampusSite) {                                 // campus website: no details of other campuses here
        bot('This chat is for ' + campus.name + '. For ' + named.name + ', please visit its own website.', backButtons(),
          [{ label: 'Open ' + named.name + ' website', icon: '↗', href: named.url, cls: 'skc-main' }]);
        return;
      }
      campus = named; store('skans_chat_campus', named.id); bot('Showing answers for ' + named.name + '.');
    }

    if (campus && feeQuestion(text)) {
      var asked = campus;
      feesData(campus, function (d) {
        var row = d && campus === asked ? classIn(text, d.classes) : null;
        if (row) return feeReply(row, d);
        // a class was named but no amounts (file not uploaded yet, or class not in the table): the fees answer
        var fees = relevant().filter(function (f) { return f.feeTable; })[0];
        if (fees && /\s(class|grade|playgroup|pg|nursery|kg|kindergarten|pre|o level|a level|igcse|\d{1,2}(st|nd|rd|th))\s/.test(text)) answer(fees);
        else match(text, list);
      });
      return;
    }
    match(text, list);
  }
  function match(text, list) {
    var scored = relevant().map(function (f) { return { f: f, score: score(f, text, list) }; })
      .filter(function (x) { return x.score >= 1; }).sort(function (a, b) { return b.score - a.score; });
    var real = scored.filter(function (x) { return !x.f.small; }), chat = scored.filter(function (x) { return x.f.small; });

    if (!real.length && chat.length) return answer(chat[0].f);          // only small talk: "hello", "thanks"
    if (real.length && chat.length && chat[0].f.pre) bot(chat[0].f.pre);  // "hello, what are the fees?"
    if (!real.length) {
      bot('Sorry, I don’t have an answer for that yet. Try one of these, or message the campus directly:',
        popular().slice(0, 4).map(function (f) { return { label: f.q, go: function () { answer(f); } }; })
          .concat([{ label: 'Main menu', icon: '☰', go: menu, cls: 'skc-quiet' }]), helpLinks(), 'list');
      return;
    }
    if (real.length === 1 || real[0].score >= real[1].score + 2) return answer(real[0].f);
    bot('Did you mean:', real.slice(0, 3).map(function (x) { return { label: x.f.q, go: function () { answer(x.f); } }; })
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
