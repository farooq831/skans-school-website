<?php
// includes/announcement-popup.php : pop-up with the latest current announcement (Home page).
// Opens shortly after the page loads; closes with ✕, Esc or a click outside the box.
// Shown once per visit for each announcement (remembered in the browser for that visit only).
// Nothing is shown when there is no announcement from the last 30 days.
require_once __DIR__ . '/announcements.php';
require_once __DIR__ . '/photo-card.php'; // plain_text()

include SK_ROOT . '/adminpanel/db.php'; // earlier sections of the old Home page close the connection
$popup = (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) ? (current_announcements($conn, $site, 1)[0] ?? null) : null;
if (!$popup) {
    return;
}
$popup_img = announcement_image($popup['image']);
$popup_text = plain_text($popup['announcement_text']);
$popup_long = mb_strlen($popup_text) > 260;
?>
<dialog class="sk-popup sk-part" id="sk-ann-popup" aria-labelledby="sk-popup-title"
        data-key="skans-ann-<?= (int) $popup['id'] ?>-<?= e(strtotime($popup['created_at'])) ?>">
    <div class="sk-popup__inner<?= $popup_img === '' ? ' sk-popup__inner--text' : '' ?>">
        <button type="button" class="sk-popup__close" data-popup-close aria-label="Close announcement">
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
        </button>
<?php if ($popup_img !== ''): ?>
        <div class="sk-media sk-popup__media" style="--img: url('<?= e($popup_img) ?>')">
            <img src="<?= e($popup_img) ?>" alt="<?= e(plain_text($popup['heading'])) ?>">
        </div>
<?php endif; ?>
        <div class="sk-popup__body">
            <span class="sk-badge">Announcement</span>
            <time class="sk-date" datetime="<?= e(date('Y-m-d', strtotime($popup['created_at']))) ?>"><?= e(nice_date($popup['created_at'])) ?></time>
            <h2 class="sk-popup__title" id="sk-popup-title"><?= e(plain_text($popup['heading'])) ?></h2>
            <p class="sk-popup__text"><?= nl2br(e($popup_long ? excerpt($popup_text, 260) : $popup_text)) ?></p>
            <div class="sk-actions">
                <a class="sk-btn sk-btn--primary" href="news-events.php#announcements"><?= $popup_long ? 'Read the full announcement' : 'View all announcements' ?></a>
                <button type="button" class="sk-btn sk-btn--outline" data-popup-close>Close</button>
            </div>
        </div>
    </div>
</dialog>
