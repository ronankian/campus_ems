<?php
session_start();
// Update event statuses: set to 'ended' if past ending_time and not already ended/cancelled
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
$update_status_query = "UPDATE create_events 
    SET status = 'ended' 
    WHERE ending_time < NOW() AND status NOT IN ('ended', 'cancelled')";
mysqli_query($con, $update_status_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub Attendee Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        :root {
            --primary: #784ba0;
            --gradient-start: #ff3cac;
            --gradient-end: #38f9d7;
            --surface-dark: #2b2d42;
            --accent: #ffb347;
            --text-main: #f0f0f0;
            --text-dark: #2b2d42;
        }

        .dashboard-container {
            border-radius: 6px;
            backdrop-filter: blur(8px);
        }

        .card,
        .table {
            background: rgba(43, 45, 66, 0.3) !important;
            backdrop-filter: blur(10px) !important;
            color: #fff !important;
        }

        .table th,
        .table td {
            background: transparent !important;
            color: #fff !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        .table thead th {
            background: rgba(0, 0, 0, 0.2) !important;
            color: #fff !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3) !important;
        }

        .table-hover tbody tr:hover {
            background: rgba(255, 255, 255, 0.15) !important;
        }

        .table-bordered {
            border-color: rgba(255, 255, 255, 0.2) !important;
        }
    </style>

</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../bg-image.php'; ?>


    <div class="container">

        <div class="row dashboard-container p-3 py-4">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9">
                <div class="row">
                    <div class="col-12">
                        <h4 class="fw-bold mb-3">My Registered Events</h4>
                    </div>
                    <div class="eventlists col-12">
                        <?php
                        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                        $query = "SELECT r.id AS reg_id, r.registration_date, e.id AS event_id, e.event_title, e.date_time, e.location, e.status, e.category
                                FROM registers r
                                JOIN create_events e ON r.event_id = e.id
                                WHERE r.user_id = '$user_id' ORDER BY r.registration_date DESC";
                        $result = mysqli_query($con, $query);
                        ?>

                        <div class="card shadow rounded-4 border-0">
                            <div class="card-body">
                                <div class="table-responsive" style="min-height: 450px;">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr class="text-center align-middle">
                                                <th>Category</th>
                                                <th>Event Title</th>
                                                <th>Schedule</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($result) === 0): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No Registered Event
                                                        Found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php while ($row = mysqli_fetch_assoc($result)):
                                                    // Category
                                                    $category = htmlspecialchars($row['category'] ?? '');
                                                    // Event Title
                                                    $event_title = htmlspecialchars($row['event_title']);
                                                    $event_id = $row['event_id'];
                                                    // Always fetch date_time and ending_time from create_events using event_id
                                                    $schedule = '';
                                                    $event_times = mysqli_query($con, "SELECT date_time, ending_time FROM create_events WHERE id = '$event_id' LIMIT 1");
                                                    if ($event_times && mysqli_num_rows($event_times) > 0) {
                                                        $times = mysqli_fetch_assoc($event_times);
                                                        $start = !empty($times['date_time']) ? date('M d, Y | h:i A', strtotime($times['date_time'])) : '<span class=\'text-muted\'>N/A</span>';
                                                        $end = !empty($times['ending_time']) ? date('M d, Y | h:i A', strtotime($times['ending_time'])) : '<span class=\'text-muted\'>N/A</span>';
                                                        $schedule = $start . ' - ' . $end;
                                                    } else {
                                                        $schedule = '<span class=\'text-muted\'>N/A</span>';
                                                    }
                                                    // Status
                                                    $status_value = $row['status'] ?? 'active';
                                                    $status_badge = '';
                                                    $status_extra = '';
                                                    if ($status_value === 'cancelled') {
                                                        $status_badge = '<span class="badge bg-danger">Cancelled</span>';
                                                        if (!empty($row['cancelled_at']) || !empty($row['date_cancelled'])) {
                                                            $cancelled_at = !empty($row['cancelled_at']) ? $row['cancelled_at'] : $row['date_cancelled'];
                                                            $status_extra = '<br><small class="text-white-50">Cancelled: ' . date('M d, Y | h:i A', strtotime($cancelled_at)) . '</small>';
                                                        }
                                                    } elseif ($status_value === 'ended') {
                                                        $status_badge = '<span class="badge bg-secondary">Ended</span>';
                                                    } elseif ($status_value === 'ongoing') {
                                                        $status_badge = '<span class="badge bg-primary">Ongoing</span>';
                                                    } elseif ($status_value === 'active') {
                                                        $status_badge = '<span class="badge bg-success">Active</span>';
                                                    } else {
                                                        $status_badge = '<span class="badge bg-secondary">Unknown</span>';
                                                    }
                                                    $registration_date = date('M d, Y | h:i A', strtotime($row['registration_date']));
                                                    ?>
                                                    <tr class="text-center align-middle">
                                                        <td><?php echo $category; ?></td>
                                                        <td><a href="../event-details.php?id=<?php echo $event_id; ?>"
                                                                class="fw-bold text-white text-decoration-none"><?php echo $event_title; ?></a>
                                                        </td>
                                                        <td><?php echo $schedule; ?></td>
                                                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                        <td>
                                                            <?php echo $status_badge; ?>         <?php echo $status_extra; ?>
                                                        </td>
                                                        <td>
                                                            <a href="view.php?reg_id=<?php echo $row['reg_id']; ?>"
                                                                class="btn btn-sm btn-primary" title="View Registration"><i
                                                                    class="fa fa-eye"></i></a>
                                                            <br>
                                                            <small class="text-white-50">Registered:
                                                                <?php echo $registration_date; ?></small>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>