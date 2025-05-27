<?php
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Helper to fetch and process created_at dates
function get_dates($con, $table, $date_col = 'created_at', $where = '')
{
    $dates = [];
    $query = "SELECT $date_col FROM $table $where ORDER BY $date_col ASC";
    $res = mysqli_query($con, $query);
    if (!$res) {
        die("Query failed for $table: " . mysqli_error($con));
    }
    while ($row = mysqli_fetch_assoc($res)) {
        $dates[] = substr($row[$date_col], 0, 10); // date only
    }
    return $dates;
}

// Fetch all dates
$user_dates = get_dates($con, 'usertable', 'created_at', "WHERE role != 'admin'");
$event_dates = get_dates($con, 'create_events', 'created_at');
$registration_dates = get_dates($con, 'registers', 'registration_date');
$inbox_dates = get_dates($con, 'inbox', 'created_at');

// Collect all unique dates
$all_dates = array_merge($user_dates, $event_dates, $registration_dates, $inbox_dates);
$all_dates = array_unique($all_dates);
sort($all_dates);

// Helper: get all Sundays in the current month
function get_sundays_in_month($month, $year)
{
    $sundays = [];
    $date = strtotime("first sunday of $year-$month-01");
    $last_day = date('t', strtotime("$year-$month-01"));
    $month = str_pad($month, 2, '0', STR_PAD_LEFT);
    while (date('m', $date) == $month) {
        $sundays[] = date('Y-m-d', $date);
        $date = strtotime('+1 week', $date);
        if (date('d', $date) > $last_day)
            break;
    }
    return $sundays;
}

$current_month = date('m');
$current_year = date('Y');
$sundays = get_sundays_in_month($current_month, $current_year);

// Helper: count up to each Sunday
function count_up_to_sundays($dates, $sundays)
{
    sort($dates);
    $counts = [];
    $idx = 0;
    foreach ($sundays as $sunday) {
        $count = 0;
        foreach ($dates as $d) {
            if ($d <= $sunday)
                $count++;
        }
        $counts[] = $count;
    }
    return $counts;
}

// Filter dates to current month
function filter_current_month($dates)
{
    global $current_month, $current_year;
    return array_filter($dates, function ($d) use ($current_month, $current_year) {
        return date('m', strtotime($d)) === $current_month && date('Y', strtotime($d)) === $current_year;
    });
}
$user_dates_month = filter_current_month($user_dates);
$event_dates_month = filter_current_month($event_dates);
$registration_dates_month = filter_current_month($registration_dates);
$inbox_dates_month = filter_current_month($inbox_dates);

$users_week = count_up_to_sundays($user_dates_month, $sundays);
$events_week = count_up_to_sundays($event_dates_month, $sundays);
$registrations_week = count_up_to_sundays($registration_dates_month, $sundays);
$inbox_week = count_up_to_sundays($inbox_dates_month, $sundays);

// Count inbox statuses
$inbox_statuses = [
    'unread' => 0,
    'read' => 0,
    'pending' => 0,
    'responded' => 0
];

$inbox_status_query = "SELECT status, COUNT(*) as count FROM inbox GROUP BY status";
$inbox_status_res = mysqli_query($con, $inbox_status_query);
if ($inbox_status_res) {
    while ($row = mysqli_fetch_assoc($inbox_status_res)) {
        $status = $row['status'] ?: 'unread';
        if (isset($inbox_statuses[$status])) {
            $inbox_statuses[$status] = (int) $row['count'];
        }
    }
}

// Get all unique days in the current month with at least one record
function get_days_with_records($arrays)
{
    $days = [];
    foreach ($arrays as $arr) {
        foreach ($arr as $d) {
            $day = date('j', strtotime($d));
            $days[$day] = true;
        }
    }
    $days = array_keys($days);
    sort($days, SORT_NUMERIC);
    return $days;
}
$all_days = get_days_with_records([$user_dates_month, $event_dates_month, $registration_dates_month, $inbox_dates_month]);

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
$users_day = count_per_day($user_dates_month, $all_days);
$events_day = count_per_day($event_dates_month, $all_days);
$registrations_day = count_per_day($registration_dates_month, $all_days);
$inbox_day = count_per_day($inbox_dates_month, $all_days);

echo json_encode([
    'labels' => $all_days,
    'users' => $users_day,
    'events' => $events_day,
    'registrations' => $registrations_day,
    'inbox' => $inbox_day,
    // Add inbox status data
    'inbox_status' => [
        'labels' => ['Unread', 'Read', 'Pending', 'Responded'],
        'data' => [
            $inbox_statuses['unread'],
            $inbox_statuses['read'],
            $inbox_statuses['pending'],
            $inbox_statuses['responded']
        ]
    ]
]);
?>