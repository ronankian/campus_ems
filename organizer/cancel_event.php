<?php
// Database connection
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Check if event_id and reason are provided
if (!isset($_POST['event_id']) || !isset($_POST['reason'])) {
    echo json_encode(['success' => false, 'message' => 'Event ID and reason are required']);
    exit;
}

$event_id = mysqli_real_escape_string($con, $_POST['event_id']);
$reason = trim($_POST['reason']);
if (empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Reason is required']);
    exit;
}
if (mb_strlen($reason) > 2500) {
    echo json_encode(['success' => false, 'message' => 'Reason must not exceed 2500 characters.']);
    exit;
}
$reason = mysqli_real_escape_string($con, $reason);

// Update event status to cancelled, set date_cancelled, and save reason
$query = "UPDATE create_events SET status = 'cancelled', date_cancelled = NOW(), reason = ? WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'si', $reason, $event_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($con)]);
}

mysqli_stmt_close($stmt);
mysqli_close($con);
?>