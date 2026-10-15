<?php
$page_title = 'About Us';
$page_description = 'About SKANS School System, AECHS Campus, Rawalpindi: a message from the Principal, our core values, vision and mission, and how we meet the needs of every student.';
require __DIR__ . '/includes/header.php';
// Original page: _old/about-legacy.php. Section order agreed on 3 Oct 2026:
// 1 Principal's message (text unchanged), 2 Core Values, 3 Vision & Mission, then the rest of the page.
?>
<?php page_banner('About Us', 'Welcome to ' . $site['full_name'] . ', ' . $site['city'] . '.', [['About', '']]); ?>

<!-- 1. Principal's message: editable in the admin panel (Settings → Principal's message); defaults in includes/settings.php -->
<?php $principal = load_principal(); ?>
<section class="sk-section sk-anchor" id="principal">
    <div class="sk-container sk-principal">
        <figure class="sk-principal__photo">
            <img src="<?= e($principal['photo_src']) ?>" width="649" height="866" alt="<?= e($principal['photo_alt']) ?>" style="object-position: center <?= e($principal['photo_pos']) ?>">
        </figure>
        <div>
            <span class="sk-eyebrow">A message from the <?= e($principal['title'] !== '' ? $principal['title'] : 'Principal') ?></span>
            <h2><?= e($principal['heading']) ?></h2>
            <span class="sk-rule"></span>
<?php foreach ($principal['paragraphs'] as $para): ?>
            <p><?= e($para) ?></p>
<?php endforeach; ?>
            <p class="sk-principal__sign">Regards,<br><?php if ($principal['name'] !== ''): ?><?= e($principal['name']) ?><br><?php endif; ?><?= e($principal['title']) ?></p>
            <div class="sk-actions">
                <a class="sk-btn sk-btn--primary" href="contact-us.php">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<!-- 2. Core Values -->
<section class="sk-section sk-section--navy">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">What we stand for</span>
            <h2>Our Core Values</h2>
            <span class="sk-rule"></span>
        </div>
        <div class="sk-values">
            <article class="sk-value">
                <span class="sk-value__num" aria-hidden="true">01</span>
                <h3>Respect &amp; Integrity</h3>
                <p>Upholding honesty, trustworthiness and ethical behaviour in all actions. Valuing and honouring diversity, treating others with dignity, and showing consideration for different perspectives.</p>
            </article>
            <article class="sk-value">
                <span class="sk-value__num" aria-hidden="true">02</span>
                <h3>Challenge, Performance and Recognition</h3>
                <p>Encouraging every student to take on new challenges, give their best performance, and be recognised for effort and achievement.</p>
            </article>
        </div>
    </div>
</section>

<!-- 3. Vision & Mission -->
<section class="sk-section">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Where we are going</span>
            <h2>Our Vision &amp; Mission</h2>
            <span class="sk-rule"></span>
        </div>
        <div class="sk-vm">
            <article class="sk-vm__item">
                <span class="sk-eyebrow">Vision</span>
                <p class="sk-vm__quote">“Everyone deserves a GOOD life.”</p>
            </article>
            <article class="sk-vm__item">
                <span class="sk-eyebrow">Mission</span>
                <p class="sk-vm__quote">“To develop highly skilled students with well-rounded personalities.”</p>
            </article>
        </div>
    </div>
</section>

<!-- Rest of the previous About page, kept and restyled -->
<section class="sk-section sk-section--white">
    <div class="sk-container sk-split">
        <div>
            <span class="sk-eyebrow">Showcasing our commitment to excellence</span>
            <h2>Discover <?= e($site['name']) ?> – <?= e($site['campus']) ?></h2>
            <span class="sk-rule"></span>
            <p>Our academic program is thoughtfully designed to ignite curiosity, foster creativity, and instill a lifelong passion for learning. In a nurturing and supportive environment, students embark on a journey of discovery, where they feel secure and encouraged to explore new ideas and develop their full potential. We focus on cultivating a love for learning by providing a stimulating curriculum that challenges students to think critically and solve problems creatively.</p>
            <p>At SKANS School System, we are dedicated to nurturing a lifelong love for learning. We provide high-quality education that combines academic rigor, creativity, and personal development in a supportive environment, with a balanced approach that emphasizes academic excellence, technological proficiency, and holistic development. We integrate innovative technology with traditional teaching methods and empower students to become responsible, compassionate, and global citizens.</p>
            <div class="sk-actions">
                <a class="sk-btn sk-btn--outline" href="departments.php">Academic Programs</a>
            </div>
        </div>
        <div class="sk-photos">
            <img src="assets/img/about-campus-1.jpg" width="1200" height="604" alt="<?= e('SKANS ' . $site['campus']) ?> building" loading="lazy">
            <img src="assets/img/about-campus-2.jpg" width="900" height="379" alt="School classroom" loading="lazy">
            <img src="assets/img/about-campus-3.jpg" width="900" height="493" alt="School park" loading="lazy">
        </div>
    </div>
</section>

<section class="sk-section">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Every child, every day</span>
            <h2>How We Meet the Needs of Students</h2>
            <span class="sk-rule"></span>
        </div>
        <div class="sk-grid sk-grid--2">
            <article class="sk-card sk-card--accent"><div class="sk-card__body">
                <h3 class="sk-card__title">A Well-Rounded Education</h3>
                <p>At SKANS, we are committed to fostering the all-around development of every child. Our philosophy integrates academic excellence with social growth and emotional wellness, ensuring each child flourishes in a nurturing and balanced setting. We inspire students to discover and pursue their passions through diverse activities that enhance creativity, physical health, and interpersonal skills, building a robust foundation for a lifetime of learning and success.</p>
            </div></article>
            <article class="sk-card sk-card--accent"><div class="sk-card__body">
                <h3 class="sk-card__title">Academics and Extra-Curricular</h3>
                <p>At SKANS, learning goes far beyond the classroom. Our curriculum is complemented by a variety of enrichment activities designed to ignite curiosity and cultivate a genuine love for learning. Whether through arts and crafts, poetry, physical education, or circle time, children are encouraged to explore new interests and develop vital life skills in a dynamic and enjoyable setting.</p>
            </div></article>
            <article class="sk-card sk-card--accent"><div class="sk-card__body">
                <h3 class="sk-card__title">Success For All</h3>
                <p>At SKANS, we recognize and embrace the individuality of every student. Our programs are thoughtfully tailored to meet each child’s unique needs, offering personalized guidance to help them thrive. Through small group instruction, interactive learning experiences, and dedicated one-on-one support, we empower every student to feel confident and successful throughout their educational journey.</p>
            </div></article>
            <article class="sk-card sk-card--accent"><div class="sk-card__body">
                <h3 class="sk-card__title">Individual Attention For Every Student</h3>
                <p>We believe in celebrating the individuality of every child by providing personalized guidance and care. With our small class sizes and committed educators, each student benefits from focused attention that addresses their unique needs, strengths, and learning styles. Through ongoing observation and customized feedback, we foster their academic progress, personal growth, and self-confidence.</p>
            </div></article>
        </div>
    </div>
</section>

<section class="sk-section sk-section--sand sk-section--tight">
    <div class="sk-container sk-text-center">
        <h2>We’re always here for you</h2>
        <p class="sk-lead">Contact us for assistance with admissions, fees or anything else.</p>
        <div class="sk-actions sk-actions--center">
            <a class="sk-btn sk-btn--primary" href="contact-us.php">Contact Us</a>
            <a class="sk-btn sk-btn--outline" href="admissions.php">Admissions</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
