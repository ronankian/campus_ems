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
                    <div class="event-filter-group pb-2">
                        <span class="event-filter-btn active" data-filter="all">All</span>
                        <span class="event-filter-btn" data-filter="active">Active</span>
                        <span class="event-filter-btn" data-filter="cancelled">Cancelled</span>
                        <span class="event-filter-btn" data-filter="ended">Ended</span>
                    </div>
                </div>

                <?php if (mysqli_num_rows(result: $result) === 0): ?>
                    <div class="text-white-50 py-5">No events found.</div>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($result)):
                        $status = isset($row['status']) ? $row['status'] : 'active';
                        ?>
                        <div class="row g-0 my-4 event-row-item" style="backdrop-filter: brightness(0.6); border-radius: 10px;"
                            data-status="<?php echo $status; ?>">
                            <a href="event-details.php?id=<?php echo $row['id']; ?>" class="col-md-6 event-img-link"
                                style="display:block; max-width:340px; min-width:200px;">
                                <div class="event-img-box"
                                    style="width: 100%; height: 180px; background: #888; border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                    <?php
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
                                    if ($img): ?>
                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Event image"
                                            style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <span class="no-image-watermark">No Image Found</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <a href="event-details.php?id=<?php echo $row['id']; ?>"
                                class="col-md-6 event-row-link text-decoration-none ms-3" style="color:inherit;">
                                <h5 class="mt-0 text-white mb-1 d-flex align-items-center" style="gap: 10px;">
                                    <?php echo htmlspecialchars($row['event_title']); ?>
                                    <?php echo getStatusBadge($row); ?>
                                </h5>
                                <p class="mb-2 text-white"
                                    style="flex-grow:1; max-width: 100%; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars(mb_strimwidth($row['event_description'], 0, 230, '...')); ?>
                                </p>
                                <small class="text-white-50">
                                    <?php echo getEventDate($row, $current_date); ?>
                                </small>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
            <!-- Sidebar -->
            <div class="col-md-4 mb-4 ps-5">
                <div class="position-sticky" style="top: 40px;">
                    <?php
                    $create_link = 'login/login-user.php';
                    $show_org_modal = false;
                    if (isset($_SESSION['role'])) {
                        if ($_SESSION['role'] === 'attendee') {
                            $create_link = 'attendee/account.php';
                        } elseif ($_SESSION['role'] === 'organizer') {
                            // Check if organizer has organization
                            $org_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                            $org_result = mysqli_query($con, "SELECT organization FROM usertable WHERE id = '$org_user_id' LIMIT 1");
                            $org_row = mysqli_fetch_assoc($org_result);
                            if (empty($org_row['organization'])) {
                                $show_org_modal = true;
                            } else {
                                $create_link = 'organizer/create-form.php';
                            }
                        }
                    }
                    ?>
                    <a href="<?php echo !$show_org_modal ? $create_link : 'javascript:void(0);'; ?>"
                        class="btn btn-success btn-lg" id="createEventBtn"
                        style="font-weight: bold; letter-spacing: 1px; width: 100%;">
                        <i class="fa fa-plus me-2"></i> Create Event
                    </a>
                    <!-- Modal for missing organization -->
                    <div class="modal fade" id="orgModal" tabindex="-1" aria-labelledby="orgModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content text-black">
                                <div class="modal-body text-center p-4">
                                    <h5 class="mb-3  text-black">You do not have an organization yet.</h5>
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
        // Event filter functionality
        document.querySelectorAll('.event-filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.event-filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const filter = this.getAttribute('data-filter');
                document.querySelectorAll('.event-row-item').forEach(row => {
                    if (filter === 'all' || row.getAttribute('data-status') === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>

</body>

</html>