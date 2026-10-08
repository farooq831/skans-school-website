<?php
// includes/faculty-card.php : one teacher card (photo + name; arrow opens experience and education).
// Uses the shared photo card (includes/photo-card.php). Used on faculty.php and the Home page.
require_once __DIR__ . '/photo-card.php';

/** Education text -> list items. Splits on new lines and ";" and, for short lists, on commas. */
function faculty_list($text): array
{
    $text = plain_text($text);
    if ($text === '') {
        return [];
    }
    $items = preg_split('/\s*(?:\R|;)\s*/', $text);
    $out = [];
    foreach ($items as $item) {
        $parts = array_map('trim', explode(',', $item));
        $short = count($parts) > 1 && max(array_map('mb_strlen', $parts)) <= 32
            && !array_filter($parts, function ($p) { return stripos($p, 'and ') === 0 || $p === ''; });
        foreach ($short ? $parts : [$item] as $p) {
            if ($p !== '') {
                $out[] = $p;
            }
        }
    }
    return $out;
}

function faculty_card(array $t, string $id_prefix = 'fac'): string
{
    $experience = plain_text($t['experience']);
    $education = faculty_list($t['qualifications']);
    $details = '';
    if ($experience !== '') {
        $details .= '<h4>Experience</h4><p>' . nl2br(e($experience)) . '</p>';
    }
    if ($education) {
        $details .= '<h4>Education</h4><ul>';
        foreach ($education as $item) {
            $details .= '<li>' . e($item) . '</li>';
        }
        $details .= '</ul>';
    }
    return photo_card([
        'id'       => $id_prefix . '-' . (int) $t['id'],
        'image'    => $t['image'],
        'title'    => plain_text($t['name']),
        'subtitle' => plain_text($t['position']),
        'details'  => $details,
        'fit'      => 'cover', // all faculty photos are portraits: fill the frame
    ]);
}
