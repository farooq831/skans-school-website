<?php
// chat-fees.php : this campus's fee table as JSON for the SKANS help chat ("Class 5 fee?").
// Same public numbers as the Fee Structure page (data/fees.php + admin Settings > Fee structure).
define('SK_QUIET', true);            // no session cookie, no visitor count
require __DIR__ . '/includes/bootstrap.php';

$fees = load_fees();
$columns = [];
foreach ($fees['columns'] as $key => $col) {
    $columns[] = ['key' => $key, 'label' => $col['label'], 'period' => $col['period'] ?? ''];
}
$classes = [];
foreach ($fees['classes'] as $row) {
    $item = ['class' => (string) $row['class']];
    foreach ($fees['columns'] as $key => $col) {
        $item[$key] = isset($row[$key]) && is_numeric($row[$key]) ? (int) $row[$key] : null;
    }
    $classes[] = $item;
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');          // the chat on the main site reads it too (public data)
header('Cache-Control: public, max-age=600');
header('X-Content-Type-Options: nosniff');
echo json_encode([
    'campus'   => $site['campus'] ?? '',
    'session'  => (string) $fees['session'],
    'currency' => (string) $fees['currency'],
    'columns'  => $columns,
    'classes'  => $classes,
], JSON_UNESCAPED_UNICODE);
