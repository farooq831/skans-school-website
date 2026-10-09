<?php
$page_title = 'Programs Offered';
$page_description = 'Academic programs at SKANS School System, AECHS Campus, Rawalpindi: Early Years, Juniors and Seniors, from Playgroup to Matric, with the key objectives of each level.';
require __DIR__ . '/includes/header.php';

?>
<?php page_banner('Programs Offered', campus_text('From the first steps of Early Years to Matric, each level builds the knowledge, skills and character children need for the next.'), [['Academics', ''], ['Programs Offered', '']]); ?>
<?php academics_nav(); ?>

<?php include __DIR__ . '/includes/levels-section.php'; ?>

<section class="sk-section">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Beyond the classroom</span>
            <h2>Responsible and active citizens</h2>
            <span class="sk-rule"></span>
        </div>
        <div class="sk-grid sk-grid--2">
            <div class="sk-note"><h3>Around the World</h3><p>We nurture curiosity about the world, introducing students to global topics, cultures, and geography. Through interactive lessons, students appreciate diversity, fostering respect and responsibility as global citizens.</p></div>
            <div class="sk-note"><h3>Student Body Council</h3><p>Our Student Council empowers students to participate in school governance, fostering leadership, teamwork, and decision-making. This helps students develop responsibility and pride in their school.</p></div>
            <div class="sk-note"><h3>Religion and Islamic Studies</h3><p>Students learn core Islamic principles like love for Allah, respect, and kindness. Through Quranic stories and Islamic morals, they develop a spiritual connection that shapes their character and behavior.</p></div>
            <div class="sk-note"><h3>Personal, Social, and Emotional Development</h3><p>We focus on confidence, self-awareness, and emotional management. Students engage in teamwork, empathy-building, and independent decision-making to develop strong social skills and a sense of responsibility.</p></div>
        </div>
    </div>
</section>

<section class="sk-section sk-section--sand sk-section--tight">
    <div class="sk-container sk-text-center">
        <h2>Find the right class for your child</h2>
        <div class="sk-actions sk-actions--center">
            <a class="sk-btn sk-btn--primary" href="admissions.php">Admission process</a>
            <a class="sk-btn sk-btn--outline" href="advanced-placement-program.php">Special Programs</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
