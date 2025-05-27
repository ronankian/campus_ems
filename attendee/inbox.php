<?php
session_start();
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
                <div class="row align-items-center">
                    <div class="col-12 d-flex justify-content-between">
                        <h4 class="fw-bold mb-3"> My Message Inbox</h4>
                        <a href="create.php" class="text-decoration-none me-2 fs-4 fw-bold text-white">
                            <i class="fa fa-plus"></i> Create
                        </a>
                    </div>
                    <div class="eventlists col-12">
                        <?php
                        $con = mysqli_connect('localhost', 'root', '', 'campus_ems');
                        if (!$con) {
                            die('Database connection failed: ' . mysqli_connect_error());
                        }
                        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                        $query = "SELECT id, title, subject_type, subject_custom, message, created_at, status 
                                FROM inbox 
                                WHERE user_id = '$user_id' 
                                ORDER BY created_at DESC";
                        $result = mysqli_query($con, $query);
                        ?>
                        <div class="card shadow rounded-4 border-0">
                            <div class="card-body">
                                <div class="table-responsive" style="min-height: 450px;">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr class="text-center align-middle">
                                                <th>Title</th>
                                                <th>Subject</th>
                                                <th>Date & Time</th>
                                                <th>Message</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($result) === 0): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No Messages Found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php while ($row = mysqli_fetch_assoc($result)):
                                                    // Get the full subject (either custom or type)
                                                    $subject = $row['subject_type'] === 'other' ? $row['subject_custom'] : ucfirst($row['subject_type']);

                                                    // Get status badge
                                                    $status = '';
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
                                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                        <td><?php echo htmlspecialchars($subject); ?></td>
                                                        <td><?php echo date('M d, Y | h:i A', strtotime($row['created_at'])); ?>
                                                        </td>
                                                        <td><?php
                                                        // Truncate message if too long
                                                        $message = htmlspecialchars($row['message']);
                                                        echo strlen($message) > 50 ? substr($message, 0, 50) . '...' : $message;
                                                        ?></td>
                                                        <td><?php echo $status; ?></td>
                                                        <td class="text-center align-middle">
                                                            <a href="view-msg.php?id=<?php echo $row['id']; ?>"
                                                                class="btn btn-sm btn-primary" title="View Message"><i
                                                                    class="fa fa-eye"></i></a>
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