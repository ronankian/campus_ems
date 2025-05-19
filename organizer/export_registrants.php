<?php
session_start();
require_once __DIR__ . '/../tcpdf/tcpdf.php';
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
if (!$con) {
    die('DB connection failed');
}
if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}
$user_id = $_SESSION['user_id'];
$selected_event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Fetch events created by this user
$events = [];
$where_event = '';
if ($selected_event_id > 0) {
    $where_event = "AND id = '$selected_event_id'";
}
$event_query = mysqli_query($con, "SELECT * FROM create_events WHERE user_id = '$user_id' $where_event ORDER BY date_time DESC");
while ($row = mysqli_fetch_assoc($event_query)) {
    $events[] = $row;
}

// Start PDF
$pdf = new TCPDF();
$pdf->SetCreator('EventHub');
$pdf->SetAuthor('EventHub');
$pdf->SetTitle('Registrants List');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();
// Add EventHub text as logo
$pdf->SetFont('helvetica', 'B', 28);
$pdf->SetTextColor(34, 34, 34);
$pdf->Cell(0, 0, 'EventHub', 0, 1, 'C');
$pdf->Ln(8);
$pdf->SetFont('helvetica', '', 12);

if (count($events) === 0) {
    $pdf->Write(0, 'No events found.', '', 0, 'C', true, 0, false, false, 0);
} else {
    $all_events = ($selected_event_id === 0);
    $organizer_name = '';
    $organizer_org = '';
    foreach ($events as $event) {
        // Event title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $event['event_title'], 0, 1, 'C');
        $pdf->Ln(5);
        // Registrants table
        $pdf->SetFont('helvetica', '', 11);
        $tbl = '<table border="1" cellpadding="4" cellspacing="0">';
        $tbl .= '<thead><tr style="background-color:#f1f1f1;"><th><b>Full Name</b></th><th><b>Student Number</b></th><th><b>Year Level</b></th><th><b>Section</b></th><th><b>Email</b></th></tr></thead><tbody>';
        $registrant_query = mysqli_query($con, "SELECT * FROM registers WHERE event_id = '" . $event['id'] . "' ORDER BY registration_date DESC");
        $has_registrants = false;
        while ($row = mysqli_fetch_assoc($registrant_query)) {
            $has_registrants = true;
            $tbl .= '<tr>';
            $tbl .= '<td>' . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) . '</td>';
            $tbl .= '<td>' . htmlspecialchars($row['student_number']) . '</td>';
            $tbl .= '<td>' . htmlspecialchars($row['year_level']) . '</td>';
            $tbl .= '<td>' . htmlspecialchars($row['section']) . '</td>';
            $tbl .= '<td>' . htmlspecialchars($row['email']) . '</td>';
            $tbl .= '</tr>';
        }
        if (!$has_registrants) {
            $tbl .= '<tr><td colspan="5" align="center">No Registrants Found.</td></tr>';
        }
        $tbl .= '</tbody></table>';
        $pdf->writeHTML($tbl, true, false, false, false, '');
        $pdf->Ln(8);
        // Save organizer info for all events
        if ($all_events && !$organizer_name) {
            $organizer_org = $event['organization'] ?? '';
            $organizer_name = $event['fullname'] ?? '';
            if (!$organizer_name) {
                $user_result = mysqli_query($con, "SELECT firstname, lastname FROM usertable WHERE id = '" . $event['user_id'] . "' LIMIT 1");
                if ($user_result && mysqli_num_rows($user_result) > 0) {
                    $user_row = mysqli_fetch_assoc($user_result);
                    $organizer_name = $user_row['firstname'] . ' ' . $user_row['lastname'];
                }
            }
        }
        // If single event, show organized by after table
        if (!$all_events) {
            $pdf->SetFont('helvetica', 'I', 11);
            $org_name = $event['organization'] ?? '';
            $fullname = $event['fullname'] ?? '';
            if (!$fullname) {
                $user_result = mysqli_query($con, "SELECT firstname, lastname FROM usertable WHERE id = '" . $event['user_id'] . "' LIMIT 1");
                if ($user_result && mysqli_num_rows($user_result) > 0) {
                    $user_row = mysqli_fetch_assoc($user_result);
                    $fullname = $user_row['firstname'] . ' ' . $user_row['lastname'];
                }
            }
            $pdf->Cell(0, 10, 'Organized by: ' . $fullname . ($org_name ? ' (' . $org_name . ')' : ''), 0, 1, 'R');
            $pdf->Ln(8);
        }
    }
    // If all events, show organized by at the very bottom
    if ($all_events && $organizer_name) {
        $pdf->SetFont('helvetica', 'I', 11);
        $pdf->Cell(0, 10, 'Organized by: ' . $organizer_name . ($organizer_org ? ' (' . $organizer_org . ')' : ''), 0, 1, 'R');
        $pdf->Ln(8);
    }
}
$pdf->Output('registrants.pdf', 'D');