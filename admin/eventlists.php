<?php
session_start();
// Database connection
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}
// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg_id'], $_POST['new_status'])) {
    $msg_id = intval($_POST['msg_id']);
    $new_status = $_POST['new_status'];
    $allowed = ['unread', 'read', 'pending', 'responded'];
    if (in_array($new_status, $allowed)) {
        mysqli_query($con, "UPDATE inbox SET status = '$new_status' WHERE id = $msg_id");
        echo "<script>window.location.href=window.location.href;</script>";
        exit;
    }
}
// Fetch all events with organizer info
$query = "SELECT e.*, u.firstname, u.lastname, u.organization FROM create_events e LEFT JOIN usertable u ON e.user_id = u.id ORDER BY e.created_at DESC";
$result = mysqli_query($con, $query);
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


    <div class="container-fluid">

        <div class="d-flex align-items-start dashboard-container">
            <?php include 'sidebar.php'; ?>
            <div class="flex-grow-1 px-4">
                <div class="row g-3 mb-4">

                    <div class="col-md-12">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h4 class="fw-bold mb-3" style="text-indent: 10px;">Event Management</h4>
                            </div>
                            <div class="eventlists col-12">
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
                                                        <th>Attachment</th>
                                                        <th>Organizer</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (mysqli_num_rows($result) === 0): ?>
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted">No Events Found.
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php while ($row = mysqli_fetch_assoc($result)):
                                                            // Category
                                                            $category = htmlspecialchars($row['category'] ?? '');
                                                            // Event Title
                                                            $event_title = htmlspecialchars($row['event_title'] ?? '');
                                                            // Schedule
                                                            $schedule = '';
                                                            if (!empty($row['date_time']) && !empty($row['ending_time'])) {
                                                                $schedule = date('M d, Y | h:i A', strtotime($row['date_time'])) . ' - ' . date('M d, Y | h:i A', strtotime($row['ending_time']));
                                                            } elseif (!empty($row['date_time'])) {
                                                                $schedule = date('M d, Y | h:i A', strtotime($row['date_time']));
                                                            } else {
                                                                $schedule = '<span class="text-muted">N/A</span>';
                                                            }
                                                            // Location
                                                            $location = htmlspecialchars($row['location'] ?? '');
                                                            // Status
                                                            $status_value = $row['status'] ?? 'active';
                                                            $status_badge = '';
                                                            $status_time = '';
                                                            if ($status_value === 'cancelled') {
                                                                $status_badge = '<span class="badge bg-danger">Cancelled</span>';
                                                                $status_time = !empty($row['date_cancelled']) ? date('M d, Y | h:i A', strtotime($row['date_cancelled'])) : '';
                                                                $status_time_label = 'Cancelled at';
                                                            } elseif ($status_value === 'ended') {
                                                                $status_badge = '<span class="badge bg-secondary">Ended</span>';
                                                                $status_time = !empty($row['updated_at']) && $row['updated_at'] !== $row['created_at'] ? date('M d, Y | h:i A', strtotime($row['updated_at'])) : date('M d, Y | h:i A', strtotime($row['created_at']));
                                                                $status_time_label = (!empty($row['updated_at']) && $row['updated_at'] !== $row['created_at']) ? 'Updated at' : 'Created at';
                                                            } elseif ($status_value === 'active') {
                                                                $status_badge = '<span class="badge bg-success">Active</span>';
                                                                $status_time = !empty($row['updated_at']) && $row['updated_at'] !== $row['created_at'] ? date('M d, Y | h:i A', strtotime($row['updated_at'])) : date('M d, Y | h:i A', strtotime($row['created_at']));
                                                                $status_time_label = (!empty($row['updated_at']) && $row['updated_at'] !== $row['created_at']) ? 'Updated at' : 'Created at';
                                                            } else {
                                                                $status_badge = '<span class="badge bg-secondary">Unknown</span>';
                                                                $status_time = date('M d, Y | h:i A', strtotime($row['created_at']));
                                                                $status_time_label = 'Created at';
                                                            }
                                                            // Organizer
                                                            $organizer = '';
                                                            if (!empty($row['firstname']) || !empty($row['lastname'])) {
                                                                $organizer = htmlspecialchars(trim($row['firstname'] . ' ' . $row['lastname']));
                                                                if (!empty($row['organization'])) {
                                                                    $organizer .= ' (' . htmlspecialchars($row['organization']) . ')';
                                                                }
                                                            } else {
                                                                $organizer = '<span class="text-muted">N/A</span>';
                                                            }
                                                            // Attachment
                                                            $attachment = '';
                                                            if (!empty($row['attach_file'])) {
                                                                $files = json_decode($row['attach_file'], true);
                                                                if (is_array($files)) {
                                                                    foreach ($files as $file) {
                                                                        $attachment .= '<a href="../uploads/' . htmlspecialchars($file) . '" target="_blank" class="btn btn-sm btn-outline-primary me-1"><i class="fa fa-paperclip"></i> Download</a>';
                                                                    }
                                                                }
                                                            }
                                                            if ($attachment === '')
                                                                $attachment = '<span class="text-white">None</span>';
                                                            ?>
                                                            <tr class="text-center align-middle">
                                                                <td><?php echo $category; ?></td>
                                                                <td><?php echo $event_title; ?></td>
                                                                <td><?php echo $schedule; ?></td>
                                                                <td><?php echo $location; ?></td>
                                                                <td><?php echo $attachment; ?></td>
                                                                <td><?php echo $organizer; ?></td>
                                                                <td>
                                                                    <?php echo $status_badge; ?><br>
                                                                    <small>
                                                                        <?php echo $status_time_label . ': ' . $status_time; ?></small>
                                                                </td>
                                                                <td>
                                                                    <a href="../event-details.php?id=<?php echo $row['id']; ?>"
                                                                        class="btn btn-sm rounded-pill btn-primary mb-1"
                                                                        title="View Details"><i class="fa fa-eye"></i></a>
                                                                    <?php if ($status_value !== 'cancelled' && $status_value !== 'ended'): ?>
                                                                        <form method="POST" action="" style="display:inline;">
                                                                            <input type="hidden" name="cancel_event_id"
                                                                                value="<?php echo $row['id']; ?>">
                                                                            <button type="submit"
                                                                                class="btn btn-sm rounded-pill btn-warning ms-1 mb-1"
                                                                                title="Cancel Event"
                                                                                onclick="return confirm('Are you sure you want to cancel this event?');"><i
                                                                                    class="fa fa-ban"></i></button>
                                                                        </form>
                                                                    <?php endif; ?>
                                                                    <form method="POST" action="" style="display:inline;">
                                                                        <input type="hidden" name="delete_event_id"
                                                                            value="<?php echo $row['id']; ?>">
                                                                        <button type="submit"
                                                                            class="btn btn-sm rounded-pill btn-danger ms-1 mb-1"
                                                                            title="Delete Event"
                                                                            onclick="return confirm('Are you sure you want to delete this event and all its data?');"><i
                                                                                class="fa fa-trash-can"></i></button>
                                                                    </form>
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
        </div>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>

// Handle cancel and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (isset($_POST['cancel_event_id'])) {
$cancel_id = intval($_POST['cancel_event_id']);
$now = date('Y-m-d H:i:s');
mysqli_query($con, "UPDATE create_events SET status = 'cancelled', date_cancelled = '$now' WHERE id = $cancel_id");
header('Location: ' . $_SERVER['PHP_SELF']);
exit;
}
if (isset($_POST['delete_event_id'])) {
$delete_id = intval($_POST['delete_event_id']);
// Optionally: delete related files, registrations, etc.
mysqli_query($con, "DELETE FROM create_events WHERE id = $delete_id");
header('Location: ' . $_SERVER['PHP_SELF']);
exit;
}
}