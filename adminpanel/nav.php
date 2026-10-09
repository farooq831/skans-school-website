<?php
// adminpanel/nav.php : top menu of the admin panel (included by every admin page).
// Restyled Oct 2026; all previous links kept, plus Dashboard, Settings and View website.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$adm_here = basename($_SERVER['SCRIPT_NAME'] ?? '');
$adm_groups = [
    'admin_panel.php'      => ['admin_panel.php'],
    'facilities.php'       => ['facilities.php', 'add_service.php', 'Manage_services.php', 'edit_service.php', 'delete_service.php', 'add_activity.php', 'manage_activities.php', 'edit_activity.php', 'delete_activity.php'],
    'events.php'           => ['events.php', 'add_news.php', 'manage_news_events.php', 'edit_news_event.php', 'add_announcement.php', 'view_announcements.php', 'edit_announcement.php'],
    'staff.php'            => ['staff.php', 'add_faculty.php', 'manage_faculty.php', 'edit_faculty.php'],
    'academic_programs.php'=> ['academic_programs.php', 'add_classes.php', 'manage_classes.php', 'edit_classes.php'],
    'admission_page.php'   => ['admission_page.php', 'view_contactus.php', 'view_addmissions.php', 'admission_summary.php'],
    'settings.php'         => ['settings.php', 'admission_policy_edit.php', 'backups.php', 'fees_edit.php', 'principal_edit.php', 'classes_edit.php'],
];
$adm_active = function (string $key) use ($adm_here, $adm_groups): string {
    return in_array($adm_here, $adm_groups[$key] ?? [$key], true) ? ' active' : '';
};
$adm_css_v = @filemtime(__DIR__ . '/admin.css') ?: 1;

/** Contact messages not answered yet (no remarks), for the red badge on the messages icon. */
function adm_pending_messages(): int
{
    static $n = null;
    if ($n !== null) return $n;
    include __DIR__ . '/db.php';   // own connection: pages may have closed theirs already
    $n = 0;
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $r = $conn->query("SELECT COUNT(*) c FROM contact_form_submissions WHERE remarks IS NULL OR remarks = ''");
        $n = $r ? (int) $r->fetch_assoc()['c'] : 0;
        $conn->close();
    }
    return $n;
}
$adm_pending = adm_pending_messages();
?>
<link rel="stylesheet" href="admin.css?v=<?= $adm_css_v ?>">
<meta name="robots" content="noindex">
<nav class="navbar navbar-expand-xl adm-nav">
    <a class="navbar-brand adm-brand" href="admin_panel.php">
        <img src="../assets/img/skans-logo-60.png" srcset="../assets/img/skans-logo-120.png 2x" alt="" width="126" height="60">
        <span>Admin Dashboard</span>
    </a>
    <button class="navbar-toggler adm-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fas fa-bars"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link<?= $adm_active('admin_panel.php') ?>" href="admin_panel.php"><i class="fas fa-gauge"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link<?= $adm_active('facilities.php') ?>" href="facilities.php"><i class="fas fa-school"></i> Facilities Page</a></li>
            <li class="nav-item"><a class="nav-link<?= $adm_active('events.php') ?>" href="events.php"><i class="fas fa-newspaper"></i> News &amp; Events Page</a></li>
            <li class="nav-item"><a class="nav-link<?= $adm_active('staff.php') ?>" href="staff.php"><i class="fas fa-chalkboard-user"></i> Faculty Page</a></li>
<?php if (campus_feature('academic_programs_admin')): ?>
            <li class="nav-item"><a class="nav-link<?= $adm_active('academic_programs.php') ?>" href="academic_programs.php"><i class="fas fa-graduation-cap"></i> Academic Programs</a></li>
<?php endif; ?>
            <li class="nav-item">
                <a class="nav-link adm-msg<?= in_array($adm_here, ['view_contactus.php'], true) ? ' active' : '' ?>" href="view_contactus.php"
                   title="<?= $adm_pending ? $adm_pending . ' message' . ($adm_pending === 1 ? '' : 's') . ' waiting for a reply' : 'No new messages' ?>">
                    <span class="adm-msg__icon"><i class="fas fa-envelope"></i><?php if ($adm_pending): ?><span class="adm-msg__badge"><?= $adm_pending > 99 ? '99+' : $adm_pending ?></span><?php endif; ?></span>
                    <span class="adm-msg__label">Messages</span>
                    <span class="sr-only"><?= $adm_pending ?> pending</span>
                </a>
            </li>
            <li class="nav-item"><a class="nav-link<?= $adm_active('admission_page.php') ?>" href="admission_page.php"><i class="fas fa-envelope-open-text"></i> Admissions &amp; Contact-us Form</a></li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle<?= $adm_active('settings.php') ?>" href="#" id="settingsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-gear"></i> Settings</a>
                <div class="dropdown-menu" aria-labelledby="settingsDropdown">
                    <a class="dropdown-item" href="settings.php"><i class="fas fa-address-card"></i> School &amp; contact details</a>
                    <a class="dropdown-item" href="classes_edit.php"><i class="fas fa-children"></i> Classes &amp; ages</a>
                    <a class="dropdown-item" href="fees_edit.php"><i class="fas fa-money-bill-wave"></i> Fee structure</a>
                    <a class="dropdown-item" href="admission_policy_edit.php"><i class="fas fa-file-signature"></i> Admission policy</a>
                    <a class="dropdown-item" href="principal_edit.php"><i class="fas fa-user-tie"></i> Principal's message</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="backups.php"><i class="fas fa-database"></i> Backups</a>
                </div>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="../index.php" target="_blank" rel="noopener"><i class="fas fa-arrow-up-right-from-square"></i><span class="adm-hide-md"> View website</span></a></li>
            <?php if (isset($_SESSION['username'])) { $username = $_SESSION['username']; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle"></i>&nbsp;<?php echo h($username); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="login_history.php"><i class="fas fa-history"></i> Login History</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
</nav>

<?php include __DIR__ . "/upload_info.php"; // size limit + file size/pixels under every picture field ?>
