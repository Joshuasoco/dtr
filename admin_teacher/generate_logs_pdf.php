<?php
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once '../config/database.php';

session_start();
if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Check if student_id is provided
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    die("Error: Student ID not provided.");
}

$student_id = $_GET['student_id'];
$status_filter = $_GET['status'] ?? 'all';

// First verify the student is assigned to this teacher and get their details
$stmt = $pdo->prepare("
    SELECT s.*, sta.teacher_id 
    FROM students s
    JOIN student_teacher_assignments sta ON s.id = sta.student_id
    WHERE s.student_id = ? AND sta.teacher_id = ? AND sta.status = 'Active'
");
$stmt->execute([$student_id, $teacher_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Error: Student not found or not assigned to you.");
}

// Fetch duty logs with teacher info
$query = "SELECT dl.*, t.name as teacher_name 
          FROM duty_logs dl 
          LEFT JOIN teachers t ON t.id = dl.teacher_id 
          INNER JOIN students s ON dl.student_id = s.student_id
          WHERE s.student_id = ? AND dl.status IN ('Approved', 'Rejected')";
$params = [$student_id];

if ($status_filter !== 'all' && in_array($status_filter, ['Approved', 'Rejected'])) {
    $query .= " AND dl.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY dl.duty_date DESC, dl.time_in DESC";

// Debug output
error_log("Query: " . $query);
error_log("Student ID: " . $student_id);
error_log("Params: " . print_r($params, true));

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$duty_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($duty_logs)) {
    die("No duty logs found for this student.");
}

// Create PDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Teacher Panel');
$pdf->SetAuthor('Teacher');
$pdf->SetTitle("Student Duty Logs - {$student['name']}");

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add page
$pdf->AddPage();

// Set font for title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'STUDENT DUTY LOGS REPORT', 0, 1, 'C');
$pdf->Ln(5);

// Student details
$pdf->SetFont('helvetica', '', 11);
$html = '<table border="0" cellpadding="3">
            <tr>
                <td width="20%"><strong>Student Name:</strong></td>
                <td>' . $student['name'] . '</td>
                <td width="20%"><strong>Student ID:</strong></td>
                <td>' . $student['student_id'] . '</td>
            </tr>
            <tr>
                <td><strong>Course:</strong></td>
                <td>' . $student['course'] . '</td>
                <td><strong>Department:</strong></td>
                <td>' . $student['department'] . '</td>
            </tr>
            <tr>
                <td><strong>Year Level:</strong></td>
                <td>' . $student['year_level'] . '</td>
                <td><strong>HK Status:</strong></td>
                <td>' . $student['hk_duty_status'] . '</td>
            </tr>
        </table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(5);

// Duty logs table
$html = '<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
            <tr style="background-color:#f2f2f2;">
                <th align="center"><strong>Date</strong></th>
                <th align="center"><strong>Time In</strong></th>
                <th align="center"><strong>Time Out</strong></th>
                <th align="center"><strong>Hours</strong></th>
                <th align="center"><strong>Status</strong></th>
                <th align="center"><strong>Handled By</strong></th>
            </tr>';

$total_hours = 0;

foreach ($duty_logs as $log) {
    $time_in = date('h:i A', strtotime($log['time_in']));
    $time_out = !empty($log['time_out']) ? date('h:i A', strtotime($log['time_out'])) : 'Not yet';
    
    // Calculate hours
    if ($log['status'] === 'Rejected') {
        $hours = '0';
    } else if (!empty($log['time_out'])) {
        $diff = strtotime($log['time_out']) - strtotime($log['time_in']);
        $hours = round($diff / 3600, 2);
        if ($log['status'] === 'Approved') {
            $total_hours += $hours;
        }
    } else {
        $hours = 'N/A';
    }

    $html .= '<tr>
                <td align="center">' . date('M d, Y', strtotime($log['duty_date'])) . '</td>
                <td align="center">' . $time_in . '</td>
                <td align="center">' . $time_out . '</td>
                <td align="center">' . (is_numeric($hours) ? number_format($hours, 2) . ' hrs' : $hours) . '</td>
                <td align="center">' . $log['status'] . '</td>
                <td align="center">' . ($log['teacher_name'] ?? 'N/A') . '</td>
              </tr>';
}

$html .= '</table>';
$html .= '<p><strong>Total Approved Hours: </strong>' . number_format($total_hours, 2) . ' hours</p>';

$pdf->writeHTML($html, true, false, true, false, '');

// Add signature line
$pdf->Ln(20);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Verified by:', 0, 1, 'R');
$pdf->Ln(10);
$pdf->Cell(0, 0, '_____________________________', 0, 1, 'R');
$pdf->Ln(5);
$pdf->Cell(0, 0, 'Expert Teacher', 0, 1, 'R');

// Output PDF
$pdf->Output('Student_Duty_Logs.pdf', 'I');
?>
