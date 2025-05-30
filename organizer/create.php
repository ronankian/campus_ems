<?php
session_start();
// Detect if this is a report for an event
$is_event_report = isset($_GET['event_id']) && isset($_GET['event_title']);
$event_id = $is_event_report ? intval($_GET['event_id']) : null;
$event_title = $is_event_report ? $_GET['event_title'] : '';
// Detect if this is a request to upgrade to organizer
$is_request_organizer = isset($_GET['request_organizer']) && $_GET['request_organizer'] == '1';
$report_user_id = isset($_GET['reportuser_id']) ? intval($_GET['reportuser_id']) : null;
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
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            background: rgba(43, 45, 66, 0.3) !important;
            backdrop-filter: blur(10px) !important;
            color: #fff !important;
            border: none !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%) !important;
            border: none !important;
            color: #fff !important;
        }

        .btn-secondary {
            background: var(--surface-dark) !important;
            color: #fff !important;
            border: none !important;
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
                    <div class="create-box col-12">
                        <div class="card shadow rounded-4 border-0">
                            <div class="card-body">
                                <h4 class="fw-bold mb-4 text-center">Send a Message to Admin</h4>
                                <?php
                                $con = mysqli_connect('localhost', 'root', '', 'campus_ems');
                                $errors = array();
                                $success = false;
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $title = mysqli_real_escape_string($con, $_POST['title']);
                                    // If event report, force subject_type and subject_custom
                                    if ($is_event_report) {
                                        $subject_type = 'event';
                                        $subject_custom = mysqli_real_escape_string($con, $event_title);
                                    } else if ($is_request_organizer) {
                                        $subject_type = 'request';
                                        $subject_custom = 'Upgrade to Organizer';
                                    } else if (isset($_POST['reportuser_id']) && !empty($_POST['reportuser_id'])) {
                                        $subject_type = 'other';
                                        $subject_custom = 'User';
                                        $report_user_id = intval($_POST['reportuser_id']);
                                    } else {
                                        $subject_type = mysqli_real_escape_string($con, $_POST['subject_type']);
                                        $subject_custom = isset($_POST['subject_custom']) ? mysqli_real_escape_string($con, $_POST['subject_custom']) : '';
                                    }
                                    $message = mysqli_real_escape_string($con, $_POST['message']);
                                    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                                    $created_at = date('Y-m-d H:i:s');
                                    if (empty($title))
                                        $errors[] = 'Title is required.';
                                    if (empty($subject_type))
                                        $errors[] = 'Subject is required.';
                                    if ($subject_type === 'other' && empty($subject_custom))
                                        $errors[] = 'Custom subject is required.';
                                    if (empty($message))
                                        $errors[] = 'Message is required.';
                                    if (!$user_id)
                                        $errors[] = 'User not logged in.';

                                    // Handle file upload
                                    $attach_files = [];
                                    if (isset($_FILES['attach_file']) && count($_FILES['attach_file']['name']) > 0) {
                                        $maxFileSize = 5 * 1024 * 1024; // 5MB
                                        $uploadDir = __DIR__ . '/../uploads/';
                                        if (!is_dir($uploadDir)) {
                                            mkdir($uploadDir, 0777, true);
                                        }

                                        foreach ($_FILES['attach_file']['name'] as $key => $name) {
                                            if ($_FILES['attach_file']['error'][$key] === UPLOAD_ERR_OK) {
                                                if ($_FILES['attach_file']['size'][$key] > $maxFileSize) {
                                                    $errors[] = 'File size exceeds 5MB limit for ' . htmlspecialchars($name);
                                                } else {
                                                    $filename = uniqid() . '_' . basename($name);
                                                    $targetFile = $uploadDir . $filename;
                                                    if (move_uploaded_file($_FILES['attach_file']['tmp_name'][$key], $targetFile)) {
                                                        $attach_files[] = $filename;
                                                    } else {
                                                        $errors[] = 'Failed to upload file: ' . htmlspecialchars($name);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $attach_files_json = !empty($attach_files) ? json_encode($attach_files) : null;

                                    if (empty($errors)) {
                                        // Insert with event_id if event report
                                        if ($is_event_report && $event_id) {
                                            $query = "INSERT INTO inbox (user_id, title, subject_type, subject_custom, message, attach_file, event_id, created_at) VALUES ('$user_id', '$title', 'event', '" . mysqli_real_escape_string($con, $event_title) . "', '$message', '$attach_files_json', '$event_id', '$created_at')";
                                        } else if ($is_request_organizer) {
                                            $query = "INSERT INTO inbox (user_id, title, subject_type, subject_custom, message, attach_file, created_at) VALUES ('$user_id', '$title', 'request', 'Upgrade to Organizer', '$message', '$attach_files_json', '$created_at')";
                                        } else if ($report_user_id) {
                                            $query = "INSERT INTO inbox (user_id, title, subject_type, subject_custom, message, attach_file, reportuser_id, created_at) VALUES ('$user_id', '$title', '$subject_type', '$subject_custom', '$message', '$attach_files_json', '$report_user_id', '$created_at')";
                                        } else {
                                            $query = "INSERT INTO inbox (user_id, title, subject_type, subject_custom, message, attach_file, created_at) VALUES ('$user_id', '$title', '$subject_type', '$subject_custom', '$message', '$attach_files_json', '$created_at')";
                                        }
                                        if (mysqli_query($con, $query)) {
                                            $success = true;
                                        } else {
                                            $errors[] = 'Failed to send message. Please try again.';
                                        }
                                    }
                                }
                                ?>
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <?php foreach ($errors as $error): ?>
                                            <p class="mb-0"><?php echo $error; ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($success): ?>
                                    <div class="alert alert-success d-flex align-items-center gap-3">
                                        <div>
                                            <div class="spinner-border text-success" role="status"
                                                style="width: 1.5rem; height: 1.5rem;">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="success-message">Message Sent. Redirecting to Inbox...</span>
                                        </div>
                                        <script>
                                            setTimeout(function () {
                                                window.location.href = 'inbox.php';
                                            }, 2500);
                                        </script>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" action="" autocomplete="off" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required
                                            value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="subject_type" class="form-label">Subject</label>
                                            <?php if ($is_event_report): ?>
                                                <select class="form-select" id="subject_type" name="subject_type" required
                                                    readonly disabled>
                                                    <option value="event" selected>Event</option>
                                                </select>
                                                <input type="hidden" name="subject_type" value="event">
                                            <?php elseif ($is_request_organizer): ?>
                                                <select class="form-select" id="subject_type" name="subject_type" required
                                                    readonly disabled>
                                                    <option value="request" selected>Request</option>
                                                </select>
                                                <input type="hidden" name="subject_type" value="request">
                                            <?php elseif ($report_user_id): ?>
                                                <select class="form-select" id="subject_type" name="subject_type" required
                                                    readonly disabled>
                                                    <option value="other" selected>Other</option>
                                                </select>
                                                <input type="hidden" name="subject_type" value="other">
                                            <?php else: ?>
                                                <select class="form-select" id="subject_type" name="subject_type" required
                                                    onchange="toggleCustomSubject(this.value)">
                                                    <option value="">Select Subject</option>
                                                    <option value="report" <?php if (isset($subject_type) && $subject_type == 'report')
                                                        echo 'selected'; ?>>Report</option>
                                                    <option value="request" <?php if (isset($subject_type) && $subject_type == 'request')
                                                        echo 'selected'; ?>>Request</option>
                                                    <option value="feedback" <?php if (isset($subject_type) && $subject_type == 'feedback')
                                                        echo 'selected'; ?>>Feedback</option>
                                                    <option value="question" <?php if (isset($subject_type) && $subject_type == 'question')
                                                        echo 'selected'; ?>>Question</option>
                                                    <option value="support" <?php if (isset($subject_type) && $subject_type == 'support')
                                                        echo 'selected'; ?>>Support</option>
                                                    <option value="other" <?php if (isset($subject_type) && $subject_type == 'other')
                                                        echo 'selected'; ?>>Other</option>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-8 mb-3" id="customSubjectDiv"
                                            style="display: <?php echo ($is_event_report || $is_request_organizer || $report_user_id || (isset($subject_type) && $subject_type == 'other')) ? 'block' : 'none'; ?>;">
                                            <label for="subject_custom" class="form-label">Specified</label>
                                            <?php if ($is_event_report): ?>
                                                <input type="text" class="form-control" id="subject_custom"
                                                    name="subject_custom"
                                                    value="<?php echo htmlspecialchars($event_title); ?>" readonly disabled>
                                                <input type="hidden" name="subject_custom"
                                                    value="<?php echo htmlspecialchars($event_title); ?>">
                                            <?php elseif ($is_request_organizer): ?>
                                                <input type="text" class="form-control" id="subject_custom"
                                                    name="subject_custom" value="Upgrade to Organizer" readonly disabled>
                                                <input type="hidden" name="subject_custom" value="Upgrade to Organizer">
                                            <?php elseif ($report_user_id): ?>
                                                <input type="text" class="form-control" id="subject_custom"
                                                    name="subject_custom" value="User" readonly disabled>
                                                <input type="hidden" name="subject_custom" value="User">
                                            <?php else: ?>
                                                <input type="text" class="form-control" id="subject_custom"
                                                    name="subject_custom"
                                                    value="<?php echo isset($subject_custom) ? htmlspecialchars($subject_custom) : ''; ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Message</label>
                                        <textarea class="form-control" id="message" name="message" rows="3"
                                            maxlength="2000"
                                            required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                                        <div class="form-text text-white-50 text-end">Maximum 2000 characters.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="attach_file" class="form-label">Attach File <span
                                                class="file-size-info">(Max 5MB)</span></label>
                                        <input type="file" class="form-control" id="attach_file" name="attach_file[]"
                                            accept=".pdf,.doc,.docx,.jpg,.png,.jpeg">
                                        <div id="file-list" class="mt-2" style="max-width: 100%; overflow-x: hidden;">
                                        </div>
                                    </div>
                                    <?php if ($report_user_id): ?>
                                        <input type="hidden" name="reportuser_id" value="<?php echo $report_user_id; ?>">
                                    <?php endif; ?>
                                    <div class="text-center d-flex justify-content-center gap-3">
                                        <button type="submit" class="btn btn-primary px-4">Send</button>
                                        <a href="inbox.php" class="btn btn-secondary px-4">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script>
        function toggleCustomSubject(val) {
            if (val === 'other') {
                document.getElementById('customSubjectDiv').style.display = 'block';
            } else {
                document.getElementById('customSubjectDiv').style.display = 'none';
            }
        }

        // File input preview and removal
        const fileInput = document.getElementById('attach_file');
        const fileList = document.getElementById('file-list');
        fileInput.addEventListener('change', function () {
            fileList.querySelectorAll('.new-file-row').forEach(e => e.remove());
            const file = fileInput.files[0];
            if (file) {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-chip new-file-row';
                fileItem.innerHTML = `
                    <span class="file-name" title="${file.name}">${file.name}</span>
                    <button type="button" class="btn btn-outline-danger btn-remove-file"><i class="fa fa-times"></i></button>
                `;
                fileList.appendChild(fileItem);
                fileList.querySelector('.btn-remove-file').addEventListener('click', function () {
                    fileInput.value = '';
                    fileItem.remove();
                });
            }
        });

        // File size validation
        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                const file = this.files[0];
                if (file.size > 5 * 1024 * 1024) { // 5MB in bytes
                    alert('File size exceeds 5MB limit.');
                    this.value = '';
                    fileList.querySelectorAll('.new-file-row').forEach(e => e.remove());
                }
            }
        });
    </script>
</body>

</html>