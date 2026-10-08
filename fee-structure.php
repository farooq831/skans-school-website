<?php
$page_title = 'Fee Structure';
$page_description = 'Fee structure at SKANS School System, AECHS Campus, Rawalpindi: registration, admission, security, annual, tuition and lab charges for Playgroup to Class 10.';
require __DIR__ . '/includes/header.php';
$fees = load_fees(); // data/fees.php + admin's saved policy
$cur = $fees['currency'];
$cols = $fees['columns'];

// First payment at admission = admission + security + registration + one month's tuition (rule from the admission form).
$first = $fees['classes'][0];
$first_total = $first['admission'] + $first['security'] + $first['registration'] + $first['tuition'];
// Phones: classes with identical fees are grouped into one card (e.g. "Playgroup – Pre 9").
$groups = [];
foreach ($fees['classes'] as $row) {
    $values = $row;
    unset($values['class']);
    $last = count($groups) - 1;
    if ($last >= 0 && $groups[$last]['values'] === $values) {
        $groups[$last]['to'] = $row['class'];
    } else {
        $groups[] = ['from' => $row['class'], 'to' => $row['class'], 'values' => $values];
    }
}
$same_for_all = count(array_unique(array_map(function ($row) {
    return $row['admission'] + $row['security'] + $row['registration'] + $row['tuition'];
}, $fees['classes']))) === 1;
?>
<?php page_banner('Fee Structure', campus_text('Fees for every class from Playgroup to Class 10.'), [['Admissions', 'admissions.php'], ['Fee Structure', '']]); ?>
<?php admissions_nav(); ?>

<section class="sk-section">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Academic session: <?= content($fees['session'], 'session year') ?></span>
            <h2>Fees by class</h2>
            <span class="sk-rule"></span>
            <p>All amounts are in Pakistani Rupees (<?= e($cur) ?>). A dash (–) means the charge does not apply to that class.</p>
        </div>

        <div class="sk-table-wrap sk-wide-only">
            <table class="sk-table">
                <caption class="sk-visually-hidden">Fees by class, in <?= e($cur) ?></caption>
                <thead>
                    <tr>
                        <th scope="col">Class</th>
<?php foreach ($cols as $col): ?>
                        <th scope="col" class="sk-num"><?= e($col['label']) ?><small><?= e($col['period']) ?></small></th>
<?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($fees['classes'] as $row): ?>
                    <tr>
                        <th scope="row"><?= e($row['class']) ?></th>
<?php foreach ($cols as $key => $col): ?>
                        <td class="sk-num" data-label="<?= e($col['label'] . ' (' . $col['period'] . ')') ?>"><?= money($row[$key], $cur) ?></td>
<?php endforeach; ?>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="sk-narrow-only sk-stack">
<?php foreach ($groups as $group): ?>
            <div class="sk-table-wrap">
                <table class="sk-table sk-table--stack">
                    <caption class="sk-visually-hidden">Fees for <?= e($group['from'] === $group['to'] ? $group['from'] : $group['from'] . ' to ' . $group['to']) ?></caption>
                    <tbody>
                        <tr>
                            <th scope="row"><?= e($group['from'] === $group['to'] ? $group['from'] : $group['from'] . ' – ' . $group['to']) ?></th>
<?php foreach ($cols as $key => $col): ?>
                            <td class="sk-num" data-label="<?= e($col['label']) ?>"><span><?= money($group['values'][$key], $cur) ?><?php if ($group['values'][$key] !== null): ?><small><?= e($col['period']) ?></small><?php endif; ?></span></td>
<?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
<?php endforeach; ?>
        </div>
    </div>
</section>

<section class="sk-section sk-section--white">
    <div class="sk-container sk-split">
        <div>
            <div class="sk-section-header">
                <span class="sk-eyebrow">At the time of admission</span>
                <h2>First payment</h2>
                <span class="sk-rule"></span>
            </div>
            <p>The first payment includes the admission fee, security fee, registration fee and one month’s tuition fee, and must be paid in advance.</p>
            <div class="sk-table-wrap">
                <table class="sk-table">
                    <caption class="sk-visually-hidden">First payment at admission</caption>
                    <tbody>
                        <tr><th scope="row">Admission Fee</th><td class="sk-num"><?= money($first['admission'], $cur) ?></td></tr>
                        <tr><th scope="row">Security Fee</th><td class="sk-num"><?= money($first['security'], $cur) ?></td></tr>
                        <tr><th scope="row">Registration Fee</th><td class="sk-num"><?= money($first['registration'], $cur) ?></td></tr>
                        <tr><th scope="row">Tuition Fee (one month)</th><td class="sk-num"><?= money($first['tuition'], $cur) ?></td></tr>
                        <tr><th scope="row">Total</th><td class="sk-num sk-total"><?= money($first_total, $cur) ?></td></tr>
                    </tbody>
                </table>
            </div>
<?php if (!$same_for_all): ?>
            <p class="sk-muted">Amounts differ by class; see the table above.</p>
<?php endif; ?>
            <p class="sk-muted">Please confirm the exact amount on your admission fee challan.</p>
        </div>

        <div class="sk-anchor" id="rules">
            <div class="sk-section-header">
                <span class="sk-eyebrow">Please read</span>
                <h2>Fee rules</h2>
                <span class="sk-rule"></span>
            </div>
            <ul class="sk-checklist">
<?php foreach ($fees['fee_rules'] as $rule): ?>
                <li><?= e($rule) ?></li>
<?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="sk-section">
    <div class="sk-container sk-text-center">
        <h2>Ready to apply?</h2>
        <p class="sk-lead">Read the admission policy, then contact the school office to begin.</p>
        <div class="sk-actions sk-actions--center">
            <a class="sk-btn sk-btn--primary" href="admissions.php">Admission process</a>
            <a class="sk-btn sk-btn--outline" href="admission-policy.php">Admission Policy</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
