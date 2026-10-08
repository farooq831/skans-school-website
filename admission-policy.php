<?php
$page_title = 'Admission Policy';
$page_description = 'Admission policy of SKANS School System, AECHS Campus, Rawalpindi: merit-based admission, required documents, fee rules, attendance and leaving the school.';
require __DIR__ . '/includes/header.php';
$fees = load_fees(); // data/fees.php + admin's saved policy
$policy = $fees['admission_policy'];
$cur = $fees['currency'];

$sections = [
    ['admission',   'Admission'],
    ['documents',   'Required documents'],
    ['fees',        'Fee rules'],
    ['attendance',  'Attendance'],
    ['leaving',     'Leaving the school and refunds'],
];
?>
<?php page_banner('Admission Policy', 'Please read these rules carefully. Parents sign the admission form to confirm they will follow them.', [['Admissions', 'admissions.php'], ['Admission Policy', '']]); ?>
<?php admissions_nav(); ?>

<section class="sk-section">
    <div class="sk-container sk-prose">
        <div class="sk-stack sk-policy">
            <section class="sk-anchor" id="admission">
                <h2>Admission</h2>
                <span class="sk-rule"></span>
                <ul class="sk-checklist">
<?php foreach ($policy['general'] as $item): ?>
                    <li><?= e($item) ?></li>
<?php endforeach; ?>
                </ul>
                <p>Minimum ages for each class are listed on the <a href="admissions.php">Admission Process</a> page.</p>
            </section>

            <section class="sk-anchor" id="documents">
                <h2>Required documents</h2>
                <span class="sk-rule"></span>
                <p>After passing the admission test and interview, the following documents are required to complete the admission process:</p>
                <ul class="sk-checklist">
<?php foreach ($policy['documents'] as $item): ?>
                    <li><?= e($item) ?></li>
<?php endforeach; ?>
                </ul>
            </section>

            <section class="sk-anchor" id="fees">
                <h2>Fee rules</h2>
                <span class="sk-rule"></span>
                <ul class="sk-checklist">
<?php foreach ($fees['fee_rules'] as $item): ?>
                    <li><?= e($item) ?></li>
<?php endforeach; ?>
                </ul>
                <p>Amounts for each class are on the <a href="fee-structure.php">Fee Structure</a> page.</p>
            </section>

            <section class="sk-anchor" id="attendance">
                <h2>Attendance</h2>
                <span class="sk-rule"></span>
                <ul class="sk-checklist">
<?php foreach ($policy['attendance'] as $item): ?>
                    <li><?= e($item) ?></li>
<?php endforeach; ?>
                </ul>
            </section>

            <section class="sk-anchor" id="leaving">
                <h2>Leaving the school and refunds</h2>
                <span class="sk-rule"></span>
                <ul class="sk-checklist">
<?php foreach ($policy['leaving'] as $item): ?>
                    <li><?= e($item) ?></li>
<?php endforeach; ?>
                </ul>
            </section>

            <div class="sk-note">
                <p><strong>Declaration by parents.</strong> <?= e($policy['declaration']) ?></p>
            </div>
        </div>

        <div class="sk-actions">
            <a class="sk-btn sk-btn--primary" href="admissions.php">Admission process</a>
            <a class="sk-btn sk-btn--outline" href="fee-structure.php">Fee Structure</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
