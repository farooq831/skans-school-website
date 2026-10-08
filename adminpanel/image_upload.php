<?php
// adminpanel/image_upload.php : safe image upload used by the announcement pages.
// - Checks the real file type (not just the name), max 2 MB, JPG / PNG / WebP / GIF.
// - Saves under a random name in this campus's pictures folder (upload_dir(), includes/campus.php).
// - If the server has the GD image library, very large photos are scaled down to 1600 px
//   (shape kept exactly). Without GD the original is kept; the website still shows it correctly.

require_once dirname(__DIR__) . '/includes/campus.php';
define('UPLOAD_DIR', upload_dir());
const UPLOAD_MAX_BYTES = IMAGE_MAX_BYTES; // 2 MB, set in session.php
const UPLOAD_MAX_SIDE = 1600;

/**
 * Handle one uploaded image field.
 * Returns ['file' => 'abc123.jpg' or null, 'error' => message or null].
 * No file chosen and $required = false -> ['file' => null, 'error' => null].
 */
function handle_image_upload(string $field, bool $required = false): array
{
    $f = $_FILES[$field] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['file' => null, 'error' => $required ? 'Please choose a picture.' : null];
    }
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE || $f['size'] > UPLOAD_MAX_BYTES) {
        return ['file' => null, 'error' => 'The picture is too large. Maximum size is ' . IMAGE_MAX_LABEL . '.'];
    }
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        return ['file' => null, 'error' => 'The picture could not be uploaded. Please try again.'];
    }

    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $size = @getimagesize($f['tmp_name']);
    if (!isset($types[$mime]) || $size === false) {
        return ['file' => null, 'error' => 'Please choose a JPG, PNG, WebP or GIF picture.'];
    }

    $name = bin2hex(random_bytes(8)) . '.' . $types[$mime];
    $dest = UPLOAD_DIR . $name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        return ['file' => null, 'error' => 'The picture could not be saved. Please try again.'];
    }
    shrink_if_huge($dest, $mime, $size[0], $size[1]);
    return ['file' => $name, 'error' => null];
}

/** Scale very large photos down to UPLOAD_MAX_SIDE (keeps the exact shape). Needs GD; skipped otherwise. */
function shrink_if_huge(string $path, string $mime, int $w, int $h): void
{
    if (max($w, $h) <= UPLOAD_MAX_SIDE || !function_exists('imagecreatetruecolor')) {
        return;
    }
    $load = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'];
    if (!isset($load[$mime]) || !function_exists($load[$mime])) {
        return; // GIFs may be animated: leave them alone
    }
    $src = @$load[$mime]($path);
    if (!$src) {
        return;
    }
    $scale = UPLOAD_MAX_SIDE / max($w, $h);
    $nw = (int) round($w * $scale);
    $nh = (int) round($h * $scale);
    $dst = imagecreatetruecolor($nw, $nh);
    if ($mime !== 'image/jpeg') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    if ($mime === 'image/jpeg') {
        imagejpeg($dst, $path, 85);
    } elseif ($mime === 'image/png') {
        imagepng($dst, $path, 6);
    } elseif (function_exists('imagewebp')) {
        imagewebp($dst, $path, 85);
    }
    imagedestroy($src);
    imagedestroy($dst);
}
