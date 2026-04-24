<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function normalize_path($path)
{
    return str_replace('\\', '/', (string) $path);
}

function app_base_path()
{
    static $base_path = null;

    if ($base_path !== null) {
        return $base_path;
    }

    $document_root = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $app_root = realpath(__DIR__ . '/..');

    if ($document_root && $app_root) {
        $document_root = rtrim(normalize_path($document_root), '/');
        $app_root = normalize_path($app_root);

        if (strpos($app_root, $document_root) === 0) {
            $relative_path = substr($app_root, strlen($document_root));
            $base_path = rtrim($relative_path, '/');
            return $base_path;
        }
    }

    $base_path = '';
    return $base_path;
}

function app_url($path = '')
{
    $base_path = app_base_path();
    $path = ltrim($path, '/');

    if ($path === '') {
        return $base_path !== '' ? $base_path . '/' : '/';
    }

    return ($base_path !== '' ? $base_path . '/' : '/') . $path;
}

function asset_url($path)
{
    return app_url($path);
}

function redirect_with_message($path, $type, $message)
{
    header('Location: ' . app_url($path) . '?' . $type . '=' . urlencode($message));
    exit();
}

function redirect_with_message_preserving_query($path, $type, $message, array $query = [])
{
    $query[$type] = $message;
    header('Location: ' . app_url($path) . '?' . http_build_query($query));
    exit();
}

function require_role($role)
{
    if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== $role) {
        redirect_with_message('index.php', 'error', "Please log in as {$role}");
    }
}

function expire_overdue_sitin_records(mysqli $conn)
{
    $conn->query("
        UPDATE sitin_records
        SET status='Expired', time_out=NOW(), remarks=IFNULL(remarks, 'Session expired')
        WHERE status='Approved' AND time_out IS NULL AND session_end IS NOT NULL AND NOW() > session_end
    ");
}

function get_latest_settings(mysqli $conn)
{
    $result = $conn->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");

    if ($result && $result->num_rows === 1) {
        return $result->fetch_assoc();
    }

    return ['sitin_time_limit_minutes' => 60];
}

function ensure_announcements_table(mysqli $conn)
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS announcements (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(150) NOT NULL DEFAULT 'Announcements',
            content TEXT NOT NULL,
            author_name VARCHAR(100) NOT NULL DEFAULT 'CCS Admin',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function migrate_legacy_announcement(mysqli $conn, $filename = 'announcements.txt')
{
    ensure_announcements_table($conn);

    $check = $conn->query("SELECT COUNT(*) AS total FROM announcements");
    $count = $check ? (int) $check->fetch_assoc()['total'] : 0;

    if ($count > 0) {
        return;
    }

    $full_path = __DIR__ . '/../' . ltrim($filename, '/');

    if (!file_exists($full_path)) {
        return;
    }

    $content = trim((string) file_get_contents($full_path));

    if ($content === '') {
        return;
    }

    $stmt = $conn->prepare("INSERT INTO announcements (title, content, author_name) VALUES ('Announcements', ?, 'CCS Admin')");
    $stmt->bind_param("s", $content);
    $stmt->execute();
}

function get_announcements(mysqli $conn)
{
    ensure_announcements_table($conn);
    migrate_legacy_announcement($conn);

    $result = $conn->query("
        SELECT id, title, content, author_name, created_at, updated_at
        FROM announcements
        ORDER BY created_at DESC, id DESC
    ");

    if (!$result) {
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_latest_announcement(mysqli $conn)
{
    $announcements = get_announcements($conn);
    return $announcements[0] ?? null;
}

function get_photo_upload_directory()
{
    if (is_dir(__DIR__ . '/../Images')) {
        return 'Images';
    }

    return 'uploads';
}

function default_sitin_sessions()
{
    return 30;
}

function bind_stmt_params(mysqli_stmt $stmt, $types, array $params)
{
    if ($types === '' || empty($params)) {
        return;
    }

    $references = [];
    foreach ($params as $index => $value) {
        $references[$index] = &$params[$index];
    }

    array_unshift($references, $types);
    call_user_func_array([$stmt, 'bind_param'], $references);
}

function get_sitin_report_filters(array $source)
{
    $status = trim($source['status'] ?? '');
    $allowed_statuses = ['Pending', 'Approved', 'Rejected', 'Completed', 'Expired'];

    if (!in_array($status, $allowed_statuses, true)) {
        $status = '';
    }

    return [
        'search' => trim($source['search'] ?? ''),
        'status' => $status,
        'lab_id' => max(0, (int) ($source['lab_id'] ?? 0)),
        'date_from' => trim($source['date_from'] ?? ''),
        'date_to' => trim($source['date_to'] ?? ''),
    ];
}

function fetch_lab_options(mysqli $conn)
{
    $result = $conn->query("SELECT id, lab_name FROM labs ORDER BY lab_name ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function build_sitin_report_where_clause(array $filters)
{
    $conditions = [];
    $types = '';
    $params = [];

    if ($filters['search'] !== '') {
        $conditions[] = "(u.student_id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.course LIKE ? OR s.purpose LIKE ?)";
        $like = '%' . $filters['search'] . '%';
        $types .= 'sssss';
        array_push($params, $like, $like, $like, $like, $like);
    }

    if ($filters['status'] !== '') {
        $conditions[] = "s.status = ?";
        $types .= 's';
        $params[] = $filters['status'];
    }

    if ($filters['lab_id'] > 0) {
        $conditions[] = "s.lab_id = ?";
        $types .= 'i';
        $params[] = $filters['lab_id'];
    }

    if ($filters['date_from'] !== '' && $filters['date_to'] !== '') {
        $conditions[] = "DATE(s.time_in) BETWEEN ? AND ?";
        $types .= 'ss';
        $params[] = $filters['date_from'];
        $params[] = $filters['date_to'];
    } elseif ($filters['date_from'] !== '') {
        $conditions[] = "DATE(s.time_in) = ?";
        $types .= 's';
        $params[] = $filters['date_from'];
    } elseif ($filters['date_to'] !== '') {
        $conditions[] = "DATE(s.time_in) = ?";
        $types .= 's';
        $params[] = $filters['date_to'];
    }

    $where_sql = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

    return [$where_sql, $types, $params];
}

function fetch_admin_sitin_report_rows(mysqli $conn, array $filters)
{
    [$where_sql, $types, $params] = build_sitin_report_where_clause($filters);

    $sql = "
        SELECT
            s.id,
            s.user_id,
            s.lab_id,
            s.purpose,
            s.status,
            s.computer_number,
            s.remarks,
            s.time_in,
            s.approved_at,
            s.session_end,
            s.time_out,
            u.student_id,
            u.first_name,
            u.last_name,
            u.course,
            u.sitin_remaining,
            l.lab_name
        FROM sitin_records s
        INNER JOIN users u ON s.user_id = u.id
        LEFT JOIN labs l ON s.lab_id = l.id
        {$where_sql}
        ORDER BY s.time_in DESC, s.id DESC
    ";

    $stmt = $conn->prepare($sql);
    bind_stmt_params($stmt, $types, $params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_students_for_session_reset(mysqli $conn, $search = '')
{
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = $conn->prepare("
            SELECT id, student_id, first_name, last_name, course, sitin_remaining
            FROM users
            WHERE role = 'student'
              AND (student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR course LIKE ?)
            ORDER BY last_name ASC, first_name ASC
            LIMIT 30
        ");
        $stmt->bind_param('ssss', $like, $like, $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    $result = $conn->query("
        SELECT id, student_id, first_name, last_name, course, sitin_remaining
        FROM users
        WHERE role = 'student'
        ORDER BY last_name ASC, first_name ASC
        LIMIT 12
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function build_admin_sitin_report_summary(array $rows)
{
    $unique_students = [];
    $summary = [
        'total_logs' => count($rows),
        'approved_count' => 0,
        'completed_count' => 0,
        'active_count' => 0,
        'unique_students' => 0,
    ];

    foreach ($rows as $row) {
        $unique_students[$row['user_id']] = true;

        if ($row['status'] === 'Approved') {
            $summary['approved_count']++;
            if (empty($row['time_out'])) {
                $summary['active_count']++;
            }
        }

        if ($row['status'] === 'Completed') {
            $summary['completed_count']++;
        }
    }

    $summary['unique_students'] = count($unique_students);
    return $summary;
}

function pdf_escape_text($text)
{
    $text = (string) $text;
    $text = preg_replace('/[^\x20-\x7E]/', '?', $text);
    $text = str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $text);
    return $text;
}

function build_simple_pdf($title, array $headers, array $rows)
{
    $column_widths = [8, 12, 20, 14, 12, 14, 14, 14];
    $lines = [];
    $lines[] = $title;
    $lines[] = 'Generated: ' . date('M d, Y h:i A');
    $lines[] = '';

    $header_line = [];
    foreach ($headers as $index => $header) {
        $width = $column_widths[$index] ?? 15;
        $header_line[] = str_pad(substr($header, 0, $width), $width);
    }
    $lines[] = implode(' ', $header_line);
    $lines[] = str_repeat('-', 110);

    foreach ($rows as $row) {
        $formatted = [];
        foreach ($row as $index => $value) {
            $width = $column_widths[$index] ?? 15;
            $text = trim((string) $value);
            if (strlen($text) > $width) {
                $text = substr($text, 0, max(1, $width - 3)) . '...';
            }
            $formatted[] = str_pad($text, $width);
        }
        $lines[] = implode(' ', $formatted);
    }

    if (count($rows) === 0) {
        $lines[] = 'No sit-in report records matched the selected filters.';
    }

    $lines_per_page = 42;
    $pages = array_chunk($lines, $lines_per_page);

    $objects = [];
    $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

    $kids = [];
    $object_id = 4;

    foreach ($pages as $page_lines) {
        $page_object_id = $object_id++;
        $content_object_id = $object_id++;
        $kids[] = $page_object_id . ' 0 R';

        $content = "BT\n/F1 10 Tf\n50 780 Td\n14 TL\n";
        foreach ($page_lines as $line) {
            $content .= '(' . pdf_escape_text($line) . ") Tj\nT*\n";
        }
        $content .= "ET";

        $objects[$content_object_id] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        $objects[$page_object_id] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R >> >> /Contents {$content_object_id} 0 R >>";
    }

    $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
    ksort($objects);

    $pdf = "%PDF-1.4\n";
    $offsets = [];

    foreach ($objects as $id => $body) {
        $offsets[$id] = strlen($pdf);
        $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
    }

    $xref_offset = strlen($pdf);
    $pdf .= "xref\n0 " . (max(array_keys($objects)) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";

    for ($i = 1; $i <= max(array_keys($objects)); $i++) {
        $offset = $offsets[$i] ?? 0;
        $pdf .= sprintf("%010d 00000 n \n", $offset);
    }

    $pdf .= "trailer\n<< /Size " . (max(array_keys($objects)) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n{$xref_offset}\n%%EOF";

    return $pdf;
}
?>
