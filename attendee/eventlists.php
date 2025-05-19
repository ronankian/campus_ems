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
        .dashboard-container {
            border-radius: 6px;
            backdrop-filter: blur(8px);
        }

        .card-summary {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .card-summary .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
    </style>

</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../bg-image.php'; ?>


    <div class="container">

        <div class="row dashboard-container p-3 py-4">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 ps-4">
                <div class="row">
                    <div class="col-12">
                        <h4 class="fw-bold mb-3">My Registered Events</h4>
                    </div>
                    <div class="eventlists col-12">
                        <?php
                        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                        $query = "SELECT r.id AS reg_id, r.registration_date, e.id AS event_id, e.event_title, e.date_time, e.location, e.status
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
                                                <th>Event Title</th>
                                                <th>Start</th>
                                                <th>Location</th>
                                                <th>Registration Date</th>
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
                                                    $status = '';
                                                    if (isset($row['status']) && $row['status'] === 'cancelled') {
                                                        $status = '<span class="badge bg-danger">Cancelled</span>';
                                                    } else if (isset($row['status']) && $row['status'] === 'ended') {
                                                        $status = '<span class="badge bg-secondary">Ended</span>';
                                                    } else if (isset($row['status']) && $row['status'] === 'active') {
                                                        $status = '<span class="badge bg-success">Active</span>';
                                                    } else {
                                                        $status = '<span class="badge bg-secondary">Unknown</span>';
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                                                        <td><?php echo date('M d, Y | h:i A', strtotime($row['date_time'])); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                        <td><?php echo date('M d, Y | h:i A', strtotime($row['registration_date'])); ?>
                                                        </td>
                                                        <td><?php echo $status; ?></td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-secondary dropdown-toggle"
                                                                    type="button" id="dropdownMenu<?php echo $row['reg_id']; ?>"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Actions
                                                                </button>
                                                                <ul class="dropdown-menu"
                                                                    aria-labelledby="dropdownMenu<?php echo $row['reg_id']; ?>">
                                                                    <li><a class="dropdown-item"
                                                                            href="../event-details.php?id=<?php echo $row['event_id']; ?>">Event
                                                                            Details</a></li>
                                                                    <li><a class="dropdown-item"
                                                                            href="view.php?reg_id=<?php echo $row['reg_id']; ?>">Registration
                                                                            Details</a></li>
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
    <?php include '../footer.php'; ?>
</body>

</html>