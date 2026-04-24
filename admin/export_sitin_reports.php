<?php
require_once "../includes/app.php";

require_role('admin');

$filters = get_sitin_report_filters($_GET);
$rows = fetch_admin_sitin_report_rows($conn, $filters);
$format = strtolower(trim($_GET['format'] ?? 'excel'));
$filename_stamp = date('Ymd_His');

if ($format === 'pdf') {
    $headers = ['ID NUMBER', 'NAME', 'PURPOSE', 'LABORATORY', 'LOGIN', 'LOGOUT', 'DATE'];
    $pdf_rows = [];

    foreach ($rows as $row) {
        $lab_name = trim((string) ($row['lab_name'] ?? ''));
        if ($lab_name !== '' && preg_match('/(\d+)/', $lab_name, $matches)) {
            $lab_name = $matches[1];
        }
        if ($lab_name === '') {
            $lab_name = 'N/A';
        }

        $pdf_rows[] = [
            $row['student_id'],
            $row['first_name'] . ' ' . $row['last_name'],
            $row['purpose'],
            $lab_name,
            date('h:i:s A', strtotime($row['time_in'])),
            $row['time_out'] ? date('h:i:s A', strtotime($row['time_out'])) : '--',
            date('Y-m-d', strtotime($row['time_in'])),
        ];
    }

    $pdf = build_simple_pdf('Admin Sit-In Reports', $headers, $pdf_rows);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="sitin_reports_' . $filename_stamp . '.pdf"');
    echo $pdf;
    exit();
}

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="sitin_reports_' . $filename_stamp . '.xls"');
?>
<table border="1">
    <thead>
        <tr>
            <th>ID NUMBER</th>
            <th>NAME</th>
            <th>PURPOSE</th>
            <th>LABORATORY</th>
            <th>LOGIN</th>
            <th>LOGOUT</th>
            <th>DATE</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
            <?php
            $lab_name = trim((string) ($row['lab_name'] ?? ''));
            if ($lab_name !== '' && preg_match('/(\d+)/', $lab_name, $matches)) {
                $lab_name = $matches[1];
            }
            if ($lab_name === '') {
                $lab_name = 'N/A';
            }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                <td><?php echo htmlspecialchars($lab_name); ?></td>
                <td><?php echo htmlspecialchars(date('h:i:s A', strtotime($row['time_in']))); ?></td>
                <td><?php echo htmlspecialchars($row['time_out'] ? date('h:i:s A', strtotime($row['time_out'])) : '--'); ?></td>
                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['time_in']))); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
