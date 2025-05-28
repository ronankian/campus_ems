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

// Handle delete registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_reg_id'])) {
    $delete_id = intval($_POST['delete_reg_id']);
    mysqli_query($con, "DELETE FROM registers WHERE id = $delete_id");
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all registrations with event and user info
$query = "SELECT r.*, e.event_title, e.status as event_status, u.username, u.organization, u.role FROM registers r LEFT JOIN create_events e ON r.event_id = e.id LEFT JOIN usertable u ON r.user_id = u.id ORDER BY r.registration_date DESC";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub</title>
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
                                <h4 class="fw-bold mb-3" style="text-indent: 10px;">Event Registrations</h4>
                            </div>
                            <div class="eventlists col-12">
                                <div class="card shadow rounded-4 border-0">
                                    <div class="card-body">
                                        <div class="table-responsive" style="min-height: 450px;">
                                            <table class="table table-hover table-bordered">
                                                <thead>
                                                    <tr class="text-center align-middle">
                                                        <th>Date</th>
                                                        <th>Event</th>
                                                        <th>Registrant</th>
                                                        <th>Year Level</th>
                                                        <th>Section</th>
                                                        <th>Student No.</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (mysqli_num_rows($result) === 0): ?>
                                                        <tr>
                                                            <td colspan="7" class="text-center text-white">No Registrations
                                                                Found.</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php while ($row = mysqli_fetch_assoc($result)):
                                                            $date = !empty($row['registration_date']) ? date('M d, Y | h:i A', strtotime($row['registration_date'])) : '';
                                                            $event_title = htmlspecialchars($row['event_title'] ?? '');
                                                            $event_status = $row['event_status'] ?? '';
                                                            $event_status_badge = '';
                                                            if ($event_status === 'active') {
                                                                $event_status_badge = '<span class=\'badge bg-success ms-2\'>Active</span>';
                                                            } elseif ($event_status === 'ended') {
                                                                $event_status_badge = '<span class=\'badge bg-secondary ms-2\'>Ended</span>';
                                                            } elseif ($event_status === 'cancelled') {
                                                                $event_status_badge = '<span class=\'badge bg-danger ms-2\'>Cancelled</span>';
                                                            } elseif ($event_status === 'ongoing') {
                                                                $event_status_badge = '<span class=\'badge bg-primary ms-2\'>Ongoing</span>';
                                                            } else {
                                                                $event_status_badge = '<span class=\'badge bg-secondary ms-2\'>Unknown</span>';
                                                            }
                                                            $username = htmlspecialchars($row['username'] ?? '');
                                                            $organization = htmlspecialchars($row['organization'] ?? '');
                                                            $role = htmlspecialchars($row['role'] ?? '');
                                                            $role_badge = '';
                                                            if ($role === 'attendee') {
                                                                $role_badge = '<span class="badge bg-primary">Attendee</span>';
                                                            } elseif ($role === 'organizer') {
                                                                $role_badge = '<span class="badge bg-success">Organizer</span>';
                                                            } elseif ($role === 'banned') {
                                                                $role_badge = '<span class="badge bg-danger">Banned</span>';
                                                            } else {
                                                                $role_badge = '<span class="badge bg-secondary">' . ucfirst($role) . '</span>';
                                                            }
                                                            $year_level = htmlspecialchars($row['year_level'] ?? '');
                                                            $section = htmlspecialchars($row['section'] ?? '');
                                                            $student_number = htmlspecialchars($row['student_number'] ?? '');
                                                            $reg_id = $row['id'];
                                                            ?>
                                                            <tr class="text-center align-middle">
                                                                <td><?php echo $date; ?></td>
                                                                <td><?php echo '<a href="../event-details.php?id=' . $row['event_id'] . '" class="text-white text-decoration-none">' . $event_title . '</a> ' . $event_status_badge; ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo $username; ?>
                                                                    <?php if ($organization): ?>
                                                                        <span
                                                                            class="text-white">(<?php echo $organization; ?>)</span>
                                                                    <?php endif; ?>
                                                                    <br><?php echo $role_badge; ?>
                                                                </td>
                                                                <td><?php echo $year_level; ?></td>
                                                                <td><?php echo $section; ?></td>
                                                                <td><?php echo $student_number; ?></td>
                                                                <td>
                                                                    <a href="view.php?reg_id=<?php echo $reg_id; ?>"
                                                                        class="btn btn-sm rounded-pill btn-primary mb-1"
                                                                        title="View Registration Details"><i
                                                                            class="fa fa-eye"></i></a>
                                                                    <form method="POST" action="" style="display:inline;">
                                                                        <input type="hidden" name="delete_reg_id"
                                                                            value="<?php echo $reg_id; ?>">
                                                                        <button type="submit"
                                                                            class="btn btn-sm rounded-pill btn-danger mb-1"
                                                                            title="Delete Registration"
                                                                            onclick="return confirm('Are you sure you want to delete this registration?');"><i
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