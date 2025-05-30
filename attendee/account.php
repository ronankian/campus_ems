<?php
session_start();
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
// Handle About Me update BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['about_me']) || isset($_POST['contact_no']) || isset($_POST['organization']))) {
    $about_me = mysqli_real_escape_string($con, substr($_POST['about_me'], 0, 500));
    $contact_no = isset($_POST['contact_no']) ? mysqli_real_escape_string($con, $_POST['contact_no']) : '';
    $organization = isset($_POST['organization']) ? mysqli_real_escape_string($con, $_POST['organization']) : '';
    // Validate contact_no: numeric, 7-11 digits
    if (preg_match('/^\d{7,11}$/', $contact_no)) {
        $update = mysqli_query($con, "UPDATE usertable SET about='$about_me', contact='$contact_no', organization='$organization' WHERE id='$user_id'");
    } else {
        $update = mysqli_query($con, "UPDATE usertable SET about='$about_me', organization='$organization' WHERE id='$user_id'");
    }
    if ($update) {
        header('Location: account.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub Attendee Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

        .account-details .card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            background: rgba(43, 45, 66, 0.3) !important;
            backdrop-filter: blur(10px) !important;
            color: #fff !important;
            border: none !important;
        }

        .account-details label.form-label {
            font-size: 1.05em;
            color: #888 !important;
        }

        .account-details .fs-5 {
            color: #222;
            font-weight: 500;
        }

        .account-details .btn-outline-primary {
            border-width: 2px;
        }

        .account-details .btn-outline-warning {
            border-width: 2px;
        }

        @media (max-width: 900px) {
            .account-details .card {
                max-width: 100% !important;
            }
        }

        .select2-container--default .select2-selection--single {
            float: right;
            width: 50% !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            text-align: right !important;
        }

        #organizationSelect {
            text-align: right !important;
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
                    <div class="account-details col-12">
                        <?php
                        $user = null;
                        if ($user_id) {
                            $result = mysqli_query($con, "SELECT firstname, lastname, role, created_at, username, email, about, contact, organization FROM usertable WHERE id = '$user_id' LIMIT 1");
                            if ($result && mysqli_num_rows($result) > 0) {
                                $user = mysqli_fetch_assoc($result);
                            }
                        }
                        if ($user):
                            $firstname = $user['firstname'];
                            $lastname = $user['lastname'];
                            $initial = strtoupper(substr($firstname, 0, 1));
                            // Generate a consistent color from the firstname
                            $hash = md5($firstname);
                            $r = hexdec(substr($hash, 0, 2));
                            $g = hexdec(substr($hash, 2, 2));
                            $b = hexdec(substr($hash, 4, 2));
                            $circle_color = "rgb($r, $g, $b)";
                            $role = htmlspecialchars($user['role']);
                            $badge_class = 'bg-danger';
                            if ($role === 'admin')
                                $badge_class = 'bg-info';
                            else if ($role === 'organizer')
                                $badge_class = 'bg-primary';
                            else if ($role === 'attendee')
                                $badge_class = 'bg-success';
                            // Before including org.php, set a variable for the selected value
                            $selected_organization = $user['organization'] ?? '';
                            ?>
                            <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                                <div class="card shadow rounded-4 border-0 p-5 w-100"
                                    style="max-width: 950px; margin: 0 auto; border-radius: 2rem; background: #fff;">
                                    <div class="d-flex flex-column align-items-center mb-4">
                                        <div class="d-flex align-items-center justify-content-center mb-3"
                                            style="width: 120px; height: 120px; border-radius: 50%; background: <?php echo $circle_color; ?>; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                                            <span
                                                style="font-size: 3.8rem; color: #fff; font-weight: 700; letter-spacing: 2px; user-select: none;">
                                                <?php echo $initial; ?> </span>
                                        </div>
                                        <h2 class="fw-bold mb-2 text-center"
                                            style="font-size: 2.4rem; letter-spacing: 1px; color: #fff;">
                                            <?php echo htmlspecialchars($firstname . ' ' . $lastname); ?>
                                        </h2>
                                        <span class="badge <?php echo $badge_class; ?> mb-2"
                                            style="font-size:1.1em; min-width:90px; text-align:center; padding:0.6em 1.2em; border-radius:1em; letter-spacing:1px;">
                                            <?php echo ucfirst($role); ?> </span>
                                        <div class="mt-2 mb-1">
                                            <div class="fs-6 text-white-50">Member since
                                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <form method="POST" id="aboutMeForm">
                                        <div class="row mb-4 w-100" style="max-width:700px;margin:0 auto;">
                                            <div class="col-md-6 col-12 mb-3 mb-md-0">
                                                <label class="form-label fw-semibold text-muted">Username</label>
                                                <div class="fs-5 text-white">
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12 mb-3 mb-md-0 text-end">
                                                <label class="form-label fw-semibold text-muted text-end w-100">Contact
                                                    No.</label>
                                                <input type="text" name="contact_no" id="contactNoInput" pattern="\d{7,11}"
                                                    maxlength="11" minlength="7" class="form-control text-end"
                                                    style="max-width: 130px; float: right;"
                                                    value="<?php echo htmlspecialchars($user['contact'] ?? ''); ?>" <?php if (!isset($_GET['edit_about']))
                                                             echo 'readonly'; ?>
                                                    oninput="this.value=this.value.replace(/[^\d]/g,'').slice(0,11);">
                                            </div>
                                        </div>
                                        <div class="row mb-4 w-100 justify-content-between"
                                            style="max-width:700px;margin:0 auto;">
                                            <div class="col-md-6 col-12 mb-3 mb-md-0">
                                                <label class="form-label fw-semibold text-muted">Email Address</label>
                                                <div class="fs-5 text-white"><?php echo htmlspecialchars($user['email']); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-12 mb-3 mb-md-0 text-end">
                                                <label
                                                    class="form-label fw-semibold text-muted text-end w-100">Organization</label>
                                                <?php include '../organizer/org.php'; ?>
                                            </div>
                                        </div>
                                        <div class="row mb-4 w-100" style="max-width:700px;margin:0 auto;">
                                            <label class="form-label fw-semibold text-muted">About Me</label>
                                            <textarea name="about_me" id="aboutMeInput" maxlength="500" class="form-control"
                                                style="width:100%;resize:none;overflow:hidden;text-indent: 50px;" rows="1"
                                                <?php if (!isset($_GET['edit_about']))
                                                    echo 'readonly'; ?>><?php echo htmlspecialchars($user['about'] ?? ''); ?></textarea>
                                            <div class="text-end text-muted" id="aboutMeCounter" style="font-size: 0.8rem;">
                                                0/500</div>
                                        </div>
                                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center mt-4">
                                            <?php if (!isset($_GET['edit_about'])): ?>
                                                <button type="button"
                                                    class="btn btn-outline-secondary btn-sm px-4 py-2 fw-semibold rounded-pill about-btn-compact"
                                                    id="editAboutBtn"><i class="fa fa-edit"></i> Edit</button>
                                            <?php else: ?>
                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm px-4 py-2 fw-semibold rounded-pill about-btn-compact"
                                                    id="cancelAboutBtn"><i class="fa fa-x"></i> Cancel</button>
                                                <button type="submit"
                                                    class="btn btn-outline-success btn-sm px-4 py-2 fw-semibold rounded-pill about-btn-compact"
                                                    id="confirmAboutBtn"><i class="fa fa-check"></i>
                                                    Confirm</button>
                                            <?php endif; ?>
                                            <?php if (!isset($_GET['edit_about'])): ?>
                                                <a href="../login/forgot-password.php"
                                                    class="btn btn-outline-primary px-4 py-2 fw-semibold rounded-pill">Change
                                                    Password</a>
                                                <?php if ($role === 'banned'): ?>
                                                    <a href="create.php?request_organizer=1&subject_type=request&subject_custom=Request%20Unban"
                                                        class="btn btn-outline-danger px-4 py-2 fw-semibold rounded-pill">Request
                                                        Unban</a>
                                                <?php else: ?>
                                                    <a href="create.php?request_organizer=1"
                                                        class="btn btn-outline-warning px-4 py-2 fw-semibold rounded-pill">Request
                                                        Organizer</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                    <script>
                                        const aboutMeInput = document.getElementById('aboutMeInput');
                                        const contactNoInput = document.getElementById('contactNoInput');
                                        const editAboutBtn = document.getElementById('editAboutBtn');
                                        const cancelAboutBtn = document.getElementById('cancelAboutBtn');
                                        const organizationSelect = document.getElementById('organizationSelect');
                                        if (editAboutBtn) {
                                            editAboutBtn.addEventListener('click', function () {
                                                window.location.href = '?edit_about=1';
                                            });
                                        }
                                        if (cancelAboutBtn) {
                                            cancelAboutBtn.addEventListener('click', function () {
                                                window.location.href = 'account.php';
                                            });
                                        }
                                        if (aboutMeInput) {
                                            function autoResize() {
                                                this.style.height = 'auto';
                                                this.style.height = (this.scrollHeight) + 'px';
                                            }
                                            aboutMeInput.addEventListener('input', autoResize, false);
                                            // Initial resize
                                            aboutMeInput.style.height = 'auto';
                                            aboutMeInput.style.height = (aboutMeInput.scrollHeight) + 'px';
                                            if (!aboutMeInput.hasAttribute('readonly')) {
                                                aboutMeInput.focus();
                                            }
                                            // Add counter logic
                                            const aboutMeCounter = document.getElementById('aboutMeCounter');
                                            aboutMeInput.addEventListener('input', function () {
                                                aboutMeCounter.textContent = this.value.length + '/500';
                                            });
                                            // Initial counter update
                                            aboutMeCounter.textContent = aboutMeInput.value.length + '/500';
                                        }
                                        // Toggle readonly for contactNoInput based on edit mode
                                        if (contactNoInput) {
                                            if (window.location.search.includes('edit_about')) {
                                                contactNoInput.removeAttribute('readonly');
                                            } else {
                                                contactNoInput.setAttribute('readonly', 'readonly');
                                            }
                                        }
                                        if (organizationSelect) {
                                            if (window.location.search.includes('edit_about')) {
                                                organizationSelect.removeAttribute('disabled');
                                                // Enable Select2 only in edit mode
                                                $(organizationSelect).select2({
                                                    dropdownAutoWidth: true,
                                                    width: 'resolve',
                                                    dropdownCssClass: 'org-dropdown'
                                                });
                                            } else {
                                                organizationSelect.setAttribute('disabled', 'disabled');
                                                // Destroy Select2 if not in edit mode
                                                if ($(organizationSelect).hasClass('select2-hidden-accessible')) {
                                                    $(organizationSelect).select2('destroy');
                                                }
                                            }
                                        }
                                    </script>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning text-center">User details not found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>