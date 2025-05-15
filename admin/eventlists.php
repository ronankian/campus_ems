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


</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="py-5"></div>

    <div class="container mt-3">

        <div class="row">

            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 ps-0">
                <div class="row mb-4">

                    <!-- Section Title -->
                    <div class="col-12">
                        <h4 class="fw-bold"> Event Management</h4>
                    </div>

                    <div class="eventlists col-12">
                        <?php
                        // Database connection
                        $con = mysqli_connect('localhost', 'root', '', 'campus_ems');
                        if (!$con) {
                            die('Database connection failed: ' . mysqli_connect_error());
                        }

                        // Fetch events
                        $query = "SELECT * FROM create_events ORDER BY date_time DESC";
                        $result = mysqli_query($con, $query);

                        // Add status column if it doesn't exist
                        $check_column = "SHOW COLUMNS FROM create_events LIKE 'status'";
                        $column_exists = mysqli_query($con, $check_column);

                        if (mysqli_num_rows($column_exists) == 0) {
                            // Add status column with default value 'active'
                            $alter_table = "ALTER TABLE create_events ADD COLUMN status VARCHAR(20) DEFAULT 'active'";
                            mysqli_query($con, $alter_table);
                        }
                        ?>

                        <div class="card shadow rounded-4 border-0">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Event Title</th>
                                                <th>Date & Time</th>
                                                <th>Location</th>
                                                <th>Organizers</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($result)):
                                                // Get current date and event date
                                                $current_date = new DateTime();
                                                $event_date = new DateTime($row['date_time']);

                                                // Determine status
                                                $status = '';
                                                if (isset($row['status']) && $row['status'] === 'cancelled') {
                                                    $status = '<span class="badge bg-danger">Cancelled</span>';
                                                } else if ($current_date > $event_date) {
                                                    $status = '<span class="badge bg-secondary">Ended</span>';
                                                } else {
                                                    $status = '<span class="badge bg-success">Active</span>';
                                                }

                                                // Combine organizer and co-organizers
                                                $organizers = $row['organizer_name'];
                                                if (!empty($row['co_organizer_name'])) {
                                                    $co_organizers = explode(',', $row['co_organizer_name']);
                                                    $organizers .= ', ' . implode(', ', $co_organizers);
                                                }
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                                                    <td><?php echo date('M d, Y h:i A', strtotime($row['date_time'])); ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                    <td><?php echo htmlspecialchars($organizers); ?></td>
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
                                                                <li><a class="dropdown-item"
                                                                        href="edit-form.php?id=<?php echo $row['id']; ?>">Edit</a>
                                                                </li>
                                                                <?php if (!isset($row['status']) || $row['status'] !== 'cancelled'): ?>
                                                                    <li><a class="dropdown-item" href="javascript:void(0)"
                                                                            onclick="confirmCancel(<?php echo $row['id']; ?>)">Cancel
                                                                            Event</a></li>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
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

        function confirmCancel(eventId) {
            if (confirm('Are you sure you want to cancel this event?')) {
                // Send AJAX request to update status
                fetch('cancel_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'event_id=' + eventId
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
            }
        }
    </script>
</body>

</html>