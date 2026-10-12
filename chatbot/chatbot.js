/* SKANS chatbot: buttons, links and fixed answers (no AI, no server code).
 * Add to any page, before </body>:
 *   <script src="https://skansschool.edu.pk/chatbot/chatbot.js" defer></script>
 * Optional attributes: data-campus="golra" (force a campus), data-position="right" (default "left").
 * On a campus website the campus is found automatically from the web address.
 * Data: campuses.js and faq.js in the same folder (edit those, not this file).
 */
(function () {
  'use strict';
  if (window.SKANS_CHAT_LOADED) return;
  window.SKANS_CHAT_LOADED = true;

  var me = document.currentScript || (function () {
    var s = document.querySelectorAll('script[src*="chatbot.js"]'); return s[s.length - 1];
  })();
  var base = me.src.replace(/[^\/]*$/, '');                 // folder of chatbot.js
  var ver = (me.src.split('?')[1] || '');
  var forced = me.getAttribute('data-campus') || '';
  var side = me.getAttribute('data-position') === 'right' ? 'right' : 'left';

  var TOPICS = [
    { key: 'fees',       label: 'Fee Structure',            icon: '💰', page: 'fee-structure.php' },
    { key: 'admissions', label: 'Admissions & age guide',   icon: '📝', page: 'admissions.php' },
    { key: 'policy',     label: 'Admission Policy',         icon: '📋', page: 'admission-policy.php' },
    { key: 'programs',   label: 'Programs & classes',       icon: '🎓', page: 'departments.php' },
    { key: 'special',    label: 'Special Programs',         icon: '🤖', page: 'advanced-placement-program.php' },
    { key: 'facilities', label: 'Facilities',               icon: '🏫', page: 'services.php' },
    { key: 'faculty',    label: 'Faculty',                  icon: '👩‍🏫', page: 'faculty.php' },
    { key: 'news',       label: 'News & Events',            icon: '📰', page: 'news-events.php' },
    { key: 'about',      label: 'About & Principal’s message', icon: '🏛️', page: 'about.php' },
    { key: 'contact',    label: 'Location & contact',       icon: '📍', page: 'contact-us.php' },
    { key: 'policies',   label: 'SKANS Policies',           icon: '📄', page: 'policies.php' }
  ];
  var MENU = ['fees', 'admissions', 'programs', 'facilities', 'faculty', 'news', 'contact'];   // shown as buttons

  function load(src, done) {
    var s = document.createElement('script');
    s.src = base + src + (ver ? '?' + ver : ''); s.async = true;
    s.onload = done; s.onerror = done;
    document.head.appendChild(s);
  }
  var css = document.createElement('link');
  css.rel = 'stylesheet'; css.href = base + 'chatbot.css' + (ver ? '?' + ver : '');
  document.head.appendChild(css);

  var pending = 2;
  function ready() { if (--pending === 0) start(); }
  load('campuses.js', ready);
  load('faq.js', ready);

  /* ---------- helpers ---------- */
  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text != null) e.textContent = text;
    return e;
  }
  function store(k, v) { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) { return null; } }
  function host(u) { try { return new URL(u).hostname.replace(/^www\./, '').toLowerCase(); } catch (e) { return ''; } }
  function topic(key) { for (var i = 0; i < TOPICS.length; i++) if (TOPICS[i].key === key) return TOPICS[i]; return null; }
  function pageUrl(c, key) {
    var t = topic(key); if (!c || !t) return '';
    var page = (c.pages && c.pages[key]) || t.page;
    return c.url.replace(/\/?$/, '/') + page;
  }

  var campuses = [], faq = [], campus = null, box, log, input, opened = false;

  function start() {
    campuses = window.SKANS_CHAT_CAMPUSES || [];
    faq = window.SKANS_CHAT_FAQ || [];
    if (!campuses.length) return;                         // data missing: show nothing rather than a broken bot
    var here = host(location.href);
    campus = find(forced) || campuses.filter(function (c) { return host(c.url) === here; })[0] || null;
    var onCampusSite = !!campus;
    if (!campus) campus = find(store('skans_chat_campus'));
    build(onCampusSite);
  }
  function find(id) { for (var i = 0; i < campuses.length; i++) if (campuses[i].id === id) return campuses[i]; return null; }

  /* ---------- layout ---------- */
  function build(onCampusSite) {
    var launcher = el('button', 'skc-launch skc-' + side);
    launcher.type = 'button';
    launcher.setAttribute('aria-label', 'Open SKANS help chat');
    launcher.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-5 4v-4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg><span>Help</span>';

    box = el('section', 'skc-box skc-' + side);
    box.setAttribute('role', 'dialog'); box.setAttribute('aria-label', 'SKANS help'); box.hidden = true;
    var head = el('header', 'skc-head');
    var title = el('div', 'skc-title');
    title.appendChild(el('strong', null, 'SKANS Help'));
    title.appendChild(el('span', 'skc-sub', 'Choose a button or type a question'));
    var close = el('button', 'skc-close', '×'); close.type = 'button'; close.setAttribute('aria-label', 'Close');
    head.appendChild(title); head.appendChild(close);
    log = el('div', 'skc-log'); log.setAttribute('aria-live', 'polite');
    var form = el('form', 'skc-form');
    input = el('input'); input.type = 'text'; input.placeholder = 'Type a question, e.g. fees'; input.setAttribute('aria-label', 'Your question'); input.maxLength = 120;
    var send = el('button', 'skc-send', 'Ask'); send.type = 'submit';
    form.appendChild(input); form.appendChild(send);
    box.appendChild(head); box.appendChild(log); box.appendChild(form);
    document.body.appendChild(launcher); document.body.appendChild(box);

    function toggle(open) {
      opened = open; box.hidden = !open; launcher.setAttribute('aria-expanded', open ? 'true' : 'false');
      launcher.classList.toggle('skc-hidden', open);
      if (open && !log.childNodes.length) welcome(onCampusSite);
      if (open) setTimeout(function () { input.focus(); }, 50); else launcher.focus();
    }
    launcher.addEventListener('click', function () { toggle(true); });
    close.addEventListener('click', function () { toggle(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && opened) toggle(false); });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var q = input.value.trim(); if (!q) return;
      input.value = ''; user(q); search(q);
    });
  }

  /* ---------- messages ---------- */
  function scroll() { log.scrollTop = log.scrollHeight; }
  function user(text) { log.appendChild(el('div', 'skc-msg skc-user', text)); scroll(); }
  function bot(text, buttons, links) {
    var m = el('div', 'skc-msg skc-bot');
    String(text).split('\n').forEach(function (line, i) { if (i) m.appendChild(el('br')); m.appendChild(document.createTextNode(line)); });
    log.appendChild(m);
    if ((links && links.length) || (buttons && buttons.length)) {
      var row = el('div', 'skc-actions');
      (links || []).forEach(function (l) {
        if (!l || !l.href) return;
        var a = el('a', 'skc-link' + (l.cls ? ' ' + l.cls : ''), l.label); a.href = l.href;
        if (/^https?:/.test(l.href) && host(l.href) !== host(location.href)) { a.target = '_blank'; a.rel = 'noopener'; }
        row.appendChild(a);
      });
      (buttons || []).forEach(function (b) {
        var x = el('button', 'skc-btn' + (b.cls ? ' ' + b.cls : ''), b.label); x.type = 'button';
        x.addEventListener('click', function () { if (b.echo !== false) user(b.label); b.go(); });
        row.appendChild(x);
      });
      log.appendChild(row);
    }
    scroll();
  }
  function helpLinks() {
    var l = [];
    if (campus && campus.whatsapp) l.push({ label: 'WhatsApp ' + campus.name, href: 'https://wa.me/' + campus.whatsapp, cls: 'skc-wa' });
    if (campus && campus.tel) l.push({ label: 'Call ' + campus.phone, href: 'tel:' + campus.tel });
    if (campus && !campus.whatsapp && !campus.tel) l.push({ label: 'Contact ' + campus.name, href: pageUrl(campus, 'contact') });
    return l;
  }
  function backButtons() {
    var b = [{ label: '☰ Main menu', go: menu, cls: 'skc-quiet' }];
    b.push({ label: '❓ Common questions', go: questions, cls: 'skc-quiet' });
    return b;
  }

  /* ---------- flows ---------- */
  function welcome(onCampusSite) {
    if (campus) {
      bot('Assalam o Alaikum! Welcome to SKANS ' + campus.name + '. How can we help you today?');
      menu(onCampusSite);
    } else {
      bot('Assalam o Alaikum! Welcome to SKANS School System. Which campus are you interested in?');
      pickCampus();
    }
  }
  function pickCampus() {
    bot('Choose a campus:', campuses.map(function (c) {
      return { label: c.name + (c.classes ? ' · ' + c.classes : ''), cls: 'skc-campus', go: function () {
        campus = c; store('skans_chat_campus', c.id);
        bot('Great, ' + c.name + '. What would you like to know?'); menu(); } };
    }));
  }
  function menu(onCampusSite) {
    var b = MENU.map(function (k) {
      var t = topic(k);
      return { label: t.icon + ' ' + t.label, go: function () { openTopic(k); } };
    });
    b.push({ label: '❓ Common questions', go: questions });
    b.push({ label: '🔁 Change campus', go: pickCampus, cls: 'skc-quiet' });
    if (!campus) return pickCampus();
    bot('Choose a topic for ' + campus.name + ':', b);
  }
  function openTopic(key) {
    if (!campus) return pickCampus();
    var t = topic(key);
    var links = [{ label: 'Open ' + t.label, href: pageUrl(campus, key), cls: 'skc-main' }];
    var text = t.label + ' of ' + campus.name + ':';
    if (key === 'contact') links = links.concat(helpLinks());
    bot(text, backButtons(), links);
  }
  function relevant() {
    return faq.filter(function (f) { return !f.campus || f.campus === 'all' || (campus && f.campus === campus.id); });
  }
  function questions() {
    bot('Common questions:', relevant().map(function (f) { return { label: f.q, go: function () { answer(f); }, cls: 'skc-q', echo: true }; })
      .concat([{ label: '☰ Main menu', go: menu, cls: 'skc-quiet' }]));
  }
  function answer(f) {
    var links = [];
    if (f.topic && campus) { var t = topic(f.topic); if (t) links.push({ label: 'Open ' + t.label + (campus ? ' – ' + campus.name : ''), href: pageUrl(campus, f.topic), cls: 'skc-main' }); }
    if (f.link) links.push({ label: f.linkText || 'Open', href: f.link, cls: 'skc-main' });
    if (f.topic && !campus) {
      bot(f.a);
      bot('Which campus? I will show you the right page.', campuses.map(function (c) {
        return { label: c.name, cls: 'skc-campus', go: function () { campus = c; store('skans_chat_campus', c.id); answer(f); } };
      }));
      return;
    }
    bot(f.a, backButtons(), links.concat(helpLinks()));
  }

  /* ---------- search (keywords) ---------- */
  function words(s) { return String(s).toLowerCase().replace(/[^a-z0-9\s\-\.\/%]/g, ' ').split(/\s+/).filter(Boolean); }
  function search(q) {
    var text = ' ' + words(q).join(' ') + ' ';
    var scored = relevant().map(function (f) {
      var score = 0;
      (f.keywords || []).forEach(function (k) { k = String(k).toLowerCase(); if (text.indexOf(' ' + k + ' ') >= 0 || (k.length > 4 && text.indexOf(k) >= 0)) score += k.split(' ').length + 1; });
      words(f.q).forEach(function (w) { if (w.length > 3 && text.indexOf(' ' + w + ' ') >= 0) score += 0.5; });
      return { f: f, score: score };
    }).filter(function (x) { return x.score >= 1; }).sort(function (a, b) { return b.score - a.score; });

    // a campus name typed: switch to it
    var named = campuses.filter(function (c) { var n = c.name.toLowerCase().replace(' campus', ''); return text.indexOf(n) >= 0 || text.indexOf(' ' + c.id + ' ') >= 0; })[0];
    if (named && (!campus || named.id !== campus.id)) { campus = named; store('skans_chat_campus', named.id); bot('Showing answers for ' + named.name + '.'); }

    if (!scored.length) {
      bot('Sorry, I don’t have an answer for that yet. Please choose a topic, or message the campus directly.', backButtons(), helpLinks());
      return;
    }
    if (scored.length === 1 || scored[0].score >= scored[1].score + 2) return answer(scored[0].f);
    bot('Did you mean:', scored.slice(0, 3).map(function (x) { return { label: x.f.q, cls: 'skc-q', go: function () { answer(x.f); } }; })
      .concat([{ label: '☰ Main menu', go: menu, cls: 'skc-quiet' }]));
  }
})();
