<?php
// includes/announcements.php : the announcements currently shown on the website.
// Rule (data/site.php 'announcement_days'): added in the last 30 days, newest first.
// Used by the News & Events page and the Home page pop-up, so both always agree.

function current_announcements(mysqli $conn, array $site, int $limit = 6): array
{
    $days = max(1, (int) ($site['announcement_days'] ?? 30));
    $since = date('Y-m-d H:i:s', strtotime("-$days days"));
    $stmt = $conn->prepare('SELECT id, image, heading, announcement_text, created_at FROM announcements
                            WHERE created_at >= ? ORDER BY created_at DESC, id DESC LIMIT ?');
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('si', $since, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/** Web path of an announcement picture, or '' if there is none / the file is missing. */
function announcement_image($file): string
{
    return upload_url((string) $file);
}
