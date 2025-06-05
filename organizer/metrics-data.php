<?php
session_start();
include '../login/connection.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$month = isset($_GET['month']) ? str_pad(intval($_GET['month']), 2, '0', STR_PAD_LEFT) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$user_event_dates = [];
$user_reg_dates = [];
if ($user_id) {
    $event_dates_query = mysqli_query($con, "SELECT created_at FROM create_events WHERE user_id = '$user_id' AND MONTH(created_at) = '$month' AND YEAR(created_at) = '$year'");
    while ($row = mysqli_fetch_assoc($event_dates_query)) {
        $user_event_dates[] = substr($row['created_at'], 0, 10);
    }
    $event_id_query = mysqli_query($con, "SELECT id FROM create_events WHERE user_id = '$user_id' AND MONTH(created_at) = '$month' AND YEAR(created_at) = '$year'");
    $event_ids = [];
    while ($row = mysqli_fetch_assoc($event_id_query)) {
        $event_ids[] = $row['id'];
    }
    if (!empty($event_ids)) {
        $ids_str = implode(',', array_map('intval', $event_ids));
        $reg_dates_query = mysqli_query($con, "SELECT registration_date FROM registers WHERE event_id IN ($ids_str) AND MONTH(registration_date) = '$month' AND YEAR(registration_date) = '$year'");
        while ($row = mysqli_fetch_assoc($reg_dates_query)) {
            $user_reg_dates[] = substr($row['registration_date'], 0, 10);
        }
    }
}

// Get all unique days in the selected month with at least one record
$all_days = [];
foreach (array_merge($user_event_dates, $user_reg_dates) as $d) {
    $day = date('j', strtotime($d));
    $all_days[$day] = true;
}
$all_days = array_keys($all_days);
sort($all_days, SORT_NUMERIC);

// Count per day for each entity
function count_per_day($dates, $all_days)
{
    $counts = array_fill_keys($all_days, 0);
    foreach ($dates as $d) {
        $day = date('j', strtotime($d));
        if (isset($counts[$day]))
            $counts[$day]++;
    }
    // Return as array in the order of $all_days
    return array_values($counts);
}
$event_created_counts = count_per_day($user_event_dates, $all_days);
$registrants_counts = count_per_day($user_reg_dates, $all_days);

// Output
echo json_encode([
    'labels' => $all_days,
    'event_created' => $event_created_counts,
    'registrants' => $registrants_counts
]);