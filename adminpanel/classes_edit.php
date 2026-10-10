<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/admin_lib.php';
// Settings > Classes & ages: the classes THIS campus teaches, the minimum age for each, and which level
// card (Early Years / Juniors / Seniors) it belongs to. Used by the age table (Admissions page), the
// level cards, the Fee Structure (one row per class) and texts like "Playgroup to Class 8".
// Saved in site_settings 'class_list'; "Restore original" goes back to the classes in data/site.php.

$site_defaults = require SK_ROOT . '/data/site.php';
// Level cards to choose from: 6 slots = Academic Programs #1-#6 (their names), else the default names.
$level_names = array_map(function ($l) { return $l['name']; }, $site_defaults['levels']); // 0, 1, 2
for ($i = count($level_names); $i < 6; $i++) {
    $level_names[$i] = 'Card ' . ($i + 1);
}
$ap = @$conn->query('SELECT id, name FROM academic_prog WHERE id BETWEEN 1 AND 6');
while ($ap && ($a = $ap->fetch_assoc())) {
    if (trim((string) $a['name']) !== '') {
        $level_names[(int) $a['id'] - 1] = 'Card ' . (int) $a['id'] . ': ' . trim($a['name']);
    }
}
$saved = settings_all($conn)['class_list'] ?? null;
$is_custom = is_array($saved) && $saved;
$rows = campus_class_list($conn);
$errors = [];
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_csrf();
    if (isset($_POST['restore'])) {
        settings_delete($conn, 'class_list');
        header('Location: classes_edit.php?msg=restored');
        exit;
    }
    $rows = [];
    $seen = [];
    $names = (array) ($_POST['class'] ?? []);
    foreach ($names as $i => $name) {
        $name = trim(preg_replace('/\s+/u', ' ', (string) $name));
        $age = trim((string) ($_POST['age'][$i] ?? ''));
        $level = (int) ($_POST['level'][$i] ?? 0);
        if ($name === '' && $age === '') {
            continue;                                   // empty row: ignored
        }
        if ($name === '') {
            $errors[] = 'Row ' . ($i + 1) . ': please type the class name.';
        } elseif (isset($seen[mb_strtolower($name)])) {
            $errors[] = '"' . $name . '" is in the list twice.';
        }
        if (mb_strlen($name) > 40 || mb_strlen($age) > 40) {
            $errors[] = 'Row ' . ($i + 1) . ': too long (max 40 characters).';
        }
        if (!isset($level_names[$level])) {
            $level = 0;
        }
        $seen[mb_strtolower($name)] = true;
        $rows[] = ['class' => mb_substr($name, 0, 40), 'age' => mb_substr($age, 0, 40), 'level' => $level];
    }
    if (!$rows) {
        $errors[] = 'Please keep at least one class.';
    }
    if (count($rows) > 30) {
        $errors[] = 'At most 30 classes.';
    }
    if (!$errors) {
        settings_save($conn, 'class_list', $rows);
        header('Location: classes_edit.php?msg=saved');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes &amp; Ages | SKANS Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .cls-table td { vertical-align: middle; }
        .cls-table .form-control, .cls-table .custom-select { min-width: 120px; }
        .cls-num { width: 2.5rem; color: #6b7487; }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container adm-page">
    <div class="adm-title">
        <div>
            <h1>Classes &amp; ages</h1>
            <p class="adm-sub">The classes taught at <strong>this campus</strong>, in order from youngest to oldest. Used on the website for the
                <strong>age table</strong> (Admissions), the <strong>3 level cards</strong>, the <strong>Fee Structure</strong> (one row per class) and texts such as “Playgroup to Class 8”.</p>
        </div>
        <a class="btn btn-outline-primary" href="../admissions.php" target="_blank" rel="noopener"><i class="fas fa-eye"></i> View on website</a>
    </div>

<?php if ($msg === 'saved'): ?>
    <div class="alert alert-success"><i class="fas fa-check"></i> Classes saved. The website now shows this list. Next: check the amounts in <a href="fees_edit.php">Fee structure</a> (a new class starts empty).</div>
<?php elseif ($msg === 'restored'): ?>
    <div class="alert alert-success"><i class="fas fa-rotate-left"></i> The original class list has been restored.</div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
    <p class="adm-help"><?= $is_custom ? '<span class="adm-badge adm-badge--new">Edited</span> The website shows your list.' : '<span class="adm-badge">Original</span> The website shows the original list (Playgroup to Class 10).' ?></p>

    <form method="POST" class="adm-card adm-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(admin_csrf_token()) ?>">
        <div class="table-responsive">
            <table class="table cls-table mb-2">
                <thead><tr><th>#</th><th>Class</th><th>Minimum age</th><th>Level card</th><th><span class="sr-only">Remove</span></th></tr></thead>
                <tbody id="cls-rows">
<?php foreach ($rows as $i => $r): ?>
                    <tr>
                        <td class="cls-num"><?= $i + 1 ?></td>
                        <td><input type="text" class="form-control" name="class[]" value="<?= h($r['class']) ?>" maxlength="40" placeholder="e.g. Class 8" aria-label="Class name"></td>
                        <td><input type="text" class="form-control" name="age[]" value="<?= h($r['age']) ?>" maxlength="40" placeholder="e.g. 12.5+ years" aria-label="Minimum age"></td>
                        <td><select class="custom-select" name="level[]" aria-label="Level card">
<?php foreach ($level_names as $li => $ln): ?>
                            <option value="<?= $li ?>"<?= (int) $r['level'] === $li ? ' selected' : '' ?>><?= h($ln) ?></option>
<?php endforeach; ?>
                        </select></td>
                        <td><button type="button" class="btn btn-outline-danger btn-sm cls-del" title="Remove this class"><i class="fas fa-xmark"></i></button></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="cls-add"><i class="fas fa-plus"></i> Add a class</button>
        <p class="adm-help mb-3">Removing a class only hides it on the website; its fees stay saved. Class names must match the Fee structure (the fee table follows this list automatically).</p>
        <div class="adm-sticky d-flex flex-wrap align-items-center" style="gap:.5rem">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-floppy-disk"></i> Save classes</button>
            <a href="admin_panel.php" class="btn btn-link">Cancel</a>
<?php if ($is_custom): ?>
            <button type="submit" name="restore" value="1" class="btn btn-outline-danger ml-auto" onclick="return confirm('Go back to the original class list (Playgroup to Class 10)?');"><i class="fas fa-rotate-left"></i> Restore original</button>
<?php endif; ?>
        </div>
    </form>
</div>
<script>
(function () {
    var body = document.getElementById('cls-rows');
    function renumber() { [].forEach.call(body.rows, function (tr, i) { tr.cells[0].textContent = i + 1; }); }
    body.addEventListener('click', function (e) {
        var b = e.target.closest('.cls-del');
        if (!b) return;
        if (body.rows.length === 1) { alert('Please keep at least one class.'); return; }
        b.closest('tr').remove(); renumber();
    });
    document.getElementById('cls-add').addEventListener('click', function () {
        var last = body.rows[body.rows.length - 1];
        var tr = last.cloneNode(true);
        tr.querySelectorAll('input').forEach(function (i) { i.value = ''; });
        tr.querySelector('select').value = last.querySelector('select').value; // same level as the row above
        body.appendChild(tr); renumber();
        tr.querySelector('input').focus();
    });
})();
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
