<?php
$page_title = 'Special Programs';
$page_description = 'Special programs at SKANS School System, AECHS Campus, Rawalpindi: STEM, Montessori, practical life skills, leadership, phonics workshops, competitions, Math Olympiad and robotics.';
require __DIR__ . '/includes/header.php';

// Program texts kept from the previous Special Programs page.
$programs = [
    ['STEM Education', 'Our STEM (Science, Technology, Engineering, and Mathematics) program offers a comprehensive introduction to these disciplines through experiential learning. By engaging in hands-on activities, conducting experiments, and employing creative problem-solving techniques, students are encouraged to develop critical thinking skills and innovative approaches. This establishes a robust foundation for later academic and intellectual growth.'],
    ['Montessori Teaching', 'Adhering to the Montessori educational philosophy, we emphasize autonomy, self-directed learning, and experiential engagement. Our educators, trained in the Montessori Method, curate an environment that supports individualized exploration and learning at each child’s own pace. This approach fosters innate curiosity and cultivates a sustained passion for learning.'],
    ['EPL (Exercises of Practical Life)', 'Our curriculum integrates exercises focused on practical life skills, including self-care, organizational abilities, and social interactions. These activities are designed to enhance children’s coordination, concentration, independence, and sense of responsibility, within a structured yet engaging framework.'],
    ['Student Leadership Programs', 'We implement specialized programs aimed at developing leadership skills from an early age. These initiatives promote decision-making, teamwork, and accountability, empowering students to articulate their ideas, take proactive roles, and build self-confidence.'],
    ['Phonics Workshop for Mothers', 'Our phonics workshops for mothers are designed to equip parents with effective strategies and tools for supporting their children’s literacy development at home. These sessions provide a thorough understanding of phonics and its application in enhancing early reading and writing abilities.'],
    ['National & International Competitions', 'We actively promote student participation in both national and international competitions, nurturing a spirit of healthy competition, global awareness, and cultural exchange. Such experiences are instrumental in building confidence and resilience, and in celebrating the diverse talents and accomplishments of our students.'],
    ['International Math Olympiad', 'Our students participate in the prestigious International Math Olympiad, gaining exposure to advanced problem-solving alongside the study of computer languages. This initiative fosters crucial skills, empowering students to face the challenges of tomorrow with confidence.'],
    ['Robotics', 'We actively promote student participation in both national and international competitions, thereby nurturing a spirit of healthy competition, global awareness, and cultural exchange. Such experiences are instrumental in building confidence, resilience, and celebrating the diverse talents and accomplishments of our students.'],
];
?>
<?php page_banner('Special Programs', 'Programs that challenge students, broaden their knowledge and build the confidence to succeed beyond school.', [['Academics', ''], ['Special Programs', '']]); ?>
<?php academics_nav(); ?>

<section class="sk-section">
    <div class="sk-container sk-split">
        <div>
            <div class="sk-section-header">
                <span class="sk-eyebrow">Beyond the curriculum</span>
                <h2>Our special programs</h2>
                <span class="sk-rule"></span>
            </div>
            <p>At SKANS School, our special programs are designed to provide a well-rounded, future-focused education. They emphasize cognitive growth, practical life skills and a lifelong passion for learning through interactive, hands-on experiences. By bringing modern technology and global perspectives into the curriculum, we promote digital literacy and holistic development, preparing students to succeed in an interconnected and rapidly evolving world.</p>
            <div class="sk-actions">
                <a class="sk-btn sk-btn--primary" href="departments.php">Programs Offered</a>
                <a class="sk-btn sk-btn--outline" href="admissions.php">Admissions</a>
            </div>
        </div>
        <figure class="sk-figure">
            <img src="assets/img/special-programs.jpeg" alt="<?= e('SKANS ' . $site['campus']) ?> students taking part in a special program" width="800" height="600">
        </figure>
    </div>
</section>

<section class="sk-section sk-section--white">
    <div class="sk-container">
        <div class="sk-grid sk-grid--3">
<?php foreach ($programs as [$title, $text]): ?>
            <article class="sk-card sk-card--accent">
                <div class="sk-card__body">
                    <h3 class="sk-card__title"><?= e($title) ?></h3>
                    <p><?= is_missing($text) ? content('', 'description of ' . $title) : e($text) ?></p>
                </div>
            </article>
<?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
