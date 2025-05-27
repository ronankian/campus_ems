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
// Fetch all inbox messages (admin sees all)
$query = "SELECT i.*, u.username, ru.username as reported_username FROM inbox i LEFT JOIN usertable u ON i.user_id = u.id LEFT JOIN usertable ru ON i.reportuser_id = ru.id ORDER BY i.created_at DESC";
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
                                <h4 class="fw-bold mb-3" style="text-indent: 10px;">Message
                                    Inbox</h4>
                            </div>
                            <div class="eventlists col-12">
                                <div class="card shadow rounded-4 border-0">
                                    <div class="card-body">
                                        <div class="table-responsive" style="min-height: 450px;">
                                            <table class="table table-hover table-bordered">
                                                <thead>
                                                    <tr class="text-center align-middle">
                                                        <th>Date</th>
                                                        <th>Sender</th>
                                                        <th>Title</th>
                                                        <th>Subject</th>
                                                        <th>Attachment</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (mysqli_num_rows($result) === 0): ?>
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">No Messages
                                                                Found.</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php while ($row = mysqli_fetch_assoc($result)):
                                                            // Date
                                                            $date = date('M d, Y | h:i A', strtotime($row['created_at']));
                                                            // Sender
                                                            $sender = isset($row['username']) ? htmlspecialchars($row['username']) : 'Unknown';
                                                            // Title
                                                            $title = htmlspecialchars($row['title']);
                                                            // Subject
                                                            $subject = '';
                                                            if ($row['subject_type'] === 'other' && $row['subject_custom'] === 'User' && isset($row['reported_username'])) {
                                                                $subject = $row['reported_username'];
                                                            } else {
                                                                $subject = !empty($row['subject_custom']) ? $row['subject_custom'] : ucfirst($row['subject_type']);
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
                                                            // Status
                                                            $status_value = isset($row['status']) ? $row['status'] : 'unread';
                                                            switch ($status_value) {
                                                                case 'unread':
                                                                    $status = '<span class="badge bg-secondary d-block mx-auto" style="min-width:80px;text-align:center;">Unread</span>';
                                                                    break;
                                                                case 'read':
                                                                    $status = '<span class="badge bg-info text-dark d-block mx-auto" style="min-width:80px;text-align:center;">Read</span>';
                                                                    break;
                                                                case 'pending':
                                                                    $status = '<span class="badge bg-warning text-dark d-block mx-auto" style="min-width:80px;text-align:center;">Pending</span>';
                                                                    break;
                                                                case 'responded':
                                                                    $status = '<span class="badge bg-success d-block mx-auto" style="min-width:80px;text-align:center;">Responded</span>';
                                                                    break;
                                                                default:
                                                                    $status = '<span class="badge bg-secondary d-block mx-auto" style="min-width:80px;text-align:center;">Unread</span>';
                                                                    break;
                                                            }
                                                            ?>
                                                            <tr class="text-center align-middle">
                                                                <td><?php echo $date; ?></td>
                                                                <td><?php echo $sender; ?></td>
                                                                <td><?php echo $title; ?></td>
                                                                <td><?php echo htmlspecialchars($subject); ?></td>
                                                                <td><?php echo $attachment; ?></td>
                                                                <td><?php echo $status; ?></td>
                                                                <td>
                                                                    <a href="view-msg.php?id=<?php echo $row['id']; ?>"
                                                                        class="btn btn-sm rounded-pill btn-primary"><i
                                                                            class="fa fa-eye"></i></a>
                                                                    <div class="btn-group">
                                                                        <button type="button"
                                                                            class="btn btn-sm rounded-pill btn-light"
                                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i class="fa fa-bars"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                                            <?php
                                                                            $statuses = [
                                                                                'unread' => 'Unread',
                                                                                'read' => 'Read',
                                                                                'pending' => 'Pending',
                                                                                'responded' => 'Responded'
                                                                            ];
                                                                            foreach ($statuses as $key => $label) {
                                                                                if ($key !== $status_value) {
                                                                                    echo '<li>
                                                                                        <form method="POST" action="" style="display:inline;">
                                                                                            <input type="hidden" name="msg_id" value="' . $row['id'] . '">
                                                                                            <input type="hidden" name="new_status" value="' . $key . '">
                                                                                            <button type="submit" class="dropdown-item">' . $label . '</button>
                                                                                        </form>
                                                                                    </li>';
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </ul>
                                                                    </div>
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