<?php
// includes/settings.php : settings the admin can change in the admin panel (Settings and
// Admission Policy pages). Stored in the database table `site_settings` (name -> JSON value).
// The website reads them on every page, so a change shows immediately.
// Anything not saved yet falls back to the defaults in data/site.php and data/fees.php.

/** Create the settings table and the news "created_at" column if they are missing (safe to call often). */
function settings_ensure_schema(mysqli $conn): void
{
    $conn->query("CREATE TABLE IF NOT EXISTS site_settings (
        name VARCHAR(64) NOT NULL PRIMARY KEY,
        value MEDIUMTEXT NOT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) DEFAULT CHARSET=utf8mb4");
    // When a news post was ADDED (its "date" is the event date). Old posts stay NULL, so they
    // never count toward the monthly post limit.
    $col = $conn->query("SHOW COLUMNS FROM news_events LIKE 'created_at'");
    if ($col && $col->num_rows === 0) {
        $conn->query("ALTER TABLE news_events ADD COLUMN created_at DATETIME NULL DEFAULT NULL");
    }
}

/** All saved settings as [name => value]. Returns [] if the table does not exist yet. */
function settings_all(mysqli $conn): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    $r = @$conn->query('SELECT name, value FROM site_settings');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $cache[$row['name']] = json_decode($row['value'], true);
        }
    }
    return $cache;
}

function settings_save(mysqli $conn, string $name, $value): bool
{
    // Never store "false": broken characters are replaced instead of losing the whole value.
    $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        return false;
    }
    $stmt = $conn->prepare('INSERT INTO site_settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
    $stmt->bind_param('ss', $name, $json);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function settings_delete(mysqli $conn, string $name): void
{
    $stmt = $conn->prepare('DELETE FROM site_settings WHERE name = ?');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->close();
}

/** Turn "+92 51 8468475" into "+92518468475" for tel: links. */
function phone_to_tel(string $display): string
{
    $digits = preg_replace('/[^0-9+]/', '', $display);
    return $digits;
}

/** Apply saved school & contact settings on top of data/site.php. */
function apply_site_settings(array $site, array $s): array
{
    $map = [
        'school_name' => 'name', 'campus_name' => 'campus', 'classes_line' => 'classes',
        'address' => 'address', 'map_url' => 'map_url', 'email' => 'email', 'whatsapp' => 'whatsapp',
        'facebook' => 'facebook', 'instagram' => 'instagram', 'youtube' => 'youtube', 'tiktok' => 'tiktok',
    ];
    $optional = ['facebook', 'instagram', 'youtube', 'tiktok']; // empty = hide that link
    foreach ($map as $key => $field) {
        if (!isset($s[$key]) || !is_string($s[$key])) {
            continue;
        }
        $value = trim($s[$key]);
        if ($value === '' && !in_array($key, $optional, true)) {
            continue; // never blank out the school name, address, email… keep the default instead
        }
        $site[$field] = $value;
    }
    if (!empty($s['phone_office']) || !empty($s['phone_mobile'])) {
        $phones = [];
        if (!empty($s['phone_office'])) {
            $phones[] = ['label' => 'Office', 'display' => $s['phone_office'], 'tel' => phone_to_tel($s['phone_office'])];
        }
        if (!empty($s['phone_mobile'])) {
            $phones[] = ['label' => 'Mobile / WhatsApp', 'display' => $s['phone_mobile'], 'tel' => phone_to_tel($s['phone_mobile'])];
        }
        $site['phones'] = $phones;
    }
    $site['full_name'] = $site['name'] . ', ' . $site['campus'];
    $site['post_limit'] = isset($s['post_limit']) ? max(1, (int) $s['post_limit']) : 8;
    return $site;
}

/** Fees + admission policy: data/fees.php, with the admin's saved policy text on top. */
function load_fees(): array
{
    global $conn, $db_ok;
    $fees = require SK_ROOT . '/data/fees.php';
    if (!empty($db_ok)) {
        $s = settings_all($conn);
        if (!empty($s['admission_policy']) && is_array($s['admission_policy'])) {
            foreach (['general', 'documents', 'attendance', 'leaving'] as $k) {
                if (isset($s['admission_policy'][$k]) && is_array($s['admission_policy'][$k])) {
                    $fees['admission_policy'][$k] = $s['admission_policy'][$k];
                }
            }
            if (isset($s['admission_policy']['declaration']) && is_string($s['admission_policy']['declaration'])) {
                $fees['admission_policy']['declaration'] = $s['admission_policy']['declaration'];
            }
            if (isset($s['admission_policy']['fee_rules']) && is_array($s['admission_policy']['fee_rules'])) {
                $fees['fee_rules'] = $s['admission_policy']['fee_rules'];
            }
        }
    }
    // Fee table edited in the admin panel (Settings → Fee structure): session year and amounts per class.
    if (!empty($db_ok)) {
        $s = settings_all($conn);
        if (!empty($s['fees']) && is_array($s['fees'])) {
            if (!empty($s['fees']['session']) && is_string($s['fees']['session'])) {
                $fees['session'] = $s['fees']['session'];
            }
            if (!empty($s['fees']['classes']) && is_array($s['fees']['classes'])) {
                $fees['classes'] = $s['fees']['classes'];
            }
        }
    }
    $fees['classes'] = fees_for_classes($fees['classes'], class_order(), $fees['columns']); // this campus's classes
    return $fees;
}

/**
 * Swap the AECHS wording written in the page texts for THIS campus's name and city
 * (campus-data/<campus>/config.php 'site', or admin Settings). On AECHS nothing changes.
 */
function campus_text(string $text, ?array $site = null): string
{
    $site = $site ?? ($GLOBALS['site'] ?? null);
    if (!$site) {
        return $text;
    }
    $max = $site['max_class'] ?? 'Class 10';
    $upto = $max === 'Class 10' ? 'Class 10 (Matric)' : $max;
    $min = $site['min_class'] ?? 'Playgroup';                 // first class at this campus
    $first = $site['first_level'] ?? 'Early Years';           // first level card
    return str_replace(
        ['SKANS School System, AECHS Campus', 'SKANS School System - AECHS Campus', 'SKANS AECHS Campus', 'AECHS Campus', 'Rawalpindi',
         // class range written in the page texts: follows "Highest class at this campus"
         'Playgroup to Class 10 (Matric)', 'Playgroup to Class 10', 'Playgroup to Matric', 'Early Years to Matric',
         'Seniors (Class 6–10, Matric)'],
        [$site['full_name'], $site['name'] . ' - ' . $site['campus'], 'SKANS ' . $site['campus'], $site['campus'], $site['city'] ?? 'Rawalpindi',
         $min . ' to ' . $upto, $min . ' to ' . $max, $min . ' to ' . ($max === 'Class 10' ? 'Matric' : $max),
         $first . ' to ' . ($max === 'Class 10' ? 'Matric' : $max),
         'Seniors (Class 6–' . ($max === 'Class 10' ? '10, Matric' : preg_replace('/^Class /', '', $max)) . ')'],
        $text
    );
}

/**
 * Principal's message on the About page: the original text below, with the admin's saved version
 * (admin panel → Settings → Principal's message, site_settings 'principal') on top.
 * 'photo' is a file in this campus's pictures folder ('' = the original photo assets/img/about-principal.jpg).
 */
function principal_defaults(): array
{
    return [
        'heading'    => 'Greetings From the Principal',
        'paragraphs' => [
            'Welcome to our SKANS School System - AECHS Campus. Our focus is on creating a balanced environment where students excel academically, grow morally, and develop essential life skills.',
            "We value the strong partnership with parents in shaping the future of our children and believe in nurturing each child's potential through innovative teaching methods in a supportive and nurturing atmosphere.",
            "We warmly invite you to explore our website, visit our campus, and experience the unique SKANS experience. Together, let's empower our students to realize their full potential and pave the way for a brighter future.",
        ],
        'name'       => '',          // e.g. "Mrs Ayesha Khan" (empty = only the title is shown)
        'title'      => 'Principal',
        'photo'      => '',
        'photo_alt'  => 'A young SKANS student smiling during an art activity',
        'photo_pos'  => 'center',    // which part of the photo stays visible: top / center / bottom
    ];
}

function load_principal(): array
{
    global $conn, $db_ok;
    $p = principal_defaults();
    $p['paragraphs'] = array_map('campus_text', $p['paragraphs']); // default text names this campus
    if (!empty($db_ok)) {
        $s = settings_all($conn)['principal'] ?? null;
        if (is_array($s)) {
            foreach ($p as $k => $v) {
                if (!isset($s[$k]) || gettype($s[$k]) !== gettype($v)) continue;
                if (in_array($k, ['heading', 'paragraphs', 'title'], true) && empty($s[$k])) continue; // never blank
                $p[$k] = $s[$k];
            }
        }
    }
    $p['photo_src'] = upload_url($p['photo']) ?: 'assets/img/about-principal.jpg';
    if (!in_array($p['photo_pos'], ['top', 'center', 'bottom'], true)) $p['photo_pos'] = 'center';
    return $p;
}

/**
 * The 3 level cards (Early Years, Juniors, Seniors) on the Home, Admissions and Programs pages:
 * data/site.php, with the admin panel's Academic Programs entries #1, #2 and #3 on top.
 *   Class name  -> card title          Title -> line under it (e.g. "Playgroup to Class 2")
 *   Description -> key objectives, one per line            Image -> card picture
 * Ages per class and the exam board stay in data/site.php.
 */
function load_levels(array $levels): array
{
    global $conn, $db_ok;
    if (empty($db_ok)) {
        return $levels;
    }
    $r = @$conn->query('SELECT id, heading, name, description, image FROM academic_prog WHERE id BETWEEN 1 AND ' . max(3, count($levels)));
    if (!$r) {
        return $levels;
    }
    // "EARLY YEARS " -> "Early Years" (text typed all in capitals is shown in normal case)
    $tidy = function ($text): string {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $text));
        return ($text !== '' && $text === mb_strtoupper($text)) ? mb_convert_case(mb_strtolower($text), MB_CASE_TITLE) : $text;
    };
    while ($row = $r->fetch_assoc()) {
        $i = (int) $row['id'] - 1;
        if (!isset($levels[$i])) {
            continue;
        }
        if (($name = $tidy($row['name'])) !== '') {
            $levels[$i]['name'] = $name;
        }
        if (($range = $tidy($row['heading'])) !== '') {
            $levels[$i]['range'] = $range;
            $levels[$i]['range_from_admin'] = true;      // kept: Settings > Classes & ages does not overwrite it
        }
        // One objective per line. Older entries are one line with commas ("Develop phonics, Develop social
        // skills, ...") -> then split on the commas.
        $desc = trim((string) $row['description']);
        $parts = preg_split('/\R/u', $desc);
        if (count(array_filter(array_map('trim', $parts), 'strlen')) <= 1 && strpos($desc, ',') !== false) {
            $parts = explode(',', $desc);
        }
        $points = array_values(array_filter(array_map(function ($line) {
            return trim($line, " \t,.");
        }, $parts), 'strlen'));
        if ($points) {
            $levels[$i]['objectives'] = $points;
        }
        if ($src = upload_url(basename((string) $row['image']))) {
            $levels[$i]['image'] = $src;
        }
    }
    return $levels;
}

/**
 * Classes taught at this campus, in order: [['class' => 'Playgroup', 'age' => '2.5+ years', 'level' => 0], ...]
 * level = 0 Early Years, 1 Juniors, 2 Seniors (the 3 level cards).
 * Admin: Settings > Classes & ages (site_settings 'class_list'); otherwise the classes in data/site.php.
 */
function default_class_list(array $levels): array
{
    $list = [];
    foreach ($levels as $i => $level) {
        foreach ($level['classes'] ?? [] as $c) {
            $list[] = ['class' => (string) $c[0], 'age' => (string) ($c[1] ?? ''), 'level' => $i];
        }
    }
    return $list;
}

function campus_class_list(?mysqli $db = null): array
{
    global $conn, $db_ok;
    $db = $db ?? (!empty($db_ok) ? $conn : null);
    $saved = $db ? (settings_all($db)['class_list'] ?? null) : null;
    if (is_array($saved) && $saved) {
        return array_values($saved);
    }
    $site = require SK_ROOT . '/data/site.php';
    return default_class_list($site['levels']);
}

/** Class names in order (fee table, fee editor). */
function class_order(?mysqli $db = null): array
{
    return array_column(campus_class_list($db), 'class');
}

/**
 * Put this campus's classes into the 3 level cards and the age table. When the admin has saved
 * their own list, each card's line is written from it ("Class 6 to Class 8"), a level without
 * classes is not shown, and the exam board only appears if Class 9 or 10 is in that level.
 */
function apply_class_list(array $site, array $list, bool $custom): array
{
    $levels = [];
    foreach ($site['levels'] as $i => $level) {
        $rows = array_values(array_filter($list, function ($r) use ($i) { return (int) $r['level'] === $i; }));
        if (!$rows) {
            continue;
        }
        $level['classes'] = array_map(function ($r) { return [$r['class'], $r['age']]; }, $rows);
        if ($custom && empty($level['range_from_admin'])) {
            $first = $rows[0]['class'];
            $last = $rows[count($rows) - 1]['class'];
            $level['range'] = $first === $last ? $first : $first . ' to ' . $last;
        }
        if ($custom && !array_intersect(array_map('class_key', array_column($rows, 'class')), ['class 9', 'class 10'])) {
            unset($level['board']);                // exam board only with Class 9 / 10
        }
        $levels[] = $level;
    }
    $site['levels'] = $levels;
    $site['min_class'] = $list ? trim(preg_replace('/\s*\(.*\)\s*$/u', '', $list[0]['class'])) : 'Playgroup';
    $site['first_level'] = $levels ? $levels[0]['name'] : 'Early Years';
    $site['max_class'] = $list ? trim(preg_replace('/\s*\(.*\)\s*$/u', '', $list[count($list) - 1]['class'])) : 'Class 10'; // campus_text()
    return $site;
}

/** "Class 10 (Matric)" -> "class 10": compare class names without a note in brackets or capitals. */
function class_key(string $name): string
{
    return mb_strtolower(trim(preg_replace('/\s*\(.*\)\s*$/u', '', $name)));
}

/** Fee rows in the order of this campus's classes; a class without saved fees gets empty amounts. */
function fees_for_classes(array $fee_rows, array $classes, array $columns): array
{
    $by_name = [];
    foreach ($fee_rows as $row) {
        $by_name[class_key((string) ($row['class'] ?? ''))] = $row;
    }
    $out = [];
    foreach ($classes as $name) {
        $out[] = $by_name[class_key($name)] ?? (['class' => preg_replace('/\s*\(.*\)\s*$/u', '', $name)] + array_fill_keys(array_keys($columns), null));
    }
    return $out;
}

/** Number of level cards: 3 (Early Years, Juniors, Seniors) or more if Classes & ages uses card 4, 5 or 6. */
function campus_level_count(array $list): int
{
    $max = 2;
    foreach ($list as $r) {
        $max = max($max, (int) ($r['level'] ?? 0));
    }
    return min(6, $max + 1);
}

/** Add cards 4-6 when a campus needs them (text and picture come from Academic Programs #4-#6). */
function pad_levels(array $levels, int $count): array
{
    for ($i = count($levels); $i < $count; $i++) {
        $levels[] = ['id' => 'level-' . ($i + 1), 'name' => 'Card ' . ($i + 1), 'range' => '', 'classes' => [],
                     'image' => 'assets/img/level-seniors.jpeg', 'objectives' => []];
    }
    return $levels;
}
