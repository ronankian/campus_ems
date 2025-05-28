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

        .modal-content {
            background: rgba(43, 45, 66, 0.7) !important;
            backdrop-filter: blur(10px) !important;
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

                    <!-- Section Title -->
                    <div class="col-12 d-flex justify-content-between">
                        <h4 class="fw-bold mb-3"> Event Management</h4>
                        <a href="create-form.php" class="text-decoration-none me-2 fs-4 fw-bold text-white">
                            <i class="fa fa-plus"></i> Create
                        </a>
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
                                                    <td colspan="6" class="text-center text-muted">No Created Event Found.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php while ($row = mysqli_fetch_assoc($result)):
                                                    // Count registrants for this event
                                                    $count_query = "SELECT COUNT(*) as registrant_count FROM registers WHERE event_id = '" . $row['id'] . "'";
                                                    $count_result = mysqli_query($con, $count_query);
                                                    $registrant_count = mysqli_fetch_assoc($count_result)['registrant_count'];

                                                    // Status badge and time
                                                    $status_badge = '';
                                                    $status_time = '';
                                                    $status_time_label = '';
                                                    if (isset($row['status']) && $row['status'] === 'cancelled') {
                                                        $status_badge = '<span class="badge bg-danger">Cancelled</span>';
                                                        $status_time = !empty($row['date_cancelled']) ? date('M d, Y | h:i A', strtotime($row['date_cancelled'])) : '';
                                                        $status_time_label = 'Cancelled at';
                                                    } elseif (isset($row['status']) && $row['status'] === 'ended') {
                                                        $status_badge = '<span class="badge bg-secondary">Ended</span>';
                                                        $status_time = !empty($row['updated_at']) && $row['updated_at'] !== $row['created_at'] ? date('M d, Y | h:i A', strtotime($row['updated_at'])) : date('M d, Y | h:i A', strtotime($row['created_at']));
                                                        $status_time_label = (!empty($row['updated_at']) && $row['updated_at'] !== $row['created_at']) ? 'Updated at' : 'Created at';
                                                    } else {
                                                        $status_badge = '<span class="badge bg-success">Active</span>';
                                                        $status_time = !empty($row['updated_at']) && $row['updated_at'] !== $row['created_at'] ? date('M d, Y | h:i A', strtotime($row['updated_at'])) : date('M d, Y | h:i A', strtotime($row['created_at']));
                                                        $status_time_label = (!empty($row['updated_at']) && $row['updated_at'] !== $row['created_at']) ? 'Updated at' : 'Created at';
                                                    }
                                                    ?>
                                                    <tr class="text-center align-middle">
                                                        <td><?php echo htmlspecialchars($row['category'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                                                        <td>
                                                            <?php
                                                            $start = !empty($row['date_time']) ? date('M d, Y | h:i A', strtotime($row['date_time'])) : '<span class=\'text-muted\'>N/A</span>';
                                                            $end = !empty($row['ending_time']) ? date('M d, Y | h:i A', strtotime($row['ending_time'])) : '<span class=\'text-muted\'>N/A</span>';
                                                            echo $start . ' - ' . $end;
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                        <td>
                                                            <?php echo $status_badge; ?><br>
                                                            <?php if ($status_time): ?>
                                                                <small><?php echo $status_time_label . ': ' . $status_time; ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <a href="/campus_ems/event-details.php?id=<?php echo $row['id']; ?>"
                                                                class="btn btn-sm btn-primary m-1" title="View Details"><i
                                                                    class="fa fa-eye"></i></a>
                                                            <?php if (isset($row['status']) && $row['status'] === 'active'): ?>
                                                                <a href="edit-form.php?id=<?php echo $row['id']; ?>"
                                                                    class="btn btn-sm btn-warning m-1" title="Edit"><i
                                                                        class="fa fa-pen-to-square"></i></a>
                                                                <button class="btn btn-sm btn-danger m-1" title="Cancel Event"
                                                                    onclick="confirmCancel(<?php echo $row['id']; ?>)"><i
                                                                        class="fa fa-ban"></i></button>
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
    <?php include '../footer.php'; ?>

    <!-- Cancel Event Modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="cancelEventModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-3 shadow">
                <form id="cancelEventForm">
                    <div class="modal-body p-4 text-center text-white">
                        <h5 class="mb-2">Cancel Event</h5>
                        <p class="mb-0">Are you sure you want to cancel this event?</p>
                        <div class="mb-3 mt-3 text-start">
                            <label for="cancelReason" class="form-label text-white">Reason for cancelling <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="cancelReason" name="reason" rows="4" maxlength="2500"
                                required placeholder="Please provide your reason"></textarea>
                            <div class="form-text text-white-50">Maximum 500 words.</div>
                        </div>
                    </div>
                    <div class="modal-footer flex-nowrap p-0">
                        <button type="submit"
                            class="btn btn-lg btn-link fs-6 text-decoration-none text-danger col-6 py-3 m-0 rounded-0 border-end"
                            id="confirmCancelBtn"><strong>Yes, cancel</strong></button>
                        <button type="button"
                            class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0"
                            data-bs-dismiss="modal">No thanks</button>
                    </div>
                </form>
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
            var cancelForm = document.getElementById('cancelEventForm');
            if (cancelForm) {
                cancelForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const reason = document.getElementById('cancelReason').value.trim();
                    // Word count check
                    if (reason.split(/\s+/).length > 500) {
                        alert('Reason must not exceed 500 words.');
                        return;
                    }
                    if (!reason) {
                        alert('Reason is required.');
                        return;
                    }
                    if (eventIdToCancel) {
                        fetch('cancel_event.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'event_id=' + encodeURIComponent(eventIdToCancel) + '&reason=' + encodeURIComponent(reason)
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