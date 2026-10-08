<?php
$page_title = 'Admission Process';
$page_description = 'How to apply to SKANS School System, AECHS Campus, Rawalpindi: admission steps, age guide from Playgroup to Class 10, fees, policy and admission form.';
require __DIR__ . '/includes/header.php';
$fees = load_fees(); // data/fees.php + admin's saved policy
?>
<?php page_banner('Admissions', campus_text('Begin your child’s journey at ' . $site['full_name'] . '. Admissions are open from Playgroup to Class 10 (Matric).'), [['Admissions', '']]); ?>
<?php admissions_nav(); ?>

<section class="sk-section">
    <div class="sk-container sk-split">
        <div>
            <div class="sk-section-header">
                <span class="sk-eyebrow">How to apply</span>
                <h2>Admission procedure</h2>
                <span class="sk-rule"></span>
            </div>
            <ol class="sk-steps">
                <li><strong>Meet the Principal</strong>Parents meet the Principal. After the meeting, the admin and accounts office registers the child before the admission test.</li>
                <li><strong>Pay the registration fee</strong>Parents pay the registration fee at the admin and accounts office.</li>
                <li><strong>Complete the admission form</strong>Parents fill in the admission form with details of the child and the parents or guardian.</li>
                <li><strong>Principal’s approval</strong>Finally, the Principal signs the registration form.</li>
            </ol>
            <div class="sk-note sk-mt-5">
                <p>Admission is granted on merit, after the admission test and interview. See the <a href="admission-policy.php">Admission Policy</a> for the documents you will need.</p>
            </div>
            <div class="sk-actions">
                <a class="sk-btn sk-btn--primary" href="contact-us.php">Contact the office</a>
            </div>
        </div>

        <div>
            <div class="sk-section-header">
                <span class="sk-eyebrow">Age guide</span>
                <h2>Classes and minimum age</h2>
                <span class="sk-rule"></span>
            </div>
            <div class="sk-table-wrap">
                <table class="sk-table">
                    <caption class="sk-visually-hidden">Minimum age for each class</caption>
                    <thead><tr><th scope="col">Class</th><th scope="col">Minimum age</th></tr></thead>
                    <tbody>
<?php foreach ($site['levels'] as $level): foreach ($level['classes'] as [$class, $age]): ?>
                        <tr><th scope="row"><?= e($class) ?></th><td><?= e($age) ?></td></tr>
<?php endforeach; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section class="sk-section sk-section--white">
    <div class="sk-container">
        <div class="sk-section-header sk-section-header--center">
            <h2>Admission information</h2>
            <span class="sk-rule"></span>
        </div>
        <div class="sk-grid">
            <article class="sk-card sk-card--accent">
                <div class="sk-card__body">
                    <h3 class="sk-card__title">Fee Structure</h3>
                    <p>Registration, admission, security, annual and monthly tuition fees for every class.</p>
                    <a class="sk-card__link" href="fee-structure.php">View fees<span class="sk-visually-hidden"> structure</span> →</a>
                </div>
            </article>
            <article class="sk-card sk-card--accent">
                <div class="sk-card__body">
                    <h3 class="sk-card__title">Admission Policy</h3>
                    <p>Required documents, fee rules, attendance and leaving the school.</p>
                    <a class="sk-card__link" href="admission-policy.php">Read the policy →</a>
                </div>
            </article>
            <article class="sk-card sk-card--accent">
                <div class="sk-card__body">
                    <h3 class="sk-card__title">Programs Offered</h3>
                    <p>Early Years, Juniors and Seniors: what children learn at each level.</p>
                    <a class="sk-card__link" href="departments.php">See programs →</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="sk-section">
    <div class="sk-container sk-text-center">
        <h2>Questions about admission?</h2>
        <p class="sk-lead">Call or message the school office. We are happy to help.</p>
        <div class="sk-actions sk-actions--center">
<?php foreach ($site['phones'] as $phone): ?>
            <a class="sk-btn sk-btn--outline" href="tel:<?= e($phone['tel']) ?>">Call <?= e($phone['display']) ?></a>
<?php endforeach; ?>
            <a class="sk-btn sk-btn--primary" href="mailto:<?= e($site['email']) ?>">Email the office</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
