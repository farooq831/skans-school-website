<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/admin_lib.php';
// Settings: school & campus name, contact details, social media links, monthly post limit.
// Saved in the database (site_settings); the website uses them immediately.

$fields = [
    // key            label                               placeholder                                                   type      group
    'school_name'  => ['School name',                     'SKANS School System',                                         'text',  'identity'],
    'campus_name'  => ['Campus name',                     'AECHS Campus',                                                'text',  'identity'],
    'classes_line' => ['Classes offered (short line)',    'Playgroup – Matric',                                          'text',  'identity'],
    'address'      => ['Address',                         '337 Street 17, Sector 1, Airport Employees CHS, Rawalpindi',  'textarea', 'contact'],
    'map_url'      => ['Google Maps link (location)',     'https://maps.app.goo.gl/…',                                   'url',   'contact'],
    'email'        => ['Email',                           'aechs.campus@skansschool.edu.pk',                             'email', 'contact'],
    'phone_office' => ['Office phone',                    '+92 51 8468475',                                              'tel',   'contact'],
    'phone_mobile' => ['Mobile phone',                    '+92 333 5352965',                                             'tel',   'contact'],
    'whatsapp'     => ['WhatsApp number',                 '923335352965 (country code, no + or spaces)',                 'tel',   'contact'],
    'facebook'     => ['Facebook page link',              'https://www.facebook.com/…',                                  'url',   'social'],
    'instagram'    => ['Instagram link',                  'https://www.instagram.com/…',                                 'url',   'social'],
    'youtube'      => ['YouTube channel link (optional)', 'https://www.youtube.com/@…',                                  'url',   'social'],
    'tiktok'       => ['TikTok link (optional)',          'https://www.tiktok.com/@…',                                   'url',   'social'],
];
$required = ['school_name', 'campus_name', 'address', 'email', 'phone_office', 'phone_mobile', 'whatsapp'];

$site = admin_site($conn);
$current = [
    'school_name' => $site['name'], 'campus_name' => $site['campus'], 'classes_line' => $site['classes'],
    'address' => $site['address'], 'map_url' => $site['map_url'], 'email' => $site['email'],
    'phone_office' => $site['phones'][0]['display'] ?? '', 'phone_mobile' => $site['phones'][1]['display'] ?? '',
    'whatsapp' => $site['whatsapp'], 'facebook' => $site['facebook'] ?? '', 'instagram' => $site['instagram'] ?? '',
    'youtube' => $site['youtube'] ?? '', 'tiktok' => $site['tiktok'] ?? '',
    'post_limit' => post_limit($conn),
];
$values = $current;
$errors = [];
$saved = isset($_GET['saved']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_csrf();
    foreach ($fields as $key => $f) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $values['post_limit'] = (int) ($_POST['post_limit'] ?? 8);

    foreach ($required as $key) {
        if ($values[$key] === '') {
            $errors[$key] = 'Please fill in ' . strtolower($fields[$key][0]) . '.';
        }
    }
    foreach ($fields as $key => $f) {
        if ($values[$key] === '' || isset($errors[$key])) continue;
        if (mb_strlen($values[$key]) > 300) $errors[$key] = 'Too long (max 300 characters).';
        elseif ($f[2] === 'url' && !preg_match('~^https://[^\s]+$~i', $values[$key])) $errors[$key] = 'Please enter a full link starting with https://';
        elseif ($f[2] === 'email' && !filter_var($values[$key], FILTER_VALIDATE_EMAIL)) $errors[$key] = 'Please enter a valid email address.';
    }
    foreach (['phone_office', 'phone_mobile'] as $key) {
        if (!isset($errors[$key]) && $values[$key] !== '' && !preg_match('/^\+?[0-9 ()-]{7,20}$/', $values[$key])) {
            $errors[$key] = 'Please enter a phone number, e.g. ' . $fields[$key][1] . '.';
        }
    }
    if (!isset($errors['whatsapp']) && !preg_match('/^[0-9]{10,15}$/', $values['whatsapp'])) {
        $errors['whatsapp'] = 'Digits only with country code, e.g. 923335352965.';
    }
    if ($values['post_limit'] < 1 || $values['post_limit'] > 100) {
        $errors['post_limit'] = 'Please choose a number from 1 to 100.';
    }

    if (!$errors) {
        foreach ($fields as $key => $f) {
            settings_save($conn, $key, $values[$key]);
        }
        settings_save($conn, 'post_limit', $values['post_limit']);
        header('Location: settings.php?saved=1');
        exit;
    }
}

function field(string $key, array $f, array $values, array $errors): string
{
    [$label, $placeholder, $type] = $f;
    $id = 'f_' . $key;
    $invalid = isset($errors[$key]) ? ' is-invalid' : '';
    $html = '<div class="form-group"><label for="' . $id . '">' . h($label) . '</label>';
    if ($type === 'textarea') {
        $html .= '<textarea class="form-control' . $invalid . '" id="' . $id . '" name="' . $key . '" rows="2" placeholder="' . h($placeholder) . '">' . h($values[$key]) . '</textarea>';
    } else {
        $html .= '<input class="form-control' . $invalid . '" id="' . $id . '" name="' . $key . '" type="' . ($type === 'url' ? 'url' : ($type === 'email' ? 'email' : 'text')) . '" placeholder="' . h($placeholder) . '" value="' . h($values[$key]) . '">';
    }
    if (isset($errors[$key])) $html .= '<div class="invalid-feedback">' . h($errors[$key]) . '</div>';
    return $html . '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | SKANS Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container adm-page">
    <div class="adm-title">
        <div>
            <h1>School &amp; contact details</h1>
            <p class="adm-sub">These details appear in the website header, footer, Contact page and WhatsApp button. Changes show on the website immediately.</p>
        </div>
        <div class="d-flex flex-wrap" style="gap:.5rem">
            <a class="btn btn-outline-primary" href="admission_policy_edit.php"><i class="fas fa-file-signature"></i> Edit admission policy</a>
            <a class="btn btn-outline-primary" href="principal_edit.php"><i class="fas fa-user-tie"></i> Principal's message</a>
            <a class="btn btn-outline-primary" href="classes_edit.php"><i class="fas fa-children"></i> Classes &amp; ages</a>
        </div>
    </div>

<?php if ($saved): ?>
    <div class="alert alert-success"><i class="fas fa-check"></i> Settings saved. <a href="../contact-us.php" target="_blank" rel="noopener">See the Contact page</a></div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger">Please check the fields marked in red.</div>
<?php endif; ?>

    <form method="POST" class="adm-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(admin_csrf_token()) ?>">
        <div class="row">
            <div class="col-lg-6">
                <div class="adm-card">
                    <h2><i class="fas fa-school"></i> School identity</h2>
<?php foreach ($fields as $k => $f) if ($f[3] === 'identity') echo field($k, $f, $values, $errors); ?>
                    <p class="adm-help mb-0">The full name is shown as "<?= h($values['school_name']) ?>, <?= h($values['campus_name']) ?>".</p>
                </div>
                <div class="adm-card">
                    <h2><i class="fas fa-share-nodes"></i> Social media</h2>
<?php foreach ($fields as $k => $f) if ($f[3] === 'social') echo field($k, $f, $values, $errors); ?>
                    <p class="adm-help mb-0">Leave optional links empty to hide them on the website.</p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="adm-card">
                    <h2><i class="fas fa-location-dot"></i> Location &amp; contact</h2>
<?php foreach ($fields as $k => $f) if ($f[3] === 'contact') echo field($k, $f, $values, $errors); ?>
                    <p class="adm-help mb-0">Google Maps link: open the school in Google Maps → Share → Copy link.</p>
                </div>
                <div class="adm-card">
                    <h2><i class="fas fa-gauge-high"></i> Posting limit</h2>
                    <div class="form-group">
                        <label for="f_post_limit">News/events + activities allowed per month</label>
                        <input class="form-control<?= isset($errors['post_limit']) ? ' is-invalid' : '' ?>" id="f_post_limit" name="post_limit" type="number" min="1" max="100" placeholder="8" value="<?= (int) $values['post_limit'] ?>" style="max-width: 140px">
<?php if (isset($errors['post_limit'])): ?><div class="invalid-feedback"><?= h($errors['post_limit']) ?></div><?php endif; ?>
                        <small class="form-text">Counted per calendar month. When the limit is reached, adding is blocked until the 1st of next month; editing and deleting still work. Announcements are not counted.</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="adm-sticky">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-floppy-disk"></i> Save settings</button>
            <a href="admin_panel.php" class="btn btn-link">Cancel</a>
        </div>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
