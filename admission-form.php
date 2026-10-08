<?php
$page_title = 'Admission Form';
$page_description = 'Download the SKANS School System, AECHS Campus admission form (PDF or Word) and see what to bring to the school office.';
require_once __DIR__ . '/includes/bootstrap.php';
$fees = load_fees(); // data/fees.php + admin's saved policy
if (empty($fees['forms_public'])) {
    // Admission forms are not offered online for now (switch: data/fees.php 'forms_public').
    header('Location: admissions.php', true, 302);
    exit;
}
require __DIR__ . '/includes/header.php';

/** File size like "197 KB", or '' if the file is missing. */
function file_size_label(string $path): string
{
    $full = __DIR__ . '/' . $path;
    return is_file($full) ? max(1, round(filesize($full) / 1024)) . ' KB' : '';
}
?>
<?php page_banner('Admission Form', 'Download, print and fill in the form, then bring it to the school office with the required documents.', [['Admissions', 'admissions.php'], ['Admission Form', '']]); ?>
<?php admissions_nav(); ?>

<section class="sk-section">
    <div class="sk-container">
        <div class="sk-stack">
<?php foreach ($fees['forms'] as $form): ?>
            <article class="sk-card sk-card--accent">
                <div class="sk-card__body sk-download">
                    <div class="sk-download__info">
                        <h2 class="sk-card__title"><?= e($form['title']) ?></h2>
<?php if (!is_missing($form['updated'])): ?>
                        <p>Updated <?= e($form['updated']) ?></p>
<?php endif; ?>
                    </div>
                    <div class="sk-actions sk-mt-0">
<?php if (empty($form['files'])): ?>
                        <?= content('', 'form to be uploaded by SKANS') ?>
<?php else: foreach ($form['files'] as $i => $file):
        $size = file_size_label($file['file']);
        if ($size === '') continue; ?>
                        <a class="sk-btn <?= $i === 0 ? 'sk-btn--primary' : 'sk-btn--outline' ?>" href="<?= e($file['file']) ?>" download>
                            Download <?= e($file['label']) ?> <span class="sk-muted-on-btn">(<?= e($size) ?>)</span>
                        </a>
<?php endforeach; endif; ?>
                    </div>
                </div>
            </article>
<?php endforeach; ?>
        </div>
    </div>
</section>

<section class="sk-section sk-section--white">
    <div class="sk-container sk-split">
        <div>
            <div class="sk-section-header">
                <span class="sk-eyebrow">Before you visit</span>
                <h2>What to bring</h2>
                <span class="sk-rule"></span>
            </div>
            <ul class="sk-checklist">
<?php foreach ($fees['admission_policy']['documents'] as $doc): ?>
                <li><?= e($doc) ?></li>
<?php endforeach; ?>
            </ul>
            <p class="sk-muted">These documents are required after the admission test and interview.</p>
        </div>
        <div>
            <div class="sk-section-header">
                <span class="sk-eyebrow">Next steps</span>
                <h2>Submitting the form</h2>
                <span class="sk-rule"></span>
            </div>
            <ol class="sk-steps">
                <li><strong>Read the rules</strong>The form includes the school rules. Please read the <a href="admission-policy.php">Admission Policy</a> before signing.</li>
                <li><strong>Fill in and sign</strong>Complete the student, parent, guardian, health and sibling details, and sign the declaration.</li>
                <li><strong>Visit the office</strong>Bring the form to the school office. <a href="contact-us.php">Find us</a>.</li>
            </ol>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
