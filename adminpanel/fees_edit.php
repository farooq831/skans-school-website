<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/admin_lib.php';
// Fee Structure editor (Settings → Fee structure). Shown on the website's Fee Structure page.
// Saved in site_settings 'fees' = ['session' => '2026–2027', 'classes' => [[class, registration, …], …]].
// When the session year changes, the previous year's table is kept in 'fees_history'.
// "Restore original" goes back to data/fees.php.

$defaults = require SK_ROOT . '/data/fees.php';
$columns = $defaults['columns'];             // registration, admission, security, annual, tuition, lab
$saved = settings_all($conn)['fees'] ?? [];
$history = settings_all($conn)['fees_history'] ?? [];
$current = [
    'session' => $saved['session'] ?? $defaults['session'],
    'classes' => $saved['classes'] ?? $defaults['classes'],
];
// Rows = the classes this campus teaches (Settings > Classes & ages); a new class starts with empty amounts.
$current['classes'] = fees_for_classes($current['classes'], class_order($conn), $columns);
$errors = [];
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_csrf();

    if (isset($_POST['restore'])) {
        settings_delete($conn, 'fees');
        header('Location: fees_edit.php?msg=restored');
        exit;
    }

    $session = trim((string) ($_POST['session'] ?? ''));
    if ($session === '' || mb_strlen($session) > 30) {
        $errors['session'] = 'Please enter the session, e.g. 2027–2028.';
    }
    $classes = [];
    $names = (array) ($_POST['class'] ?? []);
    foreach ($names as $i => $name) {
        $name = trim((string) $name);
        if ($name === '') continue;
        $row = ['class' => mb_substr($name, 0, 40)];
        foreach ($columns as $key => $col) {
            $raw = str_replace([',', ' '], '', trim((string) ($_POST[$key][$i] ?? '')));
            if ($raw === '' || $raw === '-' || $raw === '–') {
                $row[$key] = null;                       // not charged → "–" on the website
            } elseif (ctype_digit($raw) && (int) $raw <= 10000000) {
                $row[$key] = (int) $raw;
            } else {
                $errors['cells'] = 'Please enter whole numbers only (e.g. 16000), or leave a box empty if the fee does not apply.';
                $row[$key] = $raw;
            }
        }
        $classes[] = $row;
    }
    if (!$classes) {
        $errors['cells'] = 'Please keep at least one class.';
    }

    if ($errors) {
        $current = ['session' => $session, 'classes' => $classes ?: $current['classes']];
    } else {
        // A new session year: keep the previous year's table as history (newest first, max 10).
        if ($session !== $current['session']) {
            array_unshift($history, ['session' => $current['session'], 'saved_at' => date('Y-m-d H:i'), 'classes' => $current['classes']]);
            settings_save($conn, 'fees_history', array_slice($history, 0, 10));
        }
        settings_save($conn, 'fees', ['session' => $session, 'classes' => $classes]);
        header('Location: fees_edit.php?msg=saved');
        exit;
    }
}

// Suggested next session: "2026–2027" → "2027–2028"
$next_session = preg_match('/(\d{4})\D+(\d{4})/', $current['session'], $m) ? ($m[1] + 1) . '–' . ($m[2] + 1) : '';
$is_custom = !empty($saved);
$amount = function ($v) { return $v === null ? '' : (string) $v; };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Structure | SKANS Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .fee-table th, .fee-table td { vertical-align: middle; white-space: nowrap; }
        .fee-table input.form-control { min-width: 96px; text-align: right; }
        .fee-table input.fee-class { text-align: left; min-width: 130px; font-weight: 600; }
        .fee-table thead small { display: block; font-weight: 400; opacity: .8; }
        .fee-setall { display: flex; gap: 4px; margin-top: 6px; }
        .fee-setall input { min-width: 0 !important; width: 80px; height: 30px; font-size: .8rem; }
        .fee-setall button { height: 30px; padding: 0 8px; font-size: .75rem; }
        .fee-changed { background: #FFF8E6 !important; }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container-fluid adm-page" style="max-width: 1300px">
    <div class="adm-title">
        <div>
            <h1>Fee structure</h1>
            <p class="adm-sub">Shown on the website's <strong>Fee Structure</strong> page. Update it once a year for the new session. Amounts in rupees, whole numbers; leave a box <strong>empty</strong> if that fee does not apply (shown as “–”). The classes come from <a href="classes_edit.php">Settings › Classes &amp; ages</a>.</p>
        </div>
        <a class="btn btn-outline-primary" href="../fee-structure.php" target="_blank" rel="noopener"><i class="fas fa-eye"></i> View on website</a>
    </div>

<?php if ($msg === 'saved'): ?>
    <div class="alert alert-success"><i class="fas fa-check"></i> Fee structure saved. It is now shown on the website.</div>
<?php elseif ($msg === 'restored'): ?>
    <div class="alert alert-success"><i class="fas fa-rotate-left"></i> The original fee table has been restored.</div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger"><?= h(implode(' ', $errors)) ?></div>
<?php endif; ?>

    <!-- Yearly update -->
    <div class="adm-card">
        <h2><i class="fas fa-calendar-plus"></i> New school year? Start the next session</h2>
        <p class="mb-2">Copies this year's fees into the table below for <strong><?= h($next_session ?: 'the next session') ?></strong>. You can raise all amounts by a percentage, check the table, then click <strong>Save fee structure</strong>. This year's table is kept in the history below.</p>
        <div class="form-inline" style="gap:.5rem">
            <label for="next-session" class="mr-1">Next session</label>
            <input type="text" id="next-session" class="form-control" value="<?= h($next_session) ?>" placeholder="2027–2028" style="width:140px">
            <label for="raise-pct" class="mx-1">Increase</label>
            <div class="input-group" style="width:120px">
                <input type="number" id="raise-pct" class="form-control" value="0" min="0" max="100" step="1">
                <div class="input-group-append"><span class="input-group-text">%</span></div>
            </div>
            <select id="raise-what" class="custom-select" style="width:auto">
                <option value="tuition">Tuition fee only</option>
                <option value="all">All fees</option>
            </select>
            <button type="button" class="btn btn-warning" id="start-next"><i class="fas fa-forward"></i> Prepare next session</button>
        </div>
        <small class="form-text adm-help">Amounts are rounded to the nearest 100 rupees. Nothing is saved until you click “Save fee structure”.</small>
    </div>

    <form method="POST" class="adm-form" id="fee-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(admin_csrf_token()) ?>">
        <div class="adm-card">
            <h2><i class="fas fa-money-bill-wave"></i> Fees by class</h2>
            <div class="form-group" style="max-width: 260px">
                <label for="session">Academic session</label>
                <input type="text" class="form-control<?= isset($errors['session']) ? ' is-invalid' : '' ?>" id="session" name="session" value="<?= h($current['session']) ?>" placeholder="2026–2027" required>
            </div>
            <div class="table-responsive">
                <table class="table table-sm fee-table mb-0">
                    <thead>
                        <tr>
                            <th>Class</th>
<?php foreach ($columns as $key => $col): ?>
                            <th class="text-right"><?= h($col['label']) ?><small><?= h($col['period']) ?></small>
                                <div class="fee-setall"><input type="text" inputmode="numeric" class="form-control" placeholder="all" data-setall="<?= h($key) ?>" aria-label="Set <?= h($col['label']) ?> for all classes"><button type="button" class="btn btn-light" data-apply="<?= h($key) ?>">Set all</button></div>
                            </th>
<?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($current['classes'] as $i => $row): ?>
                        <tr>
                            <td><input type="text" class="form-control form-control-sm fee-class" name="class[<?= $i ?>]" value="<?= h($row['class']) ?>" aria-label="Class name" readonly title="Change classes in Settings &gt; Classes &amp; ages"></td>
<?php foreach ($columns as $key => $col): ?>
                            <td><input type="text" inputmode="numeric" class="form-control form-control-sm fee-cell" name="<?= h($key) ?>[<?= $i ?>]" data-col="<?= h($key) ?>" value="<?= h($amount($row[$key] ?? null)) ?>" placeholder="–" aria-label="<?= h($row['class'] . ' ' . $col['label']) ?>"></td>
<?php endforeach; ?>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="adm-help mt-2 mb-0">Tip: clear a class name to remove that row. The first payment shown on the website (admission + security + registration + one month's tuition) is calculated automatically.</p>
        </div>
        <div class="adm-sticky d-flex flex-wrap align-items-center" style="gap:.5rem">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-floppy-disk"></i> Save fee structure</button>
            <a href="admin_panel.php" class="btn btn-link">Cancel</a>
<?php if ($is_custom): ?>
            <button type="submit" name="restore" value="1" class="btn btn-outline-danger ml-auto" onclick="return confirm('Go back to the original fee table? Your saved fees will be replaced.');"><i class="fas fa-rotate-left"></i> Restore original</button>
<?php endif; ?>
        </div>
    </form>

<?php if ($history): ?>
    <div class="adm-card mt-3">
        <h2><i class="fas fa-clock-rotate-left"></i> Previous sessions (history)</h2>
<?php foreach ($history as $h_i => $old): ?>
        <details class="mb-2">
            <summary><strong><?= h($old['session']) ?></strong> <span class="adm-help">· replaced on <?= h($old['saved_at']) ?></span></summary>
            <div class="table-responsive mt-2">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Class</th><?php foreach ($columns as $col): ?><th class="text-right"><?= h($col['label']) ?></th><?php endforeach; ?></tr></thead>
                    <tbody>
<?php foreach ($old['classes'] as $r): ?>
                        <tr><td><?= h($r['class']) ?></td><?php foreach ($columns as $key => $col): ?><td class="text-right"><?= isset($r[$key]) && $r[$key] !== null ? number_format((float) $r[$key]) : '–' ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </details>
<?php endforeach; ?>
    </div>
<?php endif; ?>
</div>
<script>
(function () {
    function clean(v) { return String(v).replace(/[,\s]/g, ''); }
    // "Set all" for one column
    document.querySelectorAll('[data-apply]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.getAttribute('data-apply'), v = clean(document.querySelector('[data-setall="' + key + '"]').value);
            if (v !== '' && !/^\d+$/.test(v)) { alert('Please enter a whole number.'); return; }
            document.querySelectorAll('.fee-cell[data-col="' + key + '"]').forEach(function (c) { c.value = v; c.classList.add('fee-changed'); });
        });
    });
    // Prepare next session: new session name + optional % increase
    document.getElementById('start-next').addEventListener('click', function () {
        var name = document.getElementById('next-session').value.trim();
        var pct = parseFloat(document.getElementById('raise-pct').value) || 0;
        var what = document.getElementById('raise-what').value;
        if (!name) { alert('Please enter the next session, e.g. 2027–2028.'); return; }
        document.getElementById('session').value = name;
        document.getElementById('session').classList.add('fee-changed');
        if (pct > 0) {
            document.querySelectorAll('.fee-cell').forEach(function (c) {
                if (what === 'tuition' && c.getAttribute('data-col') !== 'tuition') return;
                var v = clean(c.value);
                if (/^\d+$/.test(v)) { c.value = Math.round(parseInt(v, 10) * (1 + pct / 100) / 100) * 100; c.classList.add('fee-changed'); }
            });
        }
        document.getElementById('fee-form').scrollIntoView({ behavior: 'smooth' });
    });
    document.querySelectorAll('.fee-cell').forEach(function (c) { c.addEventListener('input', function () { c.classList.add('fee-changed'); }); });
})();
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
