<?php
// includes/photo-card.php : card with a photo, a title underneath, and a round arrow button that
// opens a details card over the photo. Used for faculty and facilities (same look and behaviour).

/**
 * $o = [
 *   'id'       => unique id for the details card (string),
 *   'image'    => file name in this campus's pictures folder ('' = no photo),
 *   'title'    => text under the photo, 'subtitle' => small gold line under the title,
 *   'details'  => HTML inside the details card (already escaped),
 *   'class'    => extra class on the card (optional), 'hidden' => true to hide at first (optional),
 *   'fit'      => 'auto' (default): portrait and square photos fill the frame like the faculty
 *                 photos; clearly wide photos are shown whole on a soft blurred copy of themselves.
 *                 'cover' always fills the frame (faculty portraits).
 * ]
 */
function photo_card(array $o): string
{
    $file = (string) ($o['image'] ?? '');
    $path = upload_dir() . basename($file);
    $src = upload_url($file);
    $fit = 'cover';
    if (($o['fit'] ?? 'auto') !== 'cover' && $src !== '' && photo_ratio($path) > 1.2) {
        $fit = 'contain'; // wide photo: show it whole instead of cutting off the sides
    }
    $title = e($o['title'] ?? '');
    $id = e($o['id']);

    ob_start(); ?>
    <article class="sk-teacher<?= !empty($o['class']) ? ' ' . e($o['class']) : '' ?>"<?= !empty($o['hidden']) ? ' hidden' : '' ?>>
        <div class="sk-teacher__media<?= $fit === 'contain' ? ' sk-teacher__media--contain' : '' ?>"<?= $fit === 'contain' ? ' style="--img: url(\'' . e($src) . '\')"' : '' ?>>
<?php if ($src !== ''): ?>
            <img src="<?= e($src) ?>" alt="<?= $title ?>" loading="lazy" width="600" height="800">
<?php else: ?>
            <div class="sk-teacher__noimg" aria-hidden="true"><?= e(mb_substr(trim((string) ($o['title'] ?? '')), 0, 1)) ?></div>
<?php endif; ?>
            <div class="sk-teacher__panel" id="<?= $id ?>" role="region" aria-label="About <?= $title ?>" hidden>
                <button type="button" class="sk-teacher__close" data-teacher-close aria-label="Close details">✕</button>
                <p class="sk-teacher__panel-name"><?= $title ?></p>
<?php if (!empty($o['subtitle'])): ?>
                <p class="sk-teacher__panel-role"><?= e($o['subtitle']) ?></p>
<?php endif; ?>
                <?= $o['details'] ?? '' ?>
            </div>
            <button type="button" class="sk-teacher__toggle" data-teacher-toggle aria-expanded="false" aria-controls="<?= $id ?>">
                <span class="sk-visually-hidden">View details for <?= $title ?></span>
                <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M7 17 17 7M9 7h8v8" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
        <div class="sk-teacher__body">
            <h3 class="sk-teacher__name"><?= $title ?></h3>
<?php if (!empty($o['subtitle'])): ?>
            <p class="sk-teacher__role"><?= e($o['subtitle']) ?></p>
<?php endif; ?>
        </div>
    </article>
<?php
    return ob_get_clean();
}

/** Width / height as the photo is DISPLAYED (phone photos saved sideways carry a rotation note). */
function photo_ratio(string $path): float
{
    $size = @getimagesize($path);
    if (!$size || !$size[1]) {
        return 1.0;
    }
    [$w, $h] = $size;
    if (function_exists('exif_read_data') && ($size[2] ?? 0) === IMAGETYPE_JPEG) {
        $exif = @exif_read_data($path);
        if (!empty($exif['Orientation']) && in_array((int) $exif['Orientation'], [5, 6, 7, 8], true)) {
            [$w, $h] = [$h, $w]; // rotated by 90 degrees when shown
        }
    }
    return $w / $h;
}

/** Text saved by older admin pages may be HTML-encoded ("&amp;"): turn it back into plain text. */
function plain_text($text): string
{
    $text = (string) $text;
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) {
            break;
        }
        $text = $decoded;
    }
    return trim($text);
}
