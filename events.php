<?php
session_start();
?>

<?php
include 'login/connection.php';
// Update status to 'ended' for events whose ending_time is in the past and not already ended/cancelled
$now = date('Y-m-d H:i:s');
mysqli_query($con, "UPDATE create_events SET status = 'ended' WHERE ending_time < '$now' AND (status IS NULL OR (status != 'ended' AND status != 'cancelled'))");
function getStatusBadge($row)
{
    if (isset($row['status']) && $row['status'] === 'cancelled') {
        return '<span class="badge text-bg-danger">Cancelled</span>';
    } else if (isset($row['status']) && $row['status'] === 'ended') {
        return '<span class="badge text-bg-secondary">Ended</span>';
    } else if (isset($row['status']) && $row['status'] === 'ongoing') {
        return '<span class="badge text-bg-primary">Ongoing</span>';
    } else {
        return '<span class="badge text-bg-success">Active</span>';
    }
}
function getEventDate($row, $current_date)
{
    if (isset($row['status']) && $row['status'] === 'cancelled' && !empty($row['date_cancelled'])) {
        return 'Cancelled: ' . date('F d, Y | h:i A', strtotime($row['date_cancelled']));
    } else {
        return date('F d, Y | h:i A', isset($row['ending_time']) && $row['ending_time'] ? strtotime($row['ending_time']) : strtotime($row['date_time']));
    }
}
$current_date = new DateTime();
$query = "SELECT * FROM create_events ORDER BY GREATEST(IFNULL(updated_at, '1970-01-01 00:00:00'), IFNULL(created_at, '1970-01-01 00:00:00'), IFNULL(date_cancelled, '1970-01-01 00:00:00')) DESC";
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub</title>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
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

        h3,
        h5,
        p,
        small,
        .text-dark,
        .text-secondary {
            color: #fff !important;
        }

        .container,
        .row {
            background: transparent !important;
        }

        .event-listing-row {
            display: flex;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 1.5rem;
        }

        .event-img-link {
            display: block;
            flex: 0 0 340px;
            max-width: 340px;
            text-decoration: none;
        }

        .event-img-box {
            width: 100%;
            height: 180px;
            background: #888;
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .event-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .event-row-link {
            flex-grow: 1;
            color: inherit;
            text-decoration: none;
            transition: background 0.1s;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 180px;
        }

        .event-row-link:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .no-image-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
            opacity: 0.5;
            font-size: 1.3rem;
            font-weight: bold;
            text-align: center;
            pointer-events: none;
            user-select: none;
        }

        .event-filter-group {
            display: flex;
            gap: 18px;
        }

        .event-filter-btn {
            cursor: pointer;
            font-weight: 600;
            color: #fff;
            position: relative;
            font-size: 1.1rem;
            transition: color 0.2s;
        }

        .event-filter-btn[data-filter="all"].active {
            color: #fff;
        }

        .event-filter-btn[data-filter="active"].active {
            color: #28a745;
        }

        .event-filter-btn[data-filter="cancelled"].active {
            color: #dc3545;
        }

        .event-filter-btn[data-filter="ended"].active {
            color: #6c757d;
        }

        .event-filter-btn.active {
            text-decoration: none;
        }

        .event-filter-btn.active::after {
            content: "";
            display: block;
            margin: 3px auto 0 auto;
            width: 80%;
            height: 3px;
            border-radius: 2px;
            background: currentColor;
        }

        .event-filter-btn[data-filter="all"]:not(.active):hover {
            color: #fff;
        }

        .event-filter-btn[data-filter="active"]:not(.active):hover {
            color: #28a745;
        }

        .event-filter-btn[data-filter="cancelled"]:not(.active):hover {
            color: #dc3545;
        }

        .event-filter-btn[data-filter="ended"]:not(.active):hover {
            color: #6c757d;
        }

        .modal-content {
            background: rgba(43, 45, 66, 0.7) !important;
            backdrop-filter: blur(10px) !important;
        }

        .custom-radio-group {
            gap: 2.2rem !important;
        }

        .custom-radio {
            display: flex;
            align-items: center;
            font-size: 1.15rem;
            font-weight: 500;
            color: var(--text-main);
            cursor: pointer;
            position: relative;
            margin-bottom: 0;
            user-select: none;
        }

        .custom-radio input[type="radio"] {
            appearance: none;
            -webkit-appearance: none;
            width: 1.1em;
            height: 1.1em;
            border: 2px solid var(--primary);
            border-radius: 50%;
            outline: none;
            margin-right: 0.6em;
            background: transparent;
            transition: border-color 0.2s, box-shadow 0.2s;
            position: relative;
            box-shadow: 0 0 0 2px rgba(120, 75, 160, 0.08);
        }

        .custom-radio input[type="radio"]:checked {
            border-color: var(--gradient-start);
            background: radial-gradient(circle at 50% 50%, var(--gradient-end) 60%, var(--primary) 100%);
        }

        .custom-radio span {
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .btn-create {
            position: relative;
            background: #fff;
            border: 2px solid transparent;
            z-index: 1;
            border-radius: 50px;
            overflow: hidden;
        }

        .btn-create::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            border-radius: 50px;
            padding: 2px;
            /* border thickness */
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            -webkit-mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }

        .btn-create:hover,
        .btn-create:focus {
            border-radius: 50px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            color: #fff;
            border-color: transparent;
        }

        .modal-content {
            background: var(--surface-dark) !important;
            backdrop-filter: blur(10px) !important;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>
    <?php include 'bg-image.php'; ?>
    <div class="container">
        <div class="row">
            <!-- Main Event Listings -->
            <div class="col-md-8">
                <div class="col-md-12 mb-4 d-flex justify-content-between align-items-end border-bottom">
                    <h3 class="pb-2 fst-italic">Events</h3>
                </div>
                <div id="noEventsMsg" class="text-white-50 py-5 fs-4 text-center" style="display:none;">No Events Found.
                </div>
                <?php while ($row = mysqli_fetch_assoc($result)):
                    $status = isset($row['status']) ? $row['status'] : 'active';
                    $img = null;
                    if (!empty($row['attach_file'])) {
                        $files = json_decode($row['attach_file'], true);
                        if (is_array($files) && count($files) > 0) {
                            foreach ($files as $file) {
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                    $img = 'uploads/' . $file;
                                    break;
                                }
                            }
                        }
                    }
                    $event_title = htmlspecialchars($row['event_title']);
                    $badge = getStatusBadge($row);
                    $date_str = '';
                    if ($status === 'cancelled' && !empty($row['date_cancelled'])) {
                        $date_str = 'Cancelled: ' . date('F d, Y | h:i A', strtotime($row['date_cancelled']));
                    } else {
                        $date_str = date('F d, Y | h:i A', strtotime($row['date_time']));
                        if (!empty($row['ending_time'])) {
                            $date_str .= ' - ' . date('F d, Y | h:i A', strtotime($row['ending_time']));
                        }
                    }
                    ?>
                    <div class="row g-0 my-4 event-row-item" data-status="<?php echo $status; ?>"
                        style="border-radius: 16px; overflow: hidden; position: relative; min-height: 220px; box-shadow: 0 4px 24px rgba(0,0,0,0.18);">
                        <a href="event-details.php?id=<?php echo $row['id']; ?>"
                            style="display:block; width:100%; height:250px; position:relative;">
                            <?php if ($img): ?>
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="Event image"
                                    style="width:100%; height:100%; object-fit:cover; display:block;">
                            <?php else: ?>
                                <div
                                    style="width:100%; height:100%; background:#888; display:flex; align-items:center; justify-content:center;">
                                    <span class="no-image-watermark pb-5">No Image Found</span>
                                </div>
                            <?php endif; ?>
                            <div
                                style="position:absolute;left:0;bottom:0;width:100%;background:linear-gradient(90deg,rgba(43,45,66,0.92) 60%,rgba(43,45,66,0.0) 100%);padding:1.5rem 2.5rem 1.5rem 1.5rem;display:flex;flex-direction:column;align-items:flex-start;">
                                <h4 class="mb-2 text-white fw-bold d-flex align-items-center" style="gap:10px;">
                                    <?php echo $event_title; ?>     <?php echo $badge; ?>
                                </h4>
                                <div class="text-white-50 fw-semibold fs-6">
                                    <?php echo $date_str; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
            <!-- Sidebar -->
            <div class="col-md-4 mb-4 ps-5">
                <div class="position-sticky" style="top: 40px;">
                    <?php
                    $create_link = 'login/login-user.php';
                    $show_org_modal = false;
                    $show_create_btn = false;
                    if (isset($_SESSION['role'])) {
                        if ($_SESSION['role'] === 'organizer') {
                            // Check if organizer has organization
                            $org_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                            $org_result = mysqli_query($con, "SELECT organization FROM usertable WHERE id = '$org_user_id' LIMIT 1");
                            $org_row = mysqli_fetch_assoc($org_result);
                            if (empty($org_row['organization'])) {
                                $show_org_modal = true;
                            } else {
                                $create_link = 'organizer/create-form.php';
                            }
                            $show_create_btn = true;
                        } elseif ($_SESSION['role'] === 'admin') {
                            $create_link = 'organizer/create-form.php';
                            $show_create_btn = true;
                        }
                    }
                    ?>
                    <?php if ($show_create_btn): ?>
                        <a href="<?php echo !$show_org_modal ? $create_link : 'javascript:void(0);'; ?>"
                            class="btn btn-create btn-lg mb-3 text-white" id="createEventBtn"
                            style="font-weight: bold; letter-spacing: 1px; width: 100%;">
                            <i class="fa fa-plus"></i> Create Event
                        </a>
                    <?php endif; ?>
                    <div class="filter pt-2 my-2">
                        <h4 class="fw-bold mb-4"><i class="fa fa-filter"></i> Filter by</h4>
                        <div
                            class="ms-3 mb-4 d-flex flex-wrap gap-4 align-items-center justify-content-center custom-radio-group">
                            <label class="custom-radio">
                                <input class="event-filter-radio" type="radio" name="eventStatusFilter" value="all"
                                    checked>
                                <span>All</span>
                            </label>
                            <label class="custom-radio">
                                <input class="event-filter-radio" type="radio" name="eventStatusFilter" value="active">
                                <span>Active</span>
                            </label>
                            <label class="custom-radio">
                                <input class="event-filter-radio" type="radio" name="eventStatusFilter" value="ongoing">
                                <span>Ongoing</span>
                            </label>
                            <label class="custom-radio">
                                <input class="event-filter-radio" type="radio" name="eventStatusFilter"
                                    value="cancelled">
                                <span>Cancelled</span>
                            </label>
                            <label class="custom-radio">
                                <input class="event-filter-radio" type="radio" name="eventStatusFilter" value="ended">
                                <span>Ended</span>
                            </label>
                        </div>
                    </div>
                    <!-- Modal for missing organization -->
                    <div class="modal fade" id="orgModal" tabindex="-1" aria-labelledby="orgModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content text-white">
                                <div class="modal-body text-center p-4">
                                    <h5 class="mb-3  text-white">You do not have an organization yet.</h5>
                                    <div class="mb-2">
                                        <div class="spinner-border text-success" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <div>Redirecting to account...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var showOrgModal = <?php echo $show_org_modal ? 'true' : 'false'; ?>;
                            if (showOrgModal) {
                                var btn = document.getElementById('createEventBtn');
                                btn.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    var orgModal = new bootstrap.Modal(document.getElementById('orgModal'));
                                    orgModal.show();
                                    setTimeout(function () {
                                        window.location.href = 'organizer/account.php';
                                    }, 3000);
                                });
                            }
                        });
                    </script>
                    <div class="sidebar-ongoing-events w-100 mt-4">
                        <?php include 'ongoing.php'; ?>
                    </div>
                    <div class="sidebar-upcoming-events w-100 mt-4">
                        <?php include 'upcoming.php'; ?>
                    </div>
                    <div class="sidebar-cancelled-events w-100 mt-3">
                        <?php include 'cancelled.php'; ?>
                    </div>
                    <div class="sidebar-recent-events w-100 mt-3">
                        <?php include 'recent.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    </div>
    <script>
        // Event filter functionality for radio buttons
        function updateFilterLabelColors() {
            document.querySelectorAll('.custom-radio').forEach(label => {
                label.classList.remove('text-success', 'text-primary', 'text-danger', 'text-secondary');
            });
            const checked = document.querySelector('.event-filter-radio:checked');
            if (checked) {
                const value = checked.value;
                const label = checked.closest('.custom-radio');
                if (value === 'active') {
                    label.classList.add('text-success');
                } else if (value === 'ongoing') {
                    label.classList.add('text-primary');
                } else if (value === 'cancelled') {
                    label.classList.add('text-danger');
                } else if (value === 'ended') {
                    label.classList.add('text-secondary');
                }
            }
        }
        document.querySelectorAll('.event-filter-radio').forEach(radio => {
            radio.addEventListener('change', function () {
                const filter = this.value;
                let anyVisible = false;
                document.querySelectorAll('.event-row-item').forEach(row => {
                    if (filter === 'all' || row.getAttribute('data-status') === filter) {
                        row.style.display = '';
                        anyVisible = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                document.getElementById('noEventsMsg').style.display = anyVisible ? 'none' : '';
                updateFilterLabelColors();
            });
        });
        // Initial color update on page load
        updateFilterLabelColors();
    </script>

</body>

</html>