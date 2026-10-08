<?php
$page_title = 'Facilities';
$page_description = 'Facilities and co-curricular activities at SKANS School System, AECHS Campus, Rawalpindi.';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/photo-card.php';
// Rebuilt Oct 2026 (old page: _old/services-legacy.php). Data: admin panel → Facilities Page
// (services = facilities, activities = co-curricular activities).

$services = [];
$activities = [];
if ($db_ok) {
    $r = $conn->query('SELECT id, title, description, image FROM services ORDER BY id');
    $services = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    $r = $conn->query('SELECT id, title, description, image FROM activities ORDER BY id');
    $activities = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}
$first_batch = 12; // activities shown before "Show more"

function facility_card(array $row, string $prefix, string $label, bool $hidden = false): string
{
    return photo_card([
        'id'       => $prefix . '-' . (int) $row['id'],
        'image'    => $row['image'],
        'title'    => plain_text($row['title']),
        'subtitle' => $label,
        'details'  => '<p>' . nl2br(e(plain_text($row['description']))) . '</p>',
        'hidden'   => $hidden,
    ]);
}
?>
<!-- Intro: text on one side, picture on the other -->
<section class="sk-section sk-intro">
    <div class="sk-container sk-intro__grid">
        <div>
            <span class="sk-eyebrow">Facilities</span>
            <h1 class="sk-intro__title">Enhancing Learning with Our Facilities</h1>
            <span class="sk-rule"></span>
            <p>Enhance learning with our state-of-the-art recreational facilities, which include spacious playgrounds and creative activity zones. These spaces foster physical development, teamwork, and mental agility, ensuring students enjoy a balanced education.</p>
            <p>For our senior section, we offer advanced learning hubs equipped with the latest technology, quiet study areas, and collaborative workspaces. These facilities are designed to support focused study, critical thinking, and collaborative projects, preparing students for future academic challenges.</p>
            <div class="sk-actions">
                <a class="sk-btn sk-btn--primary" href="contact-us.php">Contact Us</a>
                <a class="sk-btn sk-btn--outline" href="#facilities">See facilities</a>
            </div>
        </div>
        <figure class="sk-intro__photo">
            <img src="assets/img/facilities-hero.jpg" width="720" height="960" alt="A SKANS student painting in the art corridor">
        </figure>
    </div>
</section>

<section class="sk-section sk-section--white sk-anchor" id="facilities">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Campus facilities</span>
            <h2>Student Services on Campus</h2>
            <span class="sk-rule"></span>
            <p>Comprehensive student services provided by the campus. Tap the arrow on a photo for details.</p>
        </div>
<?php if ($services): ?>
        <div class="sk-teachers">
<?php foreach ($services as $row) echo facility_card($row, 'svc', 'Facility'); ?>
        </div>
<?php else: ?>
        <p class="sk-muted">Facilities will appear here soon.</p>
<?php endif; ?>
    </div>
</section>

<section class="sk-section sk-anchor" id="activities">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Beyond the classroom</span>
            <h2>Co-curricular Activities</h2>
            <span class="sk-rule"></span>
        </div>
<?php if ($activities): ?>
        <div class="sk-teachers" id="activity-grid">
<?php foreach ($activities as $i => $row) echo facility_card($row, 'act', 'Activity', $i >= $first_batch); ?>
        </div>
<?php if (count($activities) > $first_batch): ?>
        <div class="sk-actions sk-actions--center">
            <button type="button" class="sk-btn sk-btn--outline" id="activity-more" data-step="<?= $first_batch ?>">
                Show more activities <span class="sk-muted-on-btn">(<span id="activity-left"><?= count($activities) - $first_batch ?></span> more)</span>
            </button>
        </div>
<?php endif; ?>
<?php else: ?>
        <p class="sk-muted">Activities will appear here soon.</p>
<?php endif; ?>
    </div>
</section>

<script>
// "Show more activities": reveals the next 12 cards each time.
(function () {
    var btn = document.getElementById('activity-more');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var hidden = document.querySelectorAll('#activity-grid > .sk-teacher[hidden]');
        var step = parseInt(btn.getAttribute('data-step'), 10) || 12;
        for (var i = 0; i < Math.min(step, hidden.length); i++) hidden[i].hidden = false;
        var left = hidden.length - step;
        if (left <= 0) { btn.parentNode.remove(); return; }
        document.getElementById('activity-left').textContent = left;
    });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
