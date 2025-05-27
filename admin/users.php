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
// Handle role/status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = date('Y-m-d H:i:s');
    if (isset($_POST['upgrade_user_id'])) {
        $uid = intval($_POST['upgrade_user_id']);
        mysqli_query($con, "UPDATE usertable SET role = 'organizer', changed_at = '$now' WHERE id = $uid");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    if (isset($_POST['downgrade_user_id'])) {
        $uid = intval($_POST['downgrade_user_id']);
        mysqli_query($con, "UPDATE usertable SET role = 'attendee', changed_at = '$now' WHERE id = $uid");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    if (isset($_POST['ban_user_id'])) {
        $uid = intval($_POST['ban_user_id']);
        mysqli_query($con, "UPDATE usertable SET role = 'banned', banned_at = '$now' WHERE id = $uid");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    if (isset($_POST['unban_user_id'])) {
        $uid = intval($_POST['unban_user_id']);
        mysqli_query($con, "UPDATE usertable SET role = 'attendee', changed_at = '$now' WHERE id = $uid");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
// Fetch all users except admins
$query = "SELECT * FROM usertable WHERE role != 'admin' ORDER BY created_at DESC";
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
                                <h4 class="fw-bold mb-3" style="text-indent: 10px;">User Management</h4>
                            </div>
                            <div class="eventlists col-12">
                                <div class="card shadow rounded-4 border-0">
                                    <div class="card-body">
                                        <div class="table-responsive" style="min-height: 450px;">
                                            <table class="table table-hover table-bordered">
                                                <thead>
                                                    <tr class="text-center align-middle">
                                                        <th>Fullname</th>
                                                        <th>Email</th>
                                                        <th>Organization</th>
                                                        <th>Activities</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (mysqli_num_rows($result) === 0): ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted">No Users Found.
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php while ($row = mysqli_fetch_assoc($result)):
                                                            $user_id = $row['id'];
                                                            $fullname = htmlspecialchars(trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')));
                                                            $email = htmlspecialchars($row['email'] ?? '');
                                                            $organization = htmlspecialchars($row['organization'] ?? '');
                                                            $role = htmlspecialchars($row['role'] ?? '');
                                                            // Activities counts
                                                            $reg_count = 0;
                                                            $event_count = 0;
                                                            $msg_count = 0;
                                                            $reg_q = mysqli_query($con, "SELECT COUNT(*) FROM registers WHERE user_id = $user_id");
                                                            if ($reg_q)
                                                                $reg_count = mysqli_fetch_row($reg_q)[0];
                                                            $event_q = mysqli_query($con, "SELECT COUNT(*) FROM create_events WHERE user_id = $user_id");
                                                            if ($event_q)
                                                                $event_count = mysqli_fetch_row($event_q)[0];
                                                            $msg_q = mysqli_query($con, "SELECT COUNT(*) FROM inbox WHERE user_id = $user_id");
                                                            if ($msg_q)
                                                                $msg_count = mysqli_fetch_row($msg_q)[0];
                                                            // Most recent date logic
                                                            $dates = [];
                                                            if (!empty($row['created_at']))
                                                                $dates['created_at'] = strtotime($row['created_at']);
                                                            if (!empty($row['changed_at']))
                                                                $dates['changed_at'] = strtotime($row['changed_at']);
                                                            if (!empty($row['banned_at']))
                                                                $dates['banned_at'] = strtotime($row['banned_at']);
                                                            $most_recent = '';
                                                            $most_recent_label = '';
                                                            if (!empty($dates)) {
                                                                arsort($dates);
                                                                $most_recent_key = key($dates);
                                                                $most_recent = date('M d, Y | h:i A', reset($dates));
                                                                if ($most_recent_key === 'created_at')
                                                                    $most_recent_label = '';
                                                                elseif ($most_recent_key === 'changed_at')
                                                                    $most_recent_label = '';
                                                                elseif ($most_recent_key === 'banned_at')
                                                                    $most_recent_label = '';
                                                            }
                                                            // Badge
                                                            if ($role === 'attendee') {
                                                                $badge = '<span class="badge bg-primary">Attendee</span>';
                                                            } elseif ($role === 'organizer') {
                                                                $badge = '<span class="badge bg-success">Organizer</span>';
                                                            } elseif ($role === 'banned') {
                                                                $badge = '<span class="badge bg-danger">Banned</span>';
                                                            } else {
                                                                $badge = '<span class="badge bg-secondary">' . ucfirst($role) . '</span>';
                                                            }
                                                            ?>
                                                            <tr class="text-center align-middle">
                                                                <td><?php echo $fullname; ?></td>
                                                                <td><?php echo $email; ?></td>
                                                                <td><?php echo $organization; ?></td>
                                                                <td>
                                                                    <span title="Registered Events" class="me-2"><i
                                                                            class="fa fa-file-circle-plus"></i>
                                                                        <?php echo $reg_count; ?></span>
                                                                    <span title="Created Events" class="me-2"><i
                                                                            class="fa fa-calendar-plus"></i>
                                                                        <?php echo $event_count; ?></span>
                                                                    <span title="Sent Messages"><i class="fa fa-envelope"></i>
                                                                        <?php echo $msg_count; ?></span>
                                                                </td>
                                                                <td><?php echo $badge; ?><?php if ($most_recent): ?><br><small><?php echo $most_recent; ?></small><?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($role === 'attendee'): ?>
                                                                        <form method="POST" action="" style="display:inline;">
                                                                            <input type="hidden" name="upgrade_user_id"
                                                                                value="<?php echo $row['id']; ?>">
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-success rounded-pill"
                                                                                title="Upgrade to Organizer"
                                                                                onclick="return confirm('Are you sure you want to assign this user to organizer?');"><i
                                                                                    class="fa fa-circle-up"></i></button>
                                                                        </form>
                                                                    <?php elseif ($role === 'organizer'): ?>
                                                                        <form method="POST" action="" style="display:inline;">
                                                                            <input type="hidden" name="downgrade_user_id"
                                                                                value="<?php echo $row['id']; ?>">
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-primary rounded-pill text-white"
                                                                                title="Downgrade to Attendee"
                                                                                onclick="return confirm('Are you sure you want to revert this user to attendee?');"><i
                                                                                    class="fa fa-circle-down"></i></button>
                                                                        </form>
                                                                    <?php elseif ($role === 'banned'): ?>
                                                                        <form method="POST" action="" style="display:inline;">
                                                                            <input type="hidden" name="unban_user_id"
                                                                                value="<?php echo $row['id']; ?>">
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-warning rounded-pill"
                                                                                title="Lift Ban"
                                                                                onclick="return confirm('Are you sure you want to lift the ban for this user?');"><i
                                                                                    class="fa fa-unlock"></i></button>
                                                                        </form>
                                                                    <?php endif; ?>
                                                                    <?php if ($role !== 'banned'): ?>
                                                                        <form method="POST" action="" style="display:inline;">
                                                                            <input type="hidden" name="ban_user_id"
                                                                                value="<?php echo $row['id']; ?>">
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-danger rounded-pill"
                                                                                title="Ban User"
                                                                                onclick="return confirm('Are you sure you want to ban this user?');"><i
                                                                                    class="fa fa-ban"></i></button>
                                                                        </form>
                                                                    <?php endif; ?>
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