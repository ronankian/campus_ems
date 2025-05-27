<?php
session_start();
include 'login/connection.php';
function getStatusBadge($row, $current_date)
{
    $event_date = new DateTime($row['date_time']);
    if (isset($row['status']) && $row['status'] === 'cancelled') {
        return '<span class="badge text-bg-danger">Cancelled</span>';
    } else if ($current_date > $event_date) {
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
        return date('F d, Y | h:i A', strtotime($row['date_time']));
    }
}
$current_date = new DateTime();
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
if ($keyword !== '' && $category !== '') {
    $safe_keyword = mysqli_real_escape_string($con, $keyword);
    $safe_category = mysqli_real_escape_string($con, $category);
    $query = "SELECT * FROM create_events WHERE event_title LIKE '%$safe_keyword%' AND category = '$safe_category' ORDER BY date_time DESC";
} else if ($keyword !== '') {
    $safe_keyword = mysqli_real_escape_string($con, $keyword);
    $query = "SELECT * FROM create_events WHERE event_title LIKE '%$safe_keyword%' ORDER BY date_time DESC";
} else if ($category !== '') {
    $safe_category = mysqli_real_escape_string($con, $category);
    $query = "SELECT * FROM create_events WHERE category = '$safe_category' ORDER BY date_time DESC";
} else {
    $query = "SELECT * FROM create_events ORDER BY date_time DESC";
}
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
                <div class="col-md-12 d-flex justify-content-between align-items-end border-bottom">
                    <h3 class="pb-2 fst-italic">Search Results</h3>
                </div>

                <?php if (mysqli_num_rows($result) === 0): ?>
                    <div class="text-white-50 py-5">No events found.</div>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($result)):
                        $status = (isset($row['status']) && $row['status'] === 'cancelled') ? 'cancelled' : (($current_date > new DateTime($row['date_time'])) ? 'ended' : 'active');
                        ?>
                        <div class="row g-0 mb-4 mt-3 event-row-item" data-status="<?php echo $status; ?>">
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
                                    <?php echo getStatusBadge($row, $current_date); ?>
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
                <div class="position-sticky" style="top: 70px;">
                    <a href="organizer/create-form.php" class="btn btn-success btn-lg mt-2"
                        style="font-weight: bold; letter-spacing: 1px; width: 100%;">
                        <i class="fa fa-plus me-2"></i> Create Event
                    </a>
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