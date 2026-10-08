<?php
$page_title = 'News & Events';
$page_description = 'Announcements, news and events from SKANS School System, AECHS Campus, Rawalpindi.';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/announcements.php';
// Rebuilt Oct 2026. Old page: _old/news-events-legacy.php.
// Announcements: the 6 newest from the last $site['announcement_days'] days (30); the newest is featured.
// Older announcements leave the website automatically but stay saved in the database.

$announcements = [];
$news = [];
if ($db_ok) {
    $announcements = current_announcements($conn, $site, 6); // includes/announcements.php
    $r = $conn->query('SELECT n.id, n.title, n.description, n.image_url, n.date, c.name AS category
                       FROM news_events n JOIN categories c ON n.category_id = c.id
                       ORDER BY n.date DESC, n.id DESC');
    $news = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}
$featured = array_shift($announcements);
$categories = array_values(array_unique(array_column($news, 'category')));
$first_batch = 12; // news cards shown before "Show more"

/** Picture path if the file exists, else ''. */
function upload_src($file): string
{
    return upload_url((string) $file);
}

/** The whole picture on a soft blurred background: never cropped, never stretched. */
function media(string $src, string $alt, string $class = '', bool $lazy = true): string
{
    if ($src === '') {
        return '<div class="sk-media sk-media--empty ' . $class . '" aria-hidden="true"><span>SKANS</span></div>';
    }
    return '<a class="sk-media ' . $class . '" href="' . e($src) . '" target="_blank" rel="noopener" style="--img: url(\'' . e($src) . '\')">'
         . '<img src="' . e($src) . '" alt="' . e($alt) . '"' . ($lazy ? ' loading="lazy"' : '') . '>'
         . '<span class="sk-visually-hidden"> (opens the full picture)</span></a>';
}
?>
<?php page_banner('News & Events', 'Announcements, news and events from ' . $site['full_name'] . '.', [['News & Events', '']]); ?>

<section class="sk-section sk-anchor" id="announcements">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Stay informed</span>
            <h2>Announcements</h2>
            <span class="sk-rule"></span>
        </div>

<?php if (!$featured): ?>
        <p class="sk-muted">There are no announcements at the moment. Please check back soon.</p>
<?php else: ?>
        <article class="sk-feature">
            <?= media(upload_src($featured['image']), (string) $featured['heading'], 'sk-feature__media', false) ?>
            <div class="sk-feature__body">
                <span class="sk-badge">Latest announcement</span>
                <time class="sk-date" datetime="<?= e(date('Y-m-d', strtotime($featured['created_at']))) ?>"><?= e(nice_date($featured['created_at'])) ?></time>
                <h3 class="sk-feature__title"><?= e($featured['heading']) ?></h3>
                <p class="sk-feature__text"><?= nl2br(e($featured['announcement_text'])) ?></p>
            </div>
        </article>

<?php if ($announcements): ?>
        <h3 class="sk-subhead">More announcements</h3>
        <div class="sk-ann-grid">
<?php foreach ($announcements as $a):
        $text = (string) $a['announcement_text'];
        $long = mb_strlen(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')) > 140; ?>
            <article class="sk-card sk-ann">
                <?= media(upload_src($a['image']), (string) $a['heading'], 'sk-ann__media') ?>
                <div class="sk-card__body">
                    <time class="sk-date" datetime="<?= e(date('Y-m-d', strtotime($a['created_at']))) ?>"><?= e(nice_date($a['created_at'])) ?></time>
                    <h4 class="sk-card__title"><?= e($a['heading']) ?></h4>
<?php if ($long): ?>
                    <p><?= e(excerpt($text, 140)) ?></p>
                    <details class="sk-more">
                        <summary>Read more<span class="sk-visually-hidden"> about <?= e($a['heading']) ?></span></summary>
                        <p><?= nl2br(e($text)) ?></p>
                    </details>
<?php else: ?>
                    <p><?= nl2br(e($text)) ?></p>
<?php endif; ?>
                </div>
            </article>
<?php endforeach; ?>
        </div>
<?php endif; ?>
<?php endif; ?>
    </div>
</section>

<section class="sk-section sk-section--white sk-anchor" id="news">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Life at SKANS</span>
            <h2>News &amp; Events</h2>
            <span class="sk-rule"></span>
        </div>

<?php if (!$news): ?>
        <p class="sk-muted">News and events will appear here soon.</p>
<?php else: ?>
        <div class="sk-filter" role="group" aria-label="Show">
            <button type="button" class="sk-filter__btn" aria-pressed="true" data-filter="all">All <span><?= count($news) ?></span></button>
<?php foreach ($categories as $cat): ?>
            <button type="button" class="sk-filter__btn" aria-pressed="false" data-filter="<?= e($cat) ?>"><?= e($cat) ?> <span><?= count(array_filter($news, function ($n) use ($cat) { return $n['category'] === $cat; })) ?></span></button>
<?php endforeach; ?>
        </div>

        <div class="sk-grid sk-grid--3 sk-news-grid" id="news-grid">
<?php foreach ($news as $i => $n):
        $text = (string) $n['description'];
        $long = mb_strlen(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')) > 150; ?>
            <article class="sk-card sk-news sk-anchor" id="news-<?= (int) $n['id'] ?>" data-category="<?= e($n['category']) ?>"<?= $i >= $first_batch ? ' hidden' : '' ?>>
                <?= media(upload_src($n['image_url']), (string) $n['title'], 'sk-news__media') ?>
                <div class="sk-card__body">
                    <div class="sk-news__meta">
                        <span class="sk-chip"><?= e($n['category']) ?></span>
                        <time class="sk-date" datetime="<?= e($n['date']) ?>"><?= e(nice_date($n['date'])) ?></time>
                    </div>
                    <h3 class="sk-card__title sk-h4"><?= e($n['title']) ?></h3>
<?php if ($long): ?>
                    <p><?= e(excerpt($text, 150)) ?></p>
                    <details class="sk-more">
                        <summary>Read more<span class="sk-visually-hidden"> about <?= e($n['title']) ?></span></summary>
                        <p><?= nl2br(e($text)) ?></p>
                    </details>
<?php else: ?>
                    <p><?= nl2br(e($text)) ?></p>
<?php endif; ?>
                </div>
            </article>
<?php endforeach; ?>
        </div>
<?php if (count($news) > $first_batch): ?>
        <div class="sk-actions sk-actions--center">
            <button type="button" class="sk-btn sk-btn--outline" id="news-more" data-step="<?= $first_batch ?>">
                Show more news &amp; events <span class="sk-muted-on-btn">(<span id="news-left"><?= count($news) - $first_batch ?></span> more)</span>
            </button>
        </div>
<?php endif; ?>
<?php endif; ?>
    </div>
</section>

<script>
// News / Events: filter buttons + "Show more" (12 cards at a time, counted within the chosen filter).
(function () {
    var cards = Array.prototype.slice.call(document.querySelectorAll('#news-grid .sk-news'));
    var more = document.getElementById('news-more');
    var step = more ? (parseInt(more.getAttribute('data-step'), 10) || 12) : cards.length;
    var want = 'all', shown = step;
    function render() {
        var match = cards.filter(function (c) { return want === 'all' || c.getAttribute('data-category') === want; });
        cards.forEach(function (c) { c.hidden = true; });
        match.forEach(function (c, i) { c.hidden = i >= shown; });
        if (more) {
            var left = match.length - shown;
            more.parentNode.hidden = left <= 0;
            document.getElementById('news-left').textContent = left;
        }
    }
    document.querySelectorAll('.sk-filter__btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            want = btn.getAttribute('data-filter');
            shown = step;
            document.querySelectorAll('.sk-filter__btn').forEach(function (b) { b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'); });
            render();
        });
    });
    if (more) more.addEventListener('click', function () { shown += step; render(); });
    // A link to one news item further down the list (news-events.php#news-25): show it.
    function openHash() {
        var target = /^#news-\d+$/.test(location.hash) && document.querySelector('#news-grid ' + location.hash);
        if (!target || !target.hidden) return;
        want = 'all';
        document.querySelectorAll('.sk-filter__btn').forEach(function (b) { b.setAttribute('aria-pressed', b.getAttribute('data-filter') === 'all' ? 'true' : 'false'); });
        shown = Math.ceil((cards.indexOf(target) + 1) / step) * step;
        render();
        target.scrollIntoView();
    }
    openHash();
    window.addEventListener('hashchange', openHash);
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
