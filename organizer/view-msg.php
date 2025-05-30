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
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            background: rgba(43, 45, 66, 0.3) !important;
            backdrop-filter: blur(10px) !important;
            color: #fff !important;
            border: none !important;
        }

        .file-size-info {
            font-size: 0.9em;
            color: #888;
        }

        #file-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .file-chip {
            background: #f8f9fa;
            border: 1px solid #ced4da;
            min-width: 180px;
            max-width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1em;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            padding: 0.25rem 1rem;
        }

        .file-chip .btn-remove-file {
            margin-left: 1rem;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .file-chip .file-name {
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%) !important;
            border: none !important;
            color: #fff !important;
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
                    <div class="create-box col-12">
                        <div class="card shadow rounded-4 border-0">
                            <div class="card-body">
                                <h4 class="fw-bold mb-4 text-center">View Message</h4>
                                <?php
                                $con = mysqli_connect('localhost', 'root', '', 'campus_ems');
                                $errors = array();
                                $success = false;
                                // Fetch message from inbox
                                $msg_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                                $message_data = null;
                                $reply = '';
                                $reply_files = [];
                                if ($msg_id > 0) {
                                    $result = mysqli_query($con, "SELECT * FROM inbox WHERE id = $msg_id LIMIT 1");
                                    if ($result && mysqli_num_rows($result) > 0) {
                                        $message_data = mysqli_fetch_assoc($result);
                                        $title = $message_data['title'];
                                        $subject_type = $message_data['subject_type'];
                                        $subject_custom = $message_data['subject_custom'];
                                        $message = $message_data['message'];
                                        // Get attached files
                                        $attached_files = [];
                                        if (!empty($message_data['attach_file'])) {
                                            $attached_files = json_decode($message_data['attach_file'], true);
                                            if (!is_array($attached_files))
                                                $attached_files = [];
                                        }
                                        // Get reply and reply files
                                        $reply = $message_data['reply'] ?? '';
                                        if (!empty($message_data['reply_file'])) {
                                            $reply_files = json_decode($message_data['reply_file'], true);
                                            if (!is_array($reply_files))
                                                $reply_files = [];
                                        }
                                    }
                                }
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $title = mysqli_real_escape_string($con, $_POST['title']);
                                    $subject_type = mysqli_real_escape_string($con, $_POST['subject_type']);
                                    $subject_custom = isset($_POST['subject_custom']) ? mysqli_real_escape_string($con, $_POST['subject_custom']) : '';
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
                                        $query = "INSERT INTO inbox (user_id, title, subject_type, subject_custom, message, attach_file, created_at) VALUES ('$user_id', '$title', '$subject_type', '$subject_custom', '$message', '$attach_files_json', '$created_at')";
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
                                    <div class="row mb-3 align-items-center">
                                        <label for="title" class="form-label col-md-2 mb-0">Title</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" id="title" name="title"
                                                value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>"
                                                readonly disabled>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label for="subject_type" class="form-label col-md-2 mb-0">Subject</label>
                                        <div class="col-md-3">
                                            <select class="form-select" id="subject_type" name="subject_type" readonly
                                                disabled>
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
                                                <option value="event" <?php if (isset($subject_type) && $subject_type == 'event')
                                                    echo 'selected'; ?>>Event</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label for="subject_custom" class="form-label col-md-2 mb-0">Specified</label>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" id="subject_custom"
                                                name="subject_custom"
                                                value="<?php echo isset($subject_custom) ? htmlspecialchars($subject_custom) : ''; ?>"
                                                readonly disabled>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label for="message" class="form-label col-md-2 mb-0">Message</label>
                                        <div class="col-md-10">
                                            <textarea class="form-control" id="message" name="message" rows="3" readonly
                                                disabled><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <?php if (!empty($attached_files)): ?>
                                        <div class="row mb-3 align-items-center">
                                            <label class="form-label col-md-2 mb-0">Attached File(s):</label>
                                            <div class="col-md-10">
                                                <?php foreach ($attached_files as $file): ?>
                                                    <a href="../uploads/<?php echo htmlspecialchars($file); ?>" target="_blank"
                                                        class="btn btn-sm btn-outline-info mb-1">
                                                        <i class="fa fa-paperclip"></i> <?php echo htmlspecialchars($file); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($reply)): ?>
                                        <?php if (!empty($message_data['replied_at'])): ?>
                                            <div class="row mb-2">
                                                <div class="col-12 text-center mb-2">
                                                    <span class="badge bg-secondary">Replied at:
                                                        <?php echo htmlspecialchars($message_data['replied_at']); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="row mb-3 align-items-center">
                                            <label for="reply_box" class="form-label col-md-2 mb-0">Reply:</label>
                                            <div class="col-md-10">
                                                <textarea class="form-control" id="reply_box" name="reply_box" rows="3"
                                                    readonly disabled
                                                    style="background: rgba(43, 45, 66, 0.3) !important; color: #fff !important; resize: vertical; min-height: 48px;"><?php echo htmlspecialchars($reply); ?></textarea>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($reply_files)): ?>
                                        <div class="row mb-3 align-items-center">
                                            <label class="form-label col-md-2 mb-0">Reply File(s):</label>
                                            <div class="col-md-10">
                                                <?php foreach ($reply_files as $file): ?>
                                                    <a href="../uploads/<?php echo htmlspecialchars($file); ?>" target="_blank"
                                                        class="btn btn-sm btn-outline-info mb-1">
                                                        <i class="fa fa-paperclip"></i> <?php echo htmlspecialchars($file); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="text-center">
                                        <a href="inbox.php" class="btn btn-primary px-4">Back</a>
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