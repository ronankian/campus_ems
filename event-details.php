<?php
session_start();
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        .container,
        .row,
        .card,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        small,
        label,
        span,
        div,
        a,
        button,
        input,
        textarea {
            font-family: 'Poppins', sans-serif !important;
        }

        h3,
        h5,
        p,
        small,
        .text-dark,
        .text-secondary {
            color: #fff !important;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>
    <?php include 'bg-image.php'; ?>
    <div class="container">
        <div class="row">
            <?php
            include 'login/connection.php';
            $event = null;
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $event_id = intval($_GET['id']);
                $result = mysqli_query($con, "SELECT * FROM create_events WHERE id = $event_id LIMIT 1");
                if ($result && mysqli_num_rows($result) > 0) {
                    $event = mysqli_fetch_assoc($result);
                }
            }
            $already_registered = false;
            $reg_id = null;
            if (isset($_SESSION['user_id']) && isset($event['id'])) {
                $user_id = $_SESSION['user_id'];
                $event_id = $event['id'];
                $reg_check = mysqli_query($con, "SELECT id FROM registers WHERE user_id = '$user_id' AND event_id = '$event_id' LIMIT 1");
                if ($reg_check && mysqli_num_rows($reg_check) > 0) {
                    $already_registered = true;
                    $reg_row = mysqli_fetch_assoc($reg_check);
                    $reg_id = $reg_row['id'];
                }
            }
            // Auto-update status to 'ended' if needed
            $now = date('Y-m-d H:i:s');
            mysqli_query($con, "UPDATE create_events SET status = 'ended' WHERE ending_time < '$now' AND (status IS NULL OR (status != 'ended' AND status != 'cancelled'))");
            // Fetch organizer's organization from usertable
            $organizer_org = '';
            if (!empty($event['user_id'])) {
                $org_result = mysqli_query($con, "SELECT organization FROM usertable WHERE id = '" . $event['user_id'] . "' LIMIT 1");
                if ($org_result && mysqli_num_rows($org_result) > 0) {
                    $org_row = mysqli_fetch_assoc($org_result);
                    $organizer_org = $org_row['organization'] ?? '';
                }
            }
            // Fetch user role if logged in
            $user_role = null;
            if (isset($_SESSION['user_id'])) {
                $uid = $_SESSION['user_id'];
                $role_result = mysqli_query($con, "SELECT role FROM usertable WHERE id = '$uid' LIMIT 1");
                if ($role_result && mysqli_num_rows($role_result) > 0) {
                    $role_row = mysqli_fetch_assoc($role_result);
                    $user_role = $role_row['role'];
                }
            }
            ?>
            <div class="col-md-8 mx-auto">
                <?php if ($event): ?>
                    <div class="card p-3" style="border:1px solid #444; background: transparent;">
                        <!-- Image -->
                        <div class="mb-1"
                            style="width:100%; height:250px; background:#888; border-radius:4px; overflow:hidden; display:flex; align-items:center; justify-content:center; position:relative;">
                            <?php
                            $img = null;
                            if (!empty($event['attach_file'])) {
                                $files = json_decode($event['attach_file'], true);
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
                                <span class="no-image-watermark"
                                    style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;opacity:0.5;font-size:1.3rem;font-weight:bold;text-align:center;pointer-events:none;user-select:none;">No
                                    Image Found</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between mb-5 border-bottom">
                            <div>
                                <small>Category</small><br><span
                                    class="fw-semibold fs-5 text-white"><?php echo htmlspecialchars($event['category'] ?? ''); ?></span>
                            </div>
                            <div class="text-end">
                                <?php
                                if (!empty($event['date_cancelled'])) {
                                    echo '<small>Cancelled at</small><br><span class="fw-semibold fs-5 text-white">' . date('F d, Y \| h:i A', strtotime($event['date_cancelled'])) . '</span>';
                                } elseif (!empty($event['updated_at']) && $event['updated_at'] !== $event['created_at']) {
                                    echo '<small>Updated at</small><br><span class="fw-semibold fs-5 text-white">' . date('F d, Y \| h:i A', strtotime($event['updated_at'])) . '</span>';
                                } else {
                                    echo '<small>Created at</small><br><span class="fw-semibold fs-5 text-white">' . date('F d, Y \| h:i A', strtotime($event['created_at'])) . '</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="text-center mb-2">
                            <h2 class="mb-2 text-white fw-bold"><?php echo htmlspecialchars($event['event_title']); ?>
                            </h2>
                            <div class="mb-3">
                                <?php
                                $status = $event['status'] ?? 'active';
                                if ($status === 'cancelled') {
                                    echo '<span class="badge bg-danger">Cancelled</span>';
                                } else if ($status === 'ended') {
                                    echo '<span class="badge bg-secondary">Ended</span>';
                                } else {
                                    echo '<span class="badge bg-success">Active</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-white">
                                <div><small>Event Starts at</small><br>
                                    <span>
                                        <?php
                                        if ($status === 'cancelled' && !empty($event['date_cancelled'])) {
                                            echo '<span class="fw-semibold">' . date('F d, Y \| h:i A', strtotime($event['date_cancelled'])) . '</span>';
                                        } else {
                                            echo '<span class="fw-semibold">' . date('F d, Y \| h:i A', strtotime($event['date_time'])) . '</span>';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="mt-2"><small>Event Ends at</small><br>
                                    <span>
                                        <?php
                                        if (!empty($event['ending_time'])) {
                                            echo '<span class="fw-semibold">' . date('F d, Y \| h:i A', strtotime($event['ending_time'])) . '</span>';
                                        } else {
                                            echo '<span class="fw-semibold text-muted">N/A</span>';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-6 text-end text-white">
                                <div class="mb-2">
                                    <small>Location</small><br><span
                                        class="fw-semibold"><?php echo htmlspecialchars($event['location'] ?? ''); ?></span>
                                </div>
                                <div>
                                    <small>Contacts</small>
                                    <?php
                                    if (!empty($event['contact'])) {
                                        $contacts = preg_split('/[\r\n,]+/', $event['contact']);
                                        foreach ($contacts as $contact) {
                                            $contact = trim($contact);
                                            if ($contact) {
                                                echo '<div class="fw-semibold">' . htmlspecialchars($contact) . '</div>';
                                            }
                                        }
                                    }
                                    if (!empty($event['other_contact'])) {
                                        $other_contacts = preg_split('/[\r\n,]+/', $event['other_contact']);
                                        foreach ($other_contacts as $other) {
                                            $other = trim($other);
                                            if ($other) {
                                                echo '<div class="fw-semibold>' . htmlspecialchars($other) . '</div>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="mb-1">
                                <small>Event Description</small>
                            </label>
                            <div
                                style="min-height:120px; padding:8px; background: transparent; color: #fff; text-indent: 50px;">
                                <?php echo nl2br(htmlspecialchars($event['event_description'] ?? '')); ?>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label>
                                <h5>Organizer</h5>
                            </label>
                            <div class="mb-2"
                                style="padding:4px 8px; border-radius:3px; background: transparent; color: #fff;">
                                <?php
                                $organizer_name = $event['fullname'] ?? '';
                                if ($organizer_name) {
                                    echo htmlspecialchars($organizer_name);
                                    if ($organizer_org) {
                                        echo ' (' . htmlspecialchars($organizer_org) . ')';
                                    }
                                } else {
                                    echo '<span class="text-muted">N/A</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label>
                                <h5>Related Links</h5>
                            </label>
                            <div class="mb-2"
                                style="padding:4px 8px; border-radius:3px; background: transparent; color: #fff;">
                                <?php
                                if (!empty($event['related_links'])) {
                                    $links = json_decode($event['related_links'], true);
                                    if (is_array($links)) {
                                        foreach ($links as $link) {
                                            $link = trim($link);
                                            if (filter_var($link, FILTER_VALIDATE_URL)) {
                                                echo '<div><a href="' . htmlspecialchars($link) . '" target="_blank" rel="noopener noreferrer" style="color:#4fc3f7;word-break:break-all;">' . htmlspecialchars($link) . '</a></div>';
                                            } else {
                                                echo '<div>' . htmlspecialchars($link) . '</div>';
                                            }
                                        }
                                    } else {
                                        $link = trim($event['related_links']);
                                        if (filter_var($link, FILTER_VALIDATE_URL)) {
                                            echo '<div><a href="' . htmlspecialchars($link) . '" target="_blank" rel="noopener noreferrer" style="color:#4fc3f7;word-break:break-all;">' . htmlspecialchars($link) . '</a></div>';
                                        } else {
                                            echo htmlspecialchars($event['related_links']);
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger mt-5">Event not found.</div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4 mb-4 ps-5">
                <div class="position-sticky" style="top: 50px;">
                    <?php
                    if ($status === 'cancelled' || $status === 'ended') {
                        echo '<a href="events.php" class="btn btn-primary btn-lg mt-2" style="font-weight: bold; letter-spacing: 1px; width: 100%;">
                            <i class="fa fa-calendar-days me-2"></i> Other Events
                        </a>';
                    } else {
                        $show_register_btn = true;
                        if (isset($_SESSION['role']) && $_SESSION['role'] === 'organizer' && isset($_SESSION['user_id']) && isset($event['user_id']) && $_SESSION['user_id'] == $event['user_id']) {
                            $show_register_btn = false;
                        }
                        if ($show_register_btn) {
                            // Hide register button if user is banned
                            if ($user_role !== 'banned') {
                                if (isset($_SESSION['role']) && !empty($_SESSION['role'])) {
                                    if ($already_registered && $reg_id) {
                                        $register_link = 'attendee/view.php?reg_id=' . $reg_id;
                                    } else {
                                        $register_link = 'attendee/reg-form.php?event_title=' . urlencode($event['event_title']);
                                    }
                                } else {
                                    $register_link = 'login/login-user.php';
                                }
                                echo '<a href="' . $register_link . '" class="btn btn-outline-success btn-lg mt-2 w-100" style="font-weight: bold; letter-spacing: 1px;">
                                    <i class="fa fa-plus me-2"></i> Register
                                </a>';
                            }
                        }
                    }
                    if (isset($_SESSION['role'])) {
                        if ($_SESSION['role'] === 'attendee') {
                            if ($status !== 'cancelled' && $status !== 'ended') {
                                echo '<a href="attendee/create.php?event_id=' . $event['id'] . '&event_title=' . urlencode($event['event_title']) . '" class="btn btn-outline-danger btn-lg mt-4 w-100" style="font-weight: bold; letter-spacing: 1px;">
                                    <i class="fa fa-flag me-2"></i> Report
                                </a>';
                            }
                        } elseif ($_SESSION['role'] === 'organizer') {
                            if ($status !== 'cancelled' && $status !== 'ended') {
                                if (isset($event['user_id']) && $_SESSION['user_id'] == $event['user_id']) {
                                    echo '<a href="organizer/edit-form.php?id=' . $event['id'] . '" class="btn btn-outline-warning btn-lg mt-4 w-100" style="font-weight: bold; letter-spacing: 1px;">
                                        <i class="fa fa-edit me-2"></i> Edit
                                    </a>';
                                }
                            }
                            if (isset($event['user_id']) && $_SESSION['user_id'] != $event['user_id'] && $status !== 'cancelled' && $status !== 'ended') {
                                echo '<a href="attendee/create.php?event_id=' . $event['id'] . '&event_title=' . urlencode($event['event_title']) . '" class="btn btn-outline-danger btn-lg mt-4 w-100" style="font-weight: bold; letter-spacing: 1px;">
                                    <i class="fa fa-flag me-2"></i> Report
                                </a>';
                            }
                        }
                    }
                    ?>
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

</body>

</html>