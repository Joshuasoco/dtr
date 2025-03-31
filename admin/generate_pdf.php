<?php
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once '../config/database.php';

// Start session and authenticate
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Validate and sanitize student ID
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    die("Error: Student ID not provided.");
}

$student_id = filter_var($_GET['student_id'], FILTER_SANITIZE_STRING);
$status_filter = isset($_GET['status']) ? filter_var($_GET['status'], FILTER_SANITIZE_STRING) : 'all';

try {
    // Fetch student details with potential teacher assignment
    $stmt = $pdo->prepare("
        SELECT s.* 
        FROM students s
        WHERE s.student_id = ?
    ");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        throw new Exception("Student not found.");
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
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $duty_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are logs
    if (empty($duty_logs)) {
        throw new Exception("No duty logs found for this student.");
    }

    // Create PDF
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Admin Panel');
    $pdf->SetAuthor('Administrative System');
    $pdf->SetTitle("Student Duty Logs - {$student['name']}");
    $pdf->SetSubject('Duty Log Report');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    // Set fonts
    $pdf->SetFont('helvetica', 'B', 16);

    // Center title
    $pdf->Cell(0, 10, "STUDENT DUTY LOGS REPORT", 0, 1, 'C');
    $pdf->Ln(5);

    // Student details
    $pdf->SetFont('helvetica', '', 11);
    $html = '<table border="0" cellpadding="3">
                <tr>
                    <td width="20%"><strong>Student Name:</strong></td>
                    <td>' . htmlspecialchars($student['name']) . '</td>
                    <td width="20%"><strong>Student ID:</strong></td>
                    <td>' . htmlspecialchars($student['student_id']) . '</td>
                </tr>
                <tr>
                    <td><strong>Course:</strong></td>
                    <td>' . htmlspecialchars($student['course']) . '</td>
                    <td><strong>Department:</strong></td>
                    <td>' . htmlspecialchars($student['department']) . '</td>
                </tr>
                <tr>
                    <td><strong>Year Level:</strong></td>
                    <td>' . htmlspecialchars($student['year_level']) . '</td>
                    <td><strong>HK Status:</strong></td>
                    <td>' . htmlspecialchars($student['hk_duty_status']) . '</td>
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
    $approved_logs = 0;
    $rejected_logs = 0;

    foreach ($duty_logs as $log) {
        // Format times
        $time_in = date('h:i A', strtotime($log['time_in']));
        $time_out = !empty($log['time_out']) ? date('h:i A', strtotime($log['time_out'])) : 'Not yet';
        
        // Calculate hours
        if ($log['status'] === 'Rejected') {
            $hours = '0';
            $rejected_logs++;
        } else if (!empty($log['time_out'])) {
            $diff = strtotime($log['time_out']) - strtotime($log['time_in']);
            $hours = round($diff / 3600, 2);
            
            if ($log['status'] === 'Approved') {
                $total_hours += $hours;
                $approved_logs++;
            }
        } else {
            $hours = 'N/A';
        }

        // Prepare handled by info
        $handled_by = htmlspecialchars($log['teacher_name'] ?? 'N/A');

        $html .= '<tr>
                    <td align="center">' . date('M d, Y', strtotime($log['duty_date'])) . '</td>
                    <td align="center">' . $time_in . '</td>
                    <td align="center">' . $time_out . '</td>
                    <td align="center">' . (is_numeric($hours) ? number_format($hours, 2) . ' hrs' : $hours) . '</td>
                    <td align="center">' . htmlspecialchars($log['status']) . '</td>
                    <td align="center">' . $handled_by . '</td>
                  </tr>';
    }

    $html .= '</table>';

    // Add summary
    $html .= '<p><strong>Total Approved Hours: </strong>' . number_format($total_hours, 2) . ' hours</p>';
    $html .= '<p><strong>Total Logs: </strong>' . count($duty_logs) . 
             ' (Approved: ' . $approved_logs . ', Rejected: ' . $rejected_logs . ')</p>';

    $pdf->writeHTML($html, true, false, true, false, '');

    // Add signature line
    $pdf->Ln(20);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, "Verified by: ____________________", 0, 1, 'R');
    $pdf->Cell(0, 10, 'Expert Teacher/Dean', 0, 1, 'R');

    // Output PDF
    $pdf->Output("Student_Duty_Logs_{$student['name']}.pdf", 'D');

} catch (Exception $e) {
    // Log the error and display a user-friendly message
    error_log("Duty Logs PDF Generation Error: " . $e->getMessage());
    die("Error generating report: " . $e->getMessage());
}
?>