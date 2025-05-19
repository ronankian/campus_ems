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

                    <!-- Section Title -->
                    <div class="col-12">
                        <h4 class="fw-bold mb-3"> Event Management</h4>
                    </div>

                    <div class="eventlists col-12">
                        <?php
                        // Database connection
                        $con = mysqli_connect('localhost', 'root', '', 'campus_ems');
                        if (!$con) {
                            die('Database connection failed: ' . mysqli_connect_error());
                        }

                        // Fetch events
                        $query = "SELECT *, 
                            CASE 
                                WHEN status = 'active' OR status IS NULL THEN 1
                                WHEN status = 'cancelled' THEN 2
                                ELSE 3
                            END AS status_order
                            FROM create_events 
                            WHERE user_id = '" . $_SESSION['user_id'] . "' 
                            ORDER BY status_order ASC, date_time DESC";
                        $result = mysqli_query($con, $query);

                        // Add status column if it doesn't exist
                        $check_column = "SHOW COLUMNS FROM create_events LIKE 'status'";
                        $column_exists = mysqli_query($con, $check_column);

                        if (mysqli_num_rows($column_exists) == 0) {
                            // Add status column with default value 'active'
                            $alter_table = "ALTER TABLE create_events ADD COLUMN status VARCHAR(20) DEFAULT 'active'";
                            mysqli_query($con, $alter_table);
                        }

                        // Auto-update status to 'ended' for events whose ending_time is in the past and not already ended/cancelled
                        $now = date('Y-m-d H:i:s');
                        mysqli_query($con, "UPDATE create_events SET status = 'ended' WHERE ending_time < '$now' AND (status IS NULL OR (status != 'ended' AND status != 'cancelled'))");
                        ?>

                        <div class="card shadow rounded-4 border-0">
                            <div class="card-body">
                                <div class="table-responsive" style="min-height: 450px;">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr class="text-center align-middle">
                                                <th>Event Title</th>
                                                <th>Start</th>
                                                <th>End</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($result) === 0): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No Created Event Found.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php while ($row = mysqli_fetch_assoc($result)):
                                                    // Get current date and event date
                                                    $current_date = new DateTime();
                                                    $event_date = new DateTime($row['date_time']);

                                                    // Count registrants for this event
                                                    $count_query = "SELECT COUNT(*) as registrant_count FROM registers WHERE event_id = '" . $row['id'] . "'";
                                                    $count_result = mysqli_query($con, $count_query);
                                                    $registrant_count = mysqli_fetch_assoc($count_result)['registrant_count'];

                                                    // Determine status from the status column
                                                    $status = '';
                                                    if (isset($row['status']) && $row['status'] === 'cancelled') {
                                                        $status = '<span class="badge bg-danger">Cancelled</span>';
                                                    } else if (isset($row['status']) && $row['status'] === 'ended') {
                                                        $status = '<span class="badge bg-secondary">Ended</span>';
                                                    } else {
                                                        $status = '<span class="badge bg-success">Active</span>';
                                                    }

                                                    // Organizer
                                                    $organizers = $row['fullname'];
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                                                        <td><?php echo date('M d, Y | h:i A', strtotime($row['date_time'])); ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if (isset($row['status']) && $row['status'] === 'cancelled' && !empty($row['date_cancelled'])) {
                                                                echo '<span class="text-danger">Cancelled: ' . date('M d, Y | h:i A', strtotime($row['date_cancelled'])) . '</span>';
                                                            } else {
                                                                echo !empty($row['ending_time']) ? date('M d, Y | h:i A', strtotime($row['ending_time'])) : '<span class="text-muted">N/A</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                        <td><?php echo $status; ?></td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-secondary dropdown-toggle"
                                                                    type="button" id="dropdownMenu<?php echo $row['id']; ?>"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Actions
                                                                </button>
                                                                <ul class="dropdown-menu"
                                                                    aria-labelledby="dropdownMenu<?php echo $row['id']; ?>">
                                                                    <li><a class="dropdown-item"
                                                                            href="/campus_ems/event-details.php?id=<?php echo $row['id']; ?>">View
                                                                            Details</a></li>
                                                                    <?php if (isset($row['status']) && $row['status'] === 'active'): ?>
                                                                        <li><a class="dropdown-item"
                                                                                href="edit-form.php?id=<?php echo $row['id']; ?>">Edit</a>
                                                                        </li>
                                                                        <li><a class="dropdown-item" href="javascript:void(0)"
                                                                                onclick="confirmCancel(<?php echo $row['id']; ?>)">Cancel
                                                                                Event</a></li>
                                                                    <?php endif; ?>
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

    <!-- Cancel Event Modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="cancelEventModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-body p-4 text-center text-black">
                    <h5 class="mb-2">Cancel Event</h5>
                    <p class="mb-0">Are you sure you want to cancel this event?</p>
                </div>
                <div class="modal-footer flex-nowrap p-0">
                    <button type="button"
                        class="btn btn-lg btn-link fs-6 text-decoration-none text-danger col-6 py-3 m-0 rounded-0 border-end"
                        id="confirmCancelBtn"><strong>Yes, cancel</strong></button>
                    <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0"
                        data-bs-dismiss="modal">No thanks</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Close all dropdowns when clicking outside
        window.onclick = function (event) {
            if (!event.target.matches('.btn')) {
                var dropdowns = document.getElementsByClassName("custom-dropdown-menu");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        function toggleDropdown(id) {
            document.getElementById("dropdown-menu-" + id).classList.toggle("show");
        }

        let eventIdToCancel = null;
        function confirmCancel(eventId) {
            eventIdToCancel = eventId;
            var cancelModal = new bootstrap.Modal(document.getElementById('cancelEventModal'));
            cancelModal.show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            var confirmBtn = document.getElementById('confirmCancelBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    if (eventIdToCancel) {
                        // Send AJAX request to update status
                        fetch('cancel_event.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'event_id=' + eventIdToCancel
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    location.reload();
                                } else {
                                    alert('Error cancelling event: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while cancelling the event');
                            });
                        // Hide modal
                        var cancelModal = bootstrap.Modal.getInstance(document.getElementById('cancelEventModal'));
                        cancelModal.hide();
                        eventIdToCancel = null;
                    }
                });
            }
        });
    </script>
</body>

</html>