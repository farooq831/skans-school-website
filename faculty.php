<?php
$page_title = 'Faculty';
$page_description = 'Leadership and teaching faculty of SKANS School System, AECHS Campus, Rawalpindi: experience and qualifications.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/faculty-card.php';
// Rebuilt Oct 2026 (old page: _old/faculty-legacy.php). Data: admin panel → Faculty Page.

$leaders = [];
$teachers = [];
if ($db_ok) {
    $r = $conn->query("SELECT id, name, position, image, experience, qualifications FROM faculty ORDER BY id");
    foreach ($r ? $r->fetch_all(MYSQLI_ASSOC) : [] as $row) {
        if (strcasecmp(trim(html_entity_decode((string) $row['position'])), 'Teacher') === 0) {
            $teachers[] = $row;
        } else {
            $leaders[] = $row;
        }
    }
}
?>
<?php page_banner('Faculty', 'The minds behind our excellence: dedicated educators who guide every child, every day.', [['Academics', ''], ['Faculty', '']]); ?>
<?php academics_nav(); ?>

<?php if ($leaders): ?>
<section class="sk-section">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Leadership</span>
            <h2>Leadership &amp; Management</h2>
            <span class="sk-rule"></span>
            <p>Tap the arrow on a photo to see experience and education.</p>
        </div>
        <div class="sk-teachers">
<?php foreach ($leaders as $t) echo faculty_card($t); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="sk-section sk-section--white">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Our teachers</span>
            <h2>Teaching Faculty</h2>
            <span class="sk-rule"></span>
        </div>
<?php if ($teachers): ?>
        <div class="sk-teachers">
<?php foreach ($teachers as $t) echo faculty_card($t); ?>
        </div>
<?php else: ?>
        <p class="sk-muted">Faculty profiles will appear here soon.</p>
<?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
