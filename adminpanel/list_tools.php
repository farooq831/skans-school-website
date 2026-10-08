<?php
// adminpanel/list_tools.php : search box, filters, "per page" and page numbers for admin lists.
// Used by Manage News & Events, Manage Activities, Manage Announcements and Contact messages.

/**
 * Read the list options from the address bar.
 * $filters = ['name' => ['allowed', 'values'], ...]  (first value is the default)
 */
function lt_params(array $filters = [], array $sorts = ['newest']): array
{
    $p = [
        'q'    => mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100),
        'page' => max(1, (int) ($_GET['page'] ?? 1)),
        'per'  => in_array((int) ($_GET['per'] ?? 20), [10, 20, 50, 100], true) ? (int) ($_GET['per'] ?? 20) : 20,
        'sort' => in_array($_GET['sort'] ?? '', $sorts, true) ? $_GET['sort'] : $sorts[0],
    ];
    foreach ($filters as $name => $allowed) {
        $v = (string) ($_GET[$name] ?? '');
        $p[$name] = in_array($v, $allowed, true) ? $v : $allowed[0];
    }
    return $p;
}

/** Page numbers to show after counting the rows; also fixes a page number that is too high. */
function lt_pages(int $total, array &$p): array
{
    $pages = max(1, (int) ceil($total / $p['per']));
    $p['page'] = min($p['page'], $pages);
    return ['total' => $total, 'pages' => $pages, 'offset' => ($p['page'] - 1) * $p['per']];
}

/** Address of this list with some options changed (keeps the others). */
function lt_url(array $p, array $change = []): string
{
    $q = array_merge($p, $change);
    $q = array_filter($q, function ($v, $k) { return !($k === 'page' && (int) $v === 1) && $v !== '' && !($k === 'per' && (int) $v === 20); }, ARRAY_FILTER_USE_BOTH);
    return basename($_SERVER['SCRIPT_NAME']) . ($q ? '?' . http_build_query($q) : '');
}

/**
 * The search bar. $selects = [['name' => 'category', 'label' => 'Category', 'options' => ['' => 'All', 'News' => 'News']], ...]
 * $sorts = ['newest' => 'Newest first', 'oldest' => 'Oldest first', ...]
 */
function lt_search_bar(array $p, string $placeholder, array $selects = [], array $sorts = []): string
{
    $html = '<form method="GET" class="adm-filter" role="search">';
    $html .= '<div class="adm-filter__search"><i class="fas fa-magnifying-glass" aria-hidden="true"></i>'
           . '<input type="search" name="q" class="form-control" value="' . h($p['q']) . '" placeholder="' . h($placeholder) . '" aria-label="Search"></div>';
    foreach ($selects as $s) {
        $html .= '<select name="' . h($s['name']) . '" class="custom-select" aria-label="' . h($s['label']) . '" onchange="this.form.submit()">';
        foreach ($s['options'] as $value => $label) {
            $html .= '<option value="' . h($value) . '"' . ((string) $p[$s['name']] === (string) $value ? ' selected' : '') . '>' . h($label) . '</option>';
        }
        $html .= '</select>';
    }
    if ($sorts) {
        $html .= '<select name="sort" class="custom-select" aria-label="Sort" onchange="this.form.submit()">';
        foreach ($sorts as $value => $label) {
            $html .= '<option value="' . h($value) . '"' . ($p['sort'] === $value ? ' selected' : '') . '>' . h($label) . '</option>';
        }
        $html .= '</select>';
    }
    $html .= '<select name="per" class="custom-select adm-filter__per" aria-label="Rows per page" onchange="this.form.submit()">';
    foreach ([10, 20, 50, 100] as $n) {
        $html .= '<option value="' . $n . '"' . ($p['per'] === $n ? ' selected' : '') . '>' . $n . ' per page</option>';
    }
    $html .= '</select><button type="submit" class="btn btn-primary"><i class="fas fa-magnifying-glass"></i> Search</button>';
    $active = $p['q'] !== '';
    foreach ($selects as $s) {
        if ((string) $p[$s['name']] !== (string) array_key_first($s['options'])) $active = true;
    }
    if ($active) {
        $html .= '<a class="btn btn-link" href="' . h(basename($_SERVER['SCRIPT_NAME'])) . '"><i class="fas fa-xmark"></i> Clear</a>';
    }
    return $html . '</form>';
}

/** "Showing 21–40 of 112" + page buttons. */
function lt_pagination(array $p, array $info, string $noun = 'items'): string
{
    $total = $info['total'];
    if ($total === 0) {
        return '';
    }
    $from = $info['offset'] + 1;
    $to = min($total, $info['offset'] + $p['per']);
    $html = '<div class="adm-pager"><span class="adm-pager__info">Showing <strong>' . $from . '–' . $to . '</strong> of <strong>' . $total . '</strong> ' . h($noun) . '</span>';
    if ($info['pages'] > 1) {
        $html .= '<nav aria-label="Pages"><ul class="pagination pagination-sm mb-0">';
        $cur = $p['page'];
        $html .= '<li class="page-item' . ($cur === 1 ? ' disabled' : '') . '"><a class="page-link" href="' . h(lt_url($p, ['page' => $cur - 1])) . '">‹ Previous</a></li>';
        $shown = [];
        for ($i = 1; $i <= $info['pages']; $i++) {
            if ($i === 1 || $i === $info['pages'] || abs($i - $cur) <= 2) {
                $shown[] = $i;
            }
        }
        $prev = 0;
        foreach ($shown as $i) {
            if ($prev && $i > $prev + 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            $html .= '<li class="page-item' . ($i === $cur ? ' active" aria-current="page' : '') . '"><a class="page-link" href="' . h(lt_url($p, ['page' => $i])) . '">' . $i . '</a></li>';
            $prev = $i;
        }
        $html .= '<li class="page-item' . ($cur === $info['pages'] ? ' disabled' : '') . '"><a class="page-link" href="' . h(lt_url($p, ['page' => $cur + 1])) . '">Next ›</a></li>';
        $html .= '</ul></nav>';
    }
    return $html . '</div>';
}

/** Run a COUNT(*) and a page of rows with the same WHERE (prepared statements). */
function lt_query(mysqli $conn, string $from_where, string $types, array $params, string $order, array &$p, string $select = '*'): array
{
    $count = $conn->prepare("SELECT COUNT(*) FROM $from_where");
    if ($types !== '') $count->bind_param($types, ...$params);
    $count->execute();
    $count->bind_result($total);
    $count->fetch();
    $count->close();
    $info = lt_pages((int) $total, $p);

    $stmt = $conn->prepare("SELECT $select FROM $from_where ORDER BY $order LIMIT ? OFFSET ?");
    $all_types = $types . 'ii';
    $all_params = array_merge($params, [$p['per'], $info['offset']]);
    $stmt->bind_param($all_types, ...$all_params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return [$rows, $info];
}

/** "%word%" for LIKE searches, with % and _ typed by the user treated as normal characters. */
function lt_like(string $q): string
{
    return '%' . addcslashes($q, '%_\\') . '%';
}
