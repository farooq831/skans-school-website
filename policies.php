<?php
$page_title = 'SKANS Policies';
$page_description = 'Policies of SKANS School System, AECHS Campus, Rawalpindi: admission, fees, attendance, leaving the school and harassment policy.';
require __DIR__ . '/includes/header.php';

$policies = [
    ['Admission Policy', 'Merit-based admission, admission test and interview, and the documents required.', 'admission-policy.php#admission', 'Read policy'],
    ['Fee Policy', 'How and when fees are paid, late payment notices and fines, and fee increases.', 'admission-policy.php#fees', 'Read policy'],
    ['Attendance Policy', 'Minimum 90% attendance for annual exams and rules on long absence.', 'admission-policy.php#attendance', 'Read policy'],
    ['Leaving the School & Refunds', 'Notice period, school leaving certificate and security refund.', 'admission-policy.php#leaving', 'Read policy'],
    ['Harassment Policy', 'The SKANS Harassment Committee, how complaints are handled, and penalties (PDF).', 'assets/docs/skans-harassment-policy.pdf', 'Open PDF'],
];
?>
<?php page_banner('SKANS Policies', 'The rules that keep our school fair, safe and well run.', [['About', 'about.php'], ['SKANS Policies', '']]); ?>

<section class="sk-section">
    <div class="sk-container">
        <div class="sk-grid sk-grid--3">
<?php foreach ($policies as [$title, $text, $href, $cta]): ?>
            <article class="sk-card sk-card--accent">
                <div class="sk-card__body">
                    <h2 class="sk-card__title sk-h3"><?= e($title) ?></h2>
                    <p><?= e($text) ?></p>
                    <a class="sk-card__link" href="<?= e($href) ?>"<?= substr($href, -4) === '.pdf' ? ' target="_blank" rel="noopener"' : '' ?>><?= e($cta) ?><span class="sk-visually-hidden">: <?= e($title) ?></span> →</a>
                </div>
            </article>
<?php endforeach; ?>
        </div>
        <p class="sk-muted">The admission, fee, attendance and leaving rules are part of the admission form that parents sign at the school office.</p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
