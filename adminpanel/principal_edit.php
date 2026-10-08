<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/admin_lib.php';
require_once __DIR__ . '/image_upload.php';
// Edit the Principal's message on the About page: photo, heading, message and sign-off.
// Saved in the database (site_settings 'principal'); "Restore original" goes back to the
// original text and photo (principal_defaults() in includes/settings.php).
// Old photos are never deleted from the pictures folder.

$defaults = principal_defaults();
$defaults['paragraphs'] = array_map(function ($t) use ($conn) { return campus_text($t, admin_site($conn)); }, $defaults['paragraphs']);
$saved = settings_all($conn)['principal'] ?? [];
$current = array_merge($defaults, is_array($saved) ? array_intersect_key($saved, $defaults) : []);
$values = [
    'heading'   => $current['heading'],
    'message'   => implode("\n\n", (array) $current['paragraphs']),
    'name'      => $current['name'],
    'title'     => $current['title'],
    'photo_alt' => $current['photo_alt'],
    'photo_pos' => $current['photo_pos'],
];
$photo = (string) $current['photo'];
$errors = [];
$msg = $_GET['msg'] ?? '';
$positions = ['top' => 'Top (keep the face in view)', 'center' => 'Centre', 'bottom' => 'Bottom'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_csrf();
    if (isset($_POST['restore'])) {
        settings_delete($conn, 'principal');
        header('Location: principal_edit.php?msg=restored');
        exit;
    }
    foreach ($values as $k => $_) {
        $values[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    if ($values['heading'] === '') $errors['heading'] = 'Please enter a heading.';
    elseif (mb_strlen($values['heading']) > 120) $errors['heading'] = 'The heading is too long (max 120 characters).';
    if ($values['title'] === '') $errors['title'] = 'Please enter the title, e.g. Principal.';
    if (mb_strlen($values['name']) > 100) $errors['name'] = 'The name is too long (max 100 characters).';
    if (mb_strlen($values['title']) > 100) $errors['title'] = 'The title is too long (max 100 characters).';
    if (mb_strlen($values['photo_alt']) > 200) $errors['photo_alt'] = 'The description is too long (max 200 characters).';
    if (!isset($positions[$values['photo_pos']])) $values['photo_pos'] = 'center';

    // Paragraphs are separated by an empty line.
    $paragraphs = array_values(array_filter(array_map(function ($p) {
        return trim(preg_replace('/\s*\R\s*/', ' ', $p));
    }, preg_split('/\R\s*\R/', $values['message'])), 'strlen'));
    if (!$paragraphs) $errors['message'] = 'Please write the message.';
    elseif (mb_strlen($values['message']) > 3000) $errors['message'] = 'The message is too long (max 3000 characters).';

    if (!empty($_POST['remove_photo'])) {
        $photo = ''; // back to the original photo (the uploaded file stays on the server)
    }
    $up = handle_image_upload('photo');
    if ($up['error']) $errors['photo'] = $up['error'];
    elseif ($up['file']) $photo = $up['file'];

    if (!$errors) {
        settings_save($conn, 'principal', [
            'heading'    => $values['heading'],
            'paragraphs' => $paragraphs,
            'name'       => $values['name'],
            'title'      => $values['title'],
            'photo'      => $photo,
            'photo_alt'  => $values['photo_alt'] !== '' ? $values['photo_alt'] : ($photo === '' ? $defaults['photo_alt'] : 'The Principal of ' . admin_site($conn)['full_name']),
            'photo_pos'  => $values['photo_pos'],
        ]);
        header('Location: principal_edit.php?msg=saved');
        exit;
    }
}
$photo_src = upload_url($photo) ?: '../assets/img/about-principal.jpg';
$is_custom = !empty($saved);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal's Message | SKANS Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .pm-photo { aspect-ratio: 3 / 4; max-width: 260px; border-radius: 12px; overflow: hidden; background: #F4F1EA; box-shadow: 0 2px 12px rgba(25, 42, 74, .12); }
        .pm-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .pm-preview h3 { font-family: "Times New Roman", Times, serif; font-weight: 700; color: #192A4A; font-size: 1.5rem; margin: .25rem 0 .5rem; }
        .pm-preview .pm-eyebrow { text-transform: uppercase; letter-spacing: .12em; font-size: .72rem; font-weight: 700; color: #8A5E17; }
        .pm-preview .pm-text p { font-size: .9rem; color: #3B4558; margin-bottom: .6rem; }
        .pm-preview .pm-sign { font-family: "Times New Roman", Times, serif; font-weight: 700; color: #192A4A; }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container adm-page">
    <div class="adm-title">
        <div>
            <h1>Principal's message</h1>
            <p class="adm-sub">Shown at the top of the website's <strong>About Us</strong> page. Changes show on the website immediately.</p>
        </div>
        <a class="btn btn-outline-primary" href="../about.php#principal" target="_blank" rel="noopener"><i class="fas fa-eye"></i> View on website</a>
    </div>

<?php if ($msg === 'saved'): ?>
    <div class="alert alert-success"><i class="fas fa-check"></i> Principal's message saved. It is now shown on the About Us page.</div>
<?php elseif ($msg === 'restored'): ?>
    <div class="alert alert-success"><i class="fas fa-rotate-left"></i> The original message and photo have been restored.</div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger">Please check the fields marked in red.</div>
<?php endif; ?>
    <p class="adm-help"><?= $is_custom ? '<span class="adm-badge adm-badge--new">Edited</span> The website shows your edited version.' : '<span class="adm-badge">Original</span> The website shows the original message and photo.' ?></p>

    <div class="row">
        <div class="col-lg-7">
            <form method="POST" enctype="multipart/form-data" class="adm-card adm-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= h(admin_csrf_token()) ?>">

                <h2><i class="fas fa-image"></i> Photo</h2>
                <div class="form-group">
                    <label for="photo">New photo <span class="adm-help font-weight-normal">(choose a file only to replace the current photo)</span></label>
                    <input type="file" class="form-control-file<?= isset($errors['photo']) ? ' is-invalid' : '' ?>" id="photo" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
<?php if (isset($errors['photo'])): ?><div class="invalid-feedback d-block"><?= h($errors['photo']) ?></div><?php endif; ?>
                    <small class="form-text adm-help">A portrait photo (taller than wide) looks best; it is shown in a 3 × 4 frame.</small>
                </div>
                <div class="form-group">
                    <label for="photo_pos">Which part of the photo to keep in the frame</label>
                    <select class="custom-select" id="photo_pos" name="photo_pos">
<?php foreach ($positions as $v => $label): ?>
                        <option value="<?= $v ?>"<?= $values['photo_pos'] === $v ? ' selected' : '' ?>><?= h($label) ?></option>
<?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="photo_alt">Photo description <span class="adm-help font-weight-normal">(optional, for blind visitors and Google)</span></label>
                    <input class="form-control<?= isset($errors['photo_alt']) ? ' is-invalid' : '' ?>" id="photo_alt" name="photo_alt" maxlength="200" value="<?= h($values['photo_alt']) ?>" placeholder="e.g. Mrs Ayesha Khan, Principal of SKANS AECHS Campus">
<?php if (isset($errors['photo_alt'])): ?><div class="invalid-feedback"><?= h($errors['photo_alt']) ?></div><?php endif; ?>
                </div>
<?php if ($photo !== ''): ?>
                <div class="custom-control custom-checkbox mb-3">
                    <input type="checkbox" class="custom-control-input" id="remove_photo" name="remove_photo" value="1">
                    <label class="custom-control-label" for="remove_photo">Go back to the original photo</label>
                </div>
<?php endif; ?>

                <h2 class="mt-4"><i class="fas fa-message"></i> Message</h2>
                <div class="form-group">
                    <label for="heading">Heading <span class="adm-help font-weight-normal">(required)</span></label>
                    <input class="form-control<?= isset($errors['heading']) ? ' is-invalid' : '' ?>" id="heading" name="heading" maxlength="120" value="<?= h($values['heading']) ?>" placeholder="e.g. Greetings From the Principal">
<?php if (isset($errors['heading'])): ?><div class="invalid-feedback"><?= h($errors['heading']) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="message">Message <span class="adm-help font-weight-normal">(required)</span></label>
                    <textarea class="form-control<?= isset($errors['message']) ? ' is-invalid' : '' ?>" id="message" name="message" rows="12" maxlength="3000" placeholder="Welcome to our SKANS School System - AECHS Campus…"><?= h($values['message']) ?></textarea>
<?php if (isset($errors['message'])): ?><div class="invalid-feedback"><?= h($errors['message']) ?></div><?php endif; ?>
                    <small class="form-text adm-help">Leave an <strong>empty line</strong> between paragraphs. <span id="para-count"></span></small>
                </div>

                <h2 class="mt-4"><i class="fas fa-signature"></i> Signed by</h2>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="name">Principal's name <span class="adm-help font-weight-normal">(optional)</span></label>
                        <input class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" id="name" name="name" maxlength="100" value="<?= h($values['name']) ?>" placeholder="e.g. Mrs Ayesha Khan">
<?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= h($errors['name']) ?></div><?php endif; ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="title">Title <span class="adm-help font-weight-normal">(required)</span></label>
                        <input class="form-control<?= isset($errors['title']) ? ' is-invalid' : '' ?>" id="title" name="title" maxlength="100" value="<?= h($values['title']) ?>" placeholder="e.g. Principal">
<?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= h($errors['title']) ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center" style="gap:.5rem">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-floppy-disk"></i> Save</button>
                    <a href="admin_panel.php" class="btn btn-link">Cancel</a>
<?php if ($is_custom): ?>
                    <button type="submit" name="restore" value="1" class="btn btn-outline-danger ml-auto" onclick="return confirm('Go back to the original message and photo? Your edits will be removed.');"><i class="fas fa-rotate-left"></i> Restore original</button>
<?php endif; ?>
                </div>
            </form>
        </div>
        <div class="col-lg-5">
            <div class="adm-card pm-preview">
                <h2><i class="fas fa-eye"></i> Preview</h2>
                <div class="pm-photo mb-3"><img id="pv-img" src="<?= h($photo_src) ?>" alt="" style="object-position: center <?= h($values['photo_pos']) ?>"></div>
                <div class="pm-eyebrow">A message from the Principal</div>
                <h3 id="pv-heading"><?= h($values['heading']) ?></h3>
                <div class="pm-text" id="pv-text"></div>
                <p class="pm-sign mb-0">Regards,<br><span id="pv-sign"></span></p>
                <p class="adm-help mt-2 mb-0">A simplified view of how it appears on the About Us page.</p>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var original = $('pv-img').src;
    function paras() { return $('message').value.split(/\n\s*\n/).map(function (p) { return p.replace(/\s*\n\s*/g, ' ').trim(); }).filter(Boolean); }
    function update() {
        $('pv-heading').textContent = $('heading').value.trim() || 'Heading';
        var box = $('pv-text'); box.innerHTML = '';
        paras().forEach(function (t) { var p = document.createElement('p'); p.textContent = t; box.appendChild(p); });
        var n = paras().length; $('para-count').textContent = n + (n === 1 ? ' paragraph' : ' paragraphs') + ' on the website.';
        var sign = $('pv-sign'); sign.textContent = '';
        if ($('name').value.trim()) { sign.appendChild(document.createTextNode($('name').value.trim())); sign.appendChild(document.createElement('br')); }
        sign.appendChild(document.createTextNode($('title').value.trim() || 'Principal'));
        $('pv-img').style.objectPosition = 'center ' + $('photo_pos').value;
    }
    ['heading', 'message', 'name', 'title', 'photo_pos'].forEach(function (id) { $(id).addEventListener('input', update); $(id).addEventListener('change', update); });
    $('photo').addEventListener('change', function () {
        var f = this.files && this.files[0];
        if (f && /^image\//.test(f.type)) { var r = new FileReader(); r.onload = function () { $('pv-img').src = r.result; }; r.readAsDataURL(f); }
        else { $('pv-img').src = original; }
    });
    var rm = $('remove_photo');
    if (rm) rm.addEventListener('change', function () { $('pv-img').src = this.checked ? '../assets/img/about-principal.jpg' : original; });
    update();
})();
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
