<?php
// adminpanel/item_editor.php : one modern Add / Edit form for News & Events, Activities, Facilities and Faculty.
// Each add_*.php / edit_*.php page only describes its fields and calls item_editor_run().
// Same look and behaviour everywhere: messages on the page (no pop-ups), what you typed is kept after an
// error, live preview, character counters, picture size/pixels (upload_info.php), safe picture upload
// (image_upload.php: real file type, 2 MB, random name), monthly post limit where it applies.

require_once __DIR__ . '/admin_lib.php';
require_once __DIR__ . '/image_upload.php';

/** Text saved by older admin pages may be HTML-encoded ("&amp;"): show it as plain text in the form. */
function ie_plain($text): string
{
    $text = (string) $text;
    for ($i = 0; $i < 3; $i++) {
        $d = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($d === $text) break;
        $text = $d;
    }
    return $text;
}

/**
 * $cfg = [
 *   'table' => 'news_events', 'noun' => 'news post', 'list_url' => 'manage_news_events.php', 'list_label' => 'Manage News & Events',
 *   'heading_add' => 'Add News & Events', 'heading_edit' => 'Edit News & Event', 'intro' => '…',
 *   'fields' => [ column => ['label', 'type' => text|textarea|date|select, 'required', 'max', 'placeholder', 'help',
 *                            'options' => [value => label], 'int' => true, 'datalist' => [...], 'rows' => 5, 'default' => ''] ],
 *   'image'  => ['column' => 'image_url', 'required' => true, 'label' => 'Picture', 'help' => '…'],
 *   'preview' => ['title' => 'title', 'text' => 'description', 'meta' => 'category_id', 'date' => 'date', 'shape' => '4 / 3', 'label' => 'News'],
 *   'quota' => true,                          // counts toward the monthly post limit (add only)
 *   'insert_extra' => ['created_at' => 'NOW()'],
 * ]
 */
function item_editor_run(mysqli $conn, array $cfg, string $mode): void
{
    $is_edit = $mode === 'edit';
    $table = $cfg['table'];
    $fields = $cfg['fields'];
    $img = $cfg['image'] ?? null;
    $id = (int) ($_GET['id'] ?? 0);
    $errors = [];
    $row = null;

    if ($is_edit) {
        $stmt = $conn->prepare("SELECT * FROM `$table` WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) {
            header('Location: ' . $cfg['list_url'] . '?msg=' . urlencode('That ' . $cfg['noun'] . ' no longer exists.'));
            exit;
        }
    }

    $values = [];
    foreach ($fields as $col => $f) {
        $values[$col] = $row ? ie_plain($row[$col] ?? '') : (string) ($f['default'] ?? '');
    }
    $current_image = ($img && $row && !empty($row[$img['column']]) && is_file(UPLOAD_DIR . $row[$img['column']])) ? $row[$img['column']] : '';

    $quota = (!$is_edit && !empty($cfg['quota'])) ? post_quota($conn) : null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_require_csrf();
        if ($quota && $quota['full']) {
            $errors[] = 'Monthly limit reached: ' . $quota['used'] . ' of ' . $quota['limit'] . ' posts this month. You can add new posts again from ' . $quota['resets'] . '.';
        }
        foreach ($fields as $col => $f) {
            $v = trim((string) ($_POST[$col] ?? ''));
            $values[$col] = $v;
            $label = $f['label'];
            if (!empty($f['required']) && $v === '') {
                $errors[$col] = 'Please fill in ' . mb_strtolower($label) . '.';
            } elseif (!empty($f['max']) && mb_strlen($v) > $f['max']) {
                $errors[$col] = $label . ' is too long: ' . mb_strlen($v) . ' characters (maximum ' . $f['max'] . ').';
            } elseif (($f['type'] ?? '') === 'date' && $v !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                $errors[$col] = 'Please choose a valid date.';
            } elseif (($f['type'] ?? '') === 'select' && $v !== '' && !array_key_exists($v, $f['options'])) {
                $errors[$col] = 'Please choose ' . mb_strtolower($label) . ' from the list.';
            }
        }

        $new_image = null;
        if ($img && !$errors) {
            $up = handle_image_upload('image', !empty($img['required']) && $current_image === '');
            if ($up['error']) {
                $errors['image'] = $up['error'];
            } else {
                $new_image = $up['file'];
            }
        }

        if (!$errors) {
            $cols = array_keys($fields);
            $types = '';
            $params = [];
            foreach ($fields as $col => $f) {
                $types .= !empty($f['int']) ? 'i' : 's';
                $params[] = !empty($f['int']) ? (int) $values[$col] : $values[$col];
            }
            if ($img && ($new_image || !$is_edit)) {
                $cols[] = $img['column'];
                $types .= 's';
                $params[] = $new_image ?? '';
            }
            if ($is_edit) {
                $set = implode(', ', array_map(function ($c) { return "`$c` = ?"; }, $cols));
                $stmt = $conn->prepare("UPDATE `$table` SET $set WHERE id = ?");
                $types .= 'i';
                $params[] = $id;
            } else {
                $extra = $cfg['insert_extra'] ?? [];
                $all_cols = array_merge($cols, array_keys($extra));
                $holders = array_merge(array_fill(0, count($cols), '?'), array_values($extra)); // extras are fixed SQL like NOW()
                $stmt = $conn->prepare("INSERT INTO `$table` (`" . implode('`, `', $all_cols) . '`) VALUES (' . implode(', ', $holders) . ')');
            }
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $stmt->close();
                $done = $is_edit ? 'Changes saved' : (ucfirst($cfg['noun']) . ' added');
                header('Location: ' . $cfg['list_url'] . '?msg=' . urlencode($done . ': “' . mb_substr($values[$cfg['preview']['title']] ?? '', 0, 80) . '”.'));
                exit;
            }
            error_log("Saving $table failed: " . $stmt->error);
            $errors[] = 'The ' . $cfg['noun'] . ' could not be saved. Please try again.';
        }
    }

    $heading = $is_edit ? $cfg['heading_edit'] : $cfg['heading_add'];
    $pv = $cfg['preview'];
    $preview_src = upload_url($current_image);
    item_editor_render($cfg, $heading, $values, $errors, $quota, $preview_src, $is_edit);
}

function item_editor_render(array $cfg, string $heading, array $values, array $errors, ?array $quota, string $preview_src, bool $is_edit): void
{
    $fields = $cfg['fields'];
    $img = $cfg['image'] ?? null;
    $pv = $cfg['preview'];
    $locked = $quota && $quota['full'];
    $field_errors = array_filter($errors, 'is_string', ARRAY_FILTER_USE_KEY);
    $general_errors = array_filter($errors, 'is_int', ARRAY_FILTER_USE_KEY);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($heading) ?> | SKANS Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .ie-preview { border: 1px solid #E4DCCD; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 2px 12px rgba(25,42,74,.06); max-width: 380px; }
        .ie-media { position: relative; aspect-ratio: <?= h($pv['shape'] ?? '4 / 3') ?>; background: #EEF1F6; overflow: hidden; display: grid; place-items: center; }
        .ie-media::before { content: ""; position: absolute; inset: -20px; background-image: var(--img); background-size: cover; background-position: center; filter: blur(18px); opacity: .55; }
        .ie-media img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: <?= ($pv['fit'] ?? 'contain') === 'cover' ? 'cover' : 'contain' ?>; }
        .ie-media span { position: relative; color: #192A4A; font-family: "Times New Roman", serif; }
        .ie-body { padding: 14px 16px; border-top: 3px solid #BD852C; }
        .ie-meta { font-size: .78rem; color: #8A5F1C; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
        .ie-body h3 { font-family: "Times New Roman", serif; font-weight: 700; color: #192A4A; font-size: 1.2rem; margin: 4px 0 6px; overflow-wrap: anywhere; }
        .ie-body p { color: #3B4558; white-space: pre-line; margin: 0; font-size: .92rem; overflow-wrap: anywhere; }
    </style>
</head>
<body>
<?php include __DIR__ . '/nav.php'; ?>
<div class="container adm-page">
    <div class="adm-title">
        <div>
            <h1><?= h($heading) ?></h1>
<?php if (!empty($cfg['intro'])): ?><p class="adm-sub"><?= h($cfg['intro']) ?></p><?php endif; ?>
        </div>
        <a class="btn btn-outline-primary" href="<?= h($cfg['list_url']) ?>"><i class="fas fa-list"></i> <?= h($cfg['list_label']) ?></a>
    </div>

<?php if ($quota): ?><?= post_quota_banner($quota) ?><?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger" role="alert"><strong>Please fix the following:</strong>
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

    <div class="row">
        <div class="col-lg-7">
            <form method="POST" enctype="multipart/form-data" class="adm-card adm-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= h(admin_csrf_token()) ?>">
                <fieldset <?= $locked ? 'disabled' : '' ?>>
<?php foreach ($fields as $col => $f):
        $id = 'f_' . $col; $type = $f['type'] ?? 'text'; $invalid = isset($field_errors[$col]) ? ' is-invalid' : ''; ?>
                <div class="form-group">
                    <label for="<?= $id ?>"><?= h($f['label']) ?> <?= !empty($f['required']) ? '<span class="adm-help font-weight-normal">(required)</span>' : '<span class="adm-help font-weight-normal">(optional)</span>' ?></label>
<?php if ($type === 'textarea'): ?>
                    <textarea class="form-control<?= $invalid ?>" id="<?= $id ?>" name="<?= $col ?>" rows="<?= (int) ($f['rows'] ?? 5) ?>"<?= !empty($f['max']) ? ' maxlength="' . (int) $f['max'] . '"' : '' ?> placeholder="<?= h($f['placeholder'] ?? '') ?>" data-preview="<?= h($col) ?>"><?= h($values[$col]) ?></textarea>
<?php elseif ($type === 'select'): ?>
                    <select class="custom-select<?= $invalid ?>" id="<?= $id ?>" name="<?= $col ?>" data-preview="<?= h($col) ?>">
                        <option value="">Choose…</option>
<?php foreach ($f['options'] as $v => $label): ?>
                        <option value="<?= h($v) ?>"<?= (string) $values[$col] === (string) $v ? ' selected' : '' ?>><?= h($label) ?></option>
<?php endforeach; ?>
                    </select>
<?php else: ?>
                    <input class="form-control<?= $invalid ?>" id="<?= $id ?>" name="<?= $col ?>" type="<?= $type === 'date' ? 'date' : 'text' ?>"<?= !empty($f['max']) && $type !== 'date' ? ' maxlength="' . (int) $f['max'] . '"' : '' ?> value="<?= h($values[$col]) ?>" placeholder="<?= h($f['placeholder'] ?? '') ?>"<?= !empty($f['datalist']) ? ' list="dl_' . $col . '"' : '' ?> data-preview="<?= h($col) ?>">
<?php if (!empty($f['datalist'])): ?>
                    <datalist id="dl_<?= $col ?>"><?php foreach ($f['datalist'] as $o): ?><option value="<?= h($o) ?>"><?php endforeach; ?></datalist>
<?php endif; ?>
<?php endif; ?>
<?php if (isset($field_errors[$col])): ?><div class="invalid-feedback"><?= h($field_errors[$col]) ?></div><?php endif; ?>
<?php if (!empty($f['help'])): ?><small class="form-text adm-help"><?= h($f['help']) ?></small><?php endif; ?>
                </div>
<?php endforeach; ?>
<?php if ($img): ?>
                <div class="form-group">
                    <label for="f_image"><?= h($img['label'] ?? 'Picture') ?> <span class="adm-help font-weight-normal"><?= (!empty($img['required']) && !$is_edit) ? '(required)' : ($is_edit ? '(choose a file only to replace the current picture)' : '(optional)') ?></span></label>
                    <input type="file" class="form-control-file<?= isset($field_errors['image']) ? ' is-invalid' : '' ?>" id="f_image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
<?php if (isset($field_errors['image'])): ?><div class="invalid-feedback d-block"><?= h($field_errors['image']) ?></div><?php endif; ?>
<?php if (!empty($img['help'])): ?><small class="form-text adm-help"><?= h($img['help']) ?></small><?php endif; ?>
                </div>
<?php endif; ?>
                <div class="d-flex flex-wrap align-items-center" style="gap:.5rem">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-floppy-disk"></i> <?= $is_edit ? 'Save changes' : 'Publish' ?></button>
                    <a href="<?= h($cfg['list_url']) ?>" class="btn btn-link">Cancel</a>
                </div>
                </fieldset>
            </form>
        </div>
        <div class="col-lg-5">
            <div class="adm-card">
                <h2><i class="fas fa-eye"></i> Preview</h2>
                <div class="ie-preview" aria-live="polite">
<?php if ($img): ?>
                    <div class="ie-media" id="pv-media" style="--img: url('<?= h($preview_src) ?>')">
                        <img id="pv-img" src="<?= h($preview_src) ?>" alt="" <?= $preview_src ? '' : 'hidden' ?>>
                        <span id="pv-empty" <?= $preview_src ? 'hidden' : '' ?>>No picture yet</span>
                    </div>
<?php endif; ?>
                    <div class="ie-body">
                        <div class="ie-meta" id="pv-meta"><?= h($pv['label'] ?? '') ?></div>
                        <h3 id="pv-title"><?= h($values[$pv['title']] ?: 'Title') ?></h3>
                        <p id="pv-text"><?= h(!empty($pv['text']) ? ($values[$pv['text']] ?: 'Text') : '') ?></p>
                    </div>
                </div>
                <p class="adm-help mt-2 mb-0">A simplified view of how it appears on the website.</p>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var pv = <?= json_encode(['title' => $pv['title'], 'text' => $pv['text'] ?? null, 'meta' => $pv['meta'] ?? null, 'date' => $pv['date'] ?? null, 'label' => $pv['label'] ?? '']) ?>;
    function val(col) { var el = document.querySelector('[name="' + col + '"]'); if (!el) return ''; return el.tagName === 'SELECT' ? (el.value ? el.options[el.selectedIndex].text : '') : el.value; }
    function update() {
        document.getElementById('pv-title').textContent = val(pv.title).trim() || 'Title';
        if (pv.text) document.getElementById('pv-text').textContent = val(pv.text).trim() || 'Text';
        var meta = [];
        if (pv.meta) meta.push(val(pv.meta) || pv.label); else if (pv.label) meta.push(pv.label);
        if (pv.date && val(pv.date)) { var d = new Date(val(pv.date)); if (!isNaN(d)) meta.push(d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })); }
        document.getElementById('pv-meta').textContent = meta.join(' · ');
    }
    document.querySelectorAll('[data-preview]').forEach(function (el) { el.addEventListener('input', update); el.addEventListener('change', update); });
    var file = document.getElementById('f_image');
    if (file) file.addEventListener('change', function () {
        var f = file.files[0], img = document.getElementById('pv-img'), empty = document.getElementById('pv-empty'), media = document.getElementById('pv-media');
        if (!f || f.size > <?= (int) IMAGE_MAX_BYTES ?> || !/^image\//.test(f.type)) return; // too big / not a picture: upload_info.php shows the message
        var r = new FileReader();
        r.onload = function (e) { img.src = e.target.result; img.hidden = false; empty.hidden = true; media.style.setProperty('--img', 'url("' + e.target.result + '")'); };
        r.readAsDataURL(f);
    });
    update();
})();
</script>
<?php include __DIR__ . '/char_limit.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
