<?php
session_start();
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
// Fetch events created by this user
$events = [];
$event_query = mysqli_query($con, "SELECT id, event_title FROM create_events WHERE user_id = '$user_id' ORDER BY date_time DESC");
while ($row = mysqli_fetch_assoc($event_query)) {
    $events[] = $row;
}
// Get selected event
$selected_event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
// Build event filter for registrants
$where = '';
if ($selected_event_id > 0) {
    $where = "AND r.event_id = '$selected_event_id'";
}
// Fetch registrants for the user's events (or selected event)
$registrants = [];
$registrant_query = mysqli_query($con, "SELECT r.*, e.event_title FROM registers r JOIN create_events e ON r.event_id = e.id WHERE e.user_id = '$user_id' $where ORDER BY r.registration_date DESC");
while ($row = mysqli_fetch_assoc($registrant_query)) {
    $registrants[] = $row;
}
// Count registrants for selected event or all
if ($selected_event_id > 0) {
    $count_query = mysqli_query($con, "SELECT COUNT(*) as cnt FROM registers r JOIN create_events e ON r.event_id = e.id WHERE e.user_id = '$user_id' AND r.event_id = '$selected_event_id'");
} else {
    $count_query = mysqli_query($con, "SELECT COUNT(*) as cnt FROM registers r JOIN create_events e ON r.event_id = e.id WHERE e.user_id = '$user_id'");
}
$registrant_count = 0;
if ($count_query && $count_row = mysqli_fetch_assoc($count_query)) {
    $registrant_count = $count_row['cnt'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub Organizer Dashboard</title>
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
                        <h4 class="fw-bold mb-3"> Registrants Management</h4>
                    </div>

                    <div class="eventlists col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="export_registrants.php<?php echo $selected_event_id > 0 ? '?event_id=' . $selected_event_id : ''; ?>"
                                class="btn btn-outline-success px-4 fw-semibold"><i
                                    class="fa fa-download me-2"></i>Export</a>
                            <div class="d-flex align-items-center gap-2">
                                <form method="get" class="d-flex align-items-center gap-2">
                                    <select name="event_id" class="form-select" style="min-width: 220px;"
                                        onchange="this.form.submit()">
                                        <option value="0" <?php if ($selected_event_id == 0)
                                            echo 'selected'; ?>>All
                                            Events</option>
                                        <?php foreach ($events as $event): ?>
                                            <option value="<?php echo $event['id']; ?>" <?php if ($selected_event_id == $event['id'])
                                                   echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($event['event_title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <span class="badge bg-success fs-6 px-3 py-2">Registrants:
                                    <?php echo $registrant_count; ?></span>
                            </div>
                        </div>
                        <div class="card shadow rounded-4 border-0">
                            <div class="card-body">
                                <div class="table-responsive" style="min-height: 450px;">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr class="text-center align-middle">
                                                <th>Full Name</th>
                                                <th>Student Number</th>
                                                <th>Year Level</th>
                                                <th>Section</th>
                                                <th>Email Address</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($registrants) === 0): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No Registrants Found.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($registrants as $row): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['student_number']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['year_level']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['section']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                        <td><a href="report_registrant.php?reg_id=<?php echo $row['id']; ?>"
                                                                class="btn btn-sm btn-outline-danger">Report</a></td>
                                                    </tr>
                                                <?php endforeach; ?>
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