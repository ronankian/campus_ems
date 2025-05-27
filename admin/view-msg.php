<?php
session_start();
// Database connection
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$errors = array();
$success = false;

// Fetch message from inbox
$msg_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message_data = null;

if ($msg_id > 0) {
    $query = "SELECT i.*, u.username, u.email, ru.username as reported_username 
              FROM inbox i 
              LEFT JOIN usertable u ON i.user_id = u.id 
              LEFT JOIN usertable ru ON i.reportuser_id = ru.id 
              WHERE i.id = $msg_id 
              LIMIT 1";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $message_data = mysqli_fetch_assoc($result);

        // Update message status to 'read' if it's unread
        if ($message_data['status'] === 'unread') {
            mysqli_query($con, "UPDATE inbox SET status = 'read' WHERE id = $msg_id");
        }
    }
}

// Handle message response
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respond'])
) {
    $response = mysqli_real_escape_string($con, $_POST['response']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $reply_files = [];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $totalSize = 0;
    // Get kept files from hidden input
    $kept_files = [];
    if (isset($_POST['kept_reply_files']) && $_POST['kept_reply_files']) {
        $kept_files = json_decode($_POST['kept_reply_files'], true);
        if (!is_array($kept_files))
            $kept_files = [];
    }
    $reply_files = $kept_files;
    // Handle new uploads
    if (isset($_FILES['reply_file']) && count($_FILES['reply_file']['name']) > 0) {
        foreach ($_FILES['reply_file']['name'] as $key => $name) {
            if ($_FILES['reply_file']['error'][$key] === UPLOAD_ERR_OK) {
                $fileInfo = pathinfo($name);
                $ext = strtolower($fileInfo['extension']);
                $size = $_FILES['reply_file']['size'][$key];
                $totalSize += $size;
                if ($size > $maxFileSize || $totalSize > $maxFileSize) {
                    $errors[] = 'Total file size exceeds 5MB limit.';
                    break;
                } elseif (!in_array($ext, $allowedTypes)) {
                    $errors[] = 'Invalid file type for reply.';
                    break;
                } else {
                    $filename = uniqid() . '_' . basename($name);
                    $targetFile = __DIR__ . '/../uploads/' . $filename;
                    if (move_uploaded_file($_FILES['reply_file']['tmp_name'][$key], $targetFile)) {
                        $reply_files[] = $filename;
                    } else {
                        $errors[] = 'Failed to upload file: ' . htmlspecialchars($name);
                        break;
                    }
                }
            }
        }
    }
    if (empty($response)) {
        $errors[] = 'Response message is required.';
    }
    if (empty($errors)) {
        $reply_files_json = !empty($reply_files) ? json_encode($reply_files) : '';
        $set_reply_file = $reply_files_json ? ", reply_file = '" . mysqli_real_escape_string($con, $reply_files_json) . "'" : '';
        $replied_at = date('Y-m-d H:i:s');
        $query = "UPDATE inbox SET reply = '$response', status = '$status', replied_at = '$replied_at' $set_reply_file WHERE id = $msg_id";
        if (mysqli_query($con, $query)) {
            $success = true;
            $result = mysqli_query($con, $query = "SELECT i.*, u.username, u.email FROM inbox i LEFT JOIN usertable u ON i.user_id = u.id WHERE i.id = $msg_id LIMIT 1");
            if ($result && mysqli_num_rows($result) > 0) {
                $message_data = mysqli_fetch_assoc($result);
            }
        } else {
            $errors[] = 'Failed to send response. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message - Admin Dashboard</title>
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

        .card {
            background: rgba(43, 45, 66, 0.3) !important;
            backdrop-filter: blur(10px) !important;
            color: #fff !important;
            border-radius: 1.5rem !important;
            border: none !important;
        }

        .form-control,
        .form-select {
            background: rgba(43, 45, 66, 0.3) !important;
            color: #fff !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 2px rgba(120, 75, 160, 0.15) !important;
            background: rgba(43, 45, 66, 0.5) !important;
            color: #fff !important;
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

        .dropdown-menu {
            background: var(--surface-dark) !important;
            color: #fff !important;
            border: none !important;
        }

        .dropdown-item {
            color: #fff !important;
        }

        .dropdown-item.active,
        .dropdown-item:active,
        .dropdown-item:hover {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%) !important;
            color: #fff !important;
        }

        .badge {
            font-size: 1rem;
            border-radius: 0.7rem;
        }

        .mb-4 {
            margin-bottom: 2rem !important;
        }

        .mb-3 {
            margin-bottom: 1.2rem !important;
        }

        .rounded-4 {
            border-radius: 1.5rem !important;
        }

        .shadow {
            box-shadow: 0 4px 32px 0 rgba(0, 0, 0, 0.18) !important;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../bg-image.php'; ?>

    <div class="container-fluid">
        <div class="d-flex align-items-start dashboard-container">
            <?php include 'sidebar.php'; ?>
            <div class="flex-grow-1 px-4" style="min-height: 450px;">
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="row align-items-center">
                            <div class="col-6 d-flex align-items-center">
                                <h4 class="fw-bold mb-3 mb-0" style="text-indent: 10px;display:inline-block;">
                                    <a href="inbox.php" style="color:inherit;text-decoration:none;">
                                        Message Inbox
                                    </a> /
                                    <?php if ($message_data): ?>
                                        <?php echo htmlspecialchars($message_data['title']); ?>
                                    </h4>
                                <?php endif; ?>
                            </div>
                            <div class="eventlists col-12">
                                <div class="card shadow rounded-4 border-0">
                                    <div class="card-body">
                                        <?php if (!empty($errors)): ?>
                                            <div class="alert alert-danger">
                                                <?php foreach ($errors as $error)
                                                    echo '<div>' . $error . '</div>'; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($success): ?>
                                            <div class="alert alert-success">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                                    aria-hidden="true"></span>
                                                Response sent successfully. Redirecting...
                                            </div>
                                            <script>setTimeout(function () { window.location.href = 'inbox.php'; }, 2000);</script>
                                        <?php endif; ?>
                                        <?php if ($message_data): ?>
                                            <div class="row g-0">
                                                <div class="col-md-6 p-4 border-end" style="min-height:400px;">
                                                    <div class="mb-3"><span class="fw-bold">From:</span>
                                                        <?php echo htmlspecialchars($message_data['username']); ?></div>
                                                    <div class="mb-3"><span class="fw-bold">Title:</span>
                                                        <?php echo htmlspecialchars($message_data['title']); ?></div>
                                                    <div class="mb-3"><span class="fw-bold">Subject:</span>
                                                        <?php
                                                        if ($message_data['subject_type'] === 'other' && $message_data['subject_custom'] === 'User' && isset($message_data['reported_username'])) {
                                                            echo htmlspecialchars($message_data['reported_username']);
                                                        } else {
                                                            echo htmlspecialchars(!empty($message_data['subject_custom']) ? $message_data['subject_custom'] : ucfirst($message_data['subject_type']));
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="mb-3"><span class="fw-bold">Message:</span></div>
                                                    <div class="mb-3">
                                                        <div
                                                            style="background:rgba(255,255,255,0.08);color:var(--text-main);border-radius:1.2rem;padding:1.2rem 1.5rem;min-height:150px;max-width:100%;">
                                                            <?php echo nl2br(htmlspecialchars($message_data['message'])); ?>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($message_data['attach_file'])): ?>
                                                        <div class="mb-2 d-flex flex-wrap gap-2 align-items-center">
                                                            <?php $files = json_decode($message_data['attach_file'], true);
                                                            if (is_array($files))
                                                                foreach ($files as $file): ?>
                                                                    <a href="../uploads/<?php echo htmlspecialchars($file); ?>"
                                                                        target="_blank" style="text-decoration:none;">
                                                                        <span class="badge bg-secondary px-3 py-2"
                                                                            style="font-size:1rem;border-radius:1rem;cursor:pointer;"><i
                                                                                class="fa fa-paperclip me-1"></i><?php echo htmlspecialchars($file); ?></span>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="text-end text-white mt-3">
                                                        <?php echo date('M d, Y | h:i A', strtotime($message_data['created_at'])); ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 p-4">
                                                    <form method="POST" enctype="multipart/form-data" <?php if (!empty($message_data['reply']))
                                                        echo 'style="display:none;"'; ?>>
                                                        <div class="mb-3"><span class="fw-bold">Status:</span></div>
                                                        <div class="ms-3 mb-4 d-flex flex-wrap gap-4">
                                                            <?php foreach ([['unread', 'Unread'], ['read', 'Read'], ['pending', 'Pending'], ['responded', 'Resolved']] as $opt): ?>
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="radio" name="status"
                                                                        id="status_<?php echo $opt[0]; ?>"
                                                                        value="<?php echo $opt[0]; ?>" <?php if ($message_data['status'] === $opt[0])
                                                                               echo 'checked'; ?>
                                                                        <?php if (!empty($message_data['reply']))
                                                                            echo 'disabled'; ?>>
                                                                    <label class="form-check-label"
                                                                        for="status_<?php echo $opt[0]; ?>"><?php echo $opt[1]; ?></label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <div class="mb-3"><span class="fw-bold">Response:</span></div>
                                                        <div class="mb-3">
                                                            <textarea class="form-control" name="response" rows="5"
                                                                placeholder="Write response here..." required></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                <button type="button" id="replyFileBtn"
                                                                    class="btn btn-outline-secondary"
                                                                    style="height:42px;width:42px;display:flex;align-items:center;justify-content:center;"><i
                                                                        class="fa fa-paperclip"></i></button>
                                                                <input type="file" class="d-none" id="reply_file"
                                                                    name="reply_file[]"
                                                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                                                                <div id="replyFileList"
                                                                    class="d-flex align-items-center gap-2 flex-wrap"></div>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3"></div>
                                                        <div class="d-flex gap-3 justify-content-center">
                                                            <button type="submit" name="respond"
                                                                class="btn btn-primary">Send
                                                                Response</button>
                                                            <a href="inbox.php" class="btn btn-secondary">Back to Inbox</a>
                                                        </div>
                                                    </form>
                                                    <?php if (!empty($message_data['reply'])): ?>
                                                        <div class="" id="adminReplyView">
                                                            <div class="mb-3"><span class="fw-bold">Status:</span></div>
                                                            <div class="ms-3 mb-4 d-flex flex-wrap gap-4">
                                                                <?php foreach ([['unread', 'Unread'], ['read', 'Read'], ['pending', 'Pending'], ['responded', 'Resolved']] as $opt): ?>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="status_view"
                                                                            id="view_status_<?php echo $opt[0]; ?>"
                                                                            value="<?php echo $opt[0]; ?>" <?php if ($message_data['status'] === $opt[0])
                                                                                   echo 'checked'; ?>
                                                                            disabled>
                                                                        <label class="form-check-label"
                                                                            for="view_status_<?php echo $opt[0]; ?>"><?php echo $opt[1]; ?></label>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            <div class="mb-3"><span class="fw-bold">Response:</span></div>
                                                            <div
                                                                style="background:var(--primary);color:#fff;border-radius:1.2rem;padding:1.2rem 1.5rem;min-height:150px;max-width:100%;margin-bottom:1rem;">
                                                                <?php echo nl2br(htmlspecialchars($message_data['reply'])); ?>
                                                            </div>
                                                            <?php if (!empty($message_data['reply_file'])): ?>
                                                                <div class="d-flex flex-wrap gap-2 align-items-center"
                                                                    id="adminReplyFilesView">
                                                                    <?php $reply_files = json_decode($message_data['reply_file'], true);
                                                                    if (is_array($reply_files))
                                                                        foreach ($reply_files as $file): ?>
                                                                            <a href="../uploads/<?php echo htmlspecialchars($file); ?>"
                                                                                target="_blank" style="text-decoration:none;">
                                                                                <span class="badge bg-info px-3 py-2"
                                                                                    style="font-size:1rem;border-radius:1rem;cursor:pointer;"><i
                                                                                        class="fa fa-paperclip me-1"></i><?php echo htmlspecialchars($file); ?></span>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="text-end text-white mt-3 mb-4">
                                                                <?php echo !empty($message_data['replied_at']) ? date('M d, Y | h:i A', strtotime($message_data['replied_at'])) : ''; ?>
                                                            </div>
                                                            <div class="d-flex gap-3 justify-content-center">
                                                                <button type="button" class="btn btn-primary"
                                                                    id="editReplyBtn">Edit</button>
                                                                <a href="inbox.php" class="btn btn-secondary">Back to Inbox</a>

                                                            </div>
                                                        </div>
                                                        <form method="POST" enctype="multipart/form-data" id="editReplyForm"
                                                            style="display:none;">
                                                            <div class="mb-3"><span class="fw-bold">Status:</span></div>
                                                            <div class="ms-4 mb-4 d-flex flex-wrap gap-4">
                                                                <?php foreach ([['unread', 'Unread'], ['read', 'Read'], ['pending', 'Pending'], ['responded', 'Resolved']] as $opt): ?>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio" name="status"
                                                                            id="edit_status_<?php echo $opt[0]; ?>"
                                                                            value="<?php echo $opt[0]; ?>" <?php if ($message_data['status'] === $opt[0])
                                                                                   echo 'checked'; ?>>
                                                                        <label class="form-check-label"
                                                                            for="edit_status_<?php echo $opt[0]; ?>"><?php echo $opt[1]; ?></label>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            <div class="mb-3"><span class="fw-bold">Response:</span></div>
                                                            <div class="mb-3">
                                                                <textarea class="form-control" name="response" rows="5"
                                                                    required><?php echo htmlspecialchars($message_data['reply']); ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                    <button type="button" id="editReplyFileBtn"
                                                                        class="btn btn-outline-secondary"
                                                                        style="height:42px;width:42px;display:flex;align-items:center;justify-content:center;"><i
                                                                            class="fa fa-paperclip"></i></button>
                                                                    <input type="file" class="d-none" id="edit_reply_file"
                                                                        name="reply_file[]"
                                                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                                                                    <div id="editReplyFileList"
                                                                        class="d-flex align-items-center gap-2 flex-wrap"></div>
                                                                </div>
                                                                <div id="editReplyExistingFiles"
                                                                    class="d-flex align-items-center gap-2 flex-wrap mt-2">
                                                                    <?php $reply_files = json_decode($message_data['reply_file'], true);
                                                                    if (is_array($reply_files))
                                                                        foreach ($reply_files as $file): ?>
                                                                            <div class="badge bg-info px-3 py-2 d-flex align-items-center"
                                                                                style="font-size:1rem;border-radius:1rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                                                <a href="../uploads/<?php echo htmlspecialchars($file); ?>"
                                                                                    target="_blank"
                                                                                    style="color:#fff;text-decoration:none;overflow:hidden;text-overflow:ellipsis;max-width:90px;display:inline-block;">
                                                                                    <i class="fa fa-paperclip me-1"></i>
                                                                                    <?php echo htmlspecialchars($file); ?>
                                                                                </a>
                                                                                <button type="button"
                                                                                    class="btn btn-sm btn-link text-white ms-2 p-0"
                                                                                    style="font-size:1.1rem;"
                                                                                    onclick="removeExistingEditFile(this, '<?php echo htmlspecialchars($file); ?>')"
                                                                                    tabindex="-1"><i class="fa fa-times"></i></button>
                                                                            </div>
                                                                        <?php endforeach; ?>
                                                                </div>
                                                                <input type="hidden" name="kept_reply_files"
                                                                    id="keptReplyFilesInput"
                                                                    value='<?php echo htmlspecialchars($message_data['reply_file']); ?>'>
                                                            </div>
                                                            <div class="row mb-3"></div>
                                                            <div class="d-flex gap-3 justify-content-center">
                                                                <button type="submit" name="respond"
                                                                    class="btn btn-primary">Save Changes</button>
                                                                <button type="button" class="btn btn-secondary"
                                                                    id="cancelEditReplyBtn">Cancel</button>
                                                            </div>
                                                        </form>
                                                        <script>
                                                            document.getElementById('editReplyBtn').onclick = function () {
                                                                document.getElementById('adminReplyView').style.display = 'none';
                                                                var filesView = document.getElementById('adminReplyFilesView');
                                                                if (filesView) filesView.style.display = 'none';
                                                                this.style.display = 'none';
                                                                document.getElementById('editReplyForm').style.display = '';
                                                            };
                                                            document.getElementById('cancelEditReplyBtn').onclick = function () {
                                                                document.getElementById('adminReplyView').style.display = '';
                                                                var filesView = document.getElementById('adminReplyFilesView');
                                                                if (filesView) filesView.style.display = '';
                                                                document.getElementById('editReplyBtn').style.display = '';
                                                                document.getElementById('editReplyForm').style.display = 'none';
                                                            };
                                                            // File upload logic for edit form
                                                            const editReplyFileBtn = document.getElementById('editReplyFileBtn');
                                                            const editReplyFileInput = document.getElementById('edit_reply_file');
                                                            const editReplyFileList = document.getElementById('editReplyFileList');
                                                            let editSelectedFiles = [];
                                                            editReplyFileBtn.addEventListener('click', () => editReplyFileInput.click());
                                                            editReplyFileInput.addEventListener('change', function () {
                                                                editSelectedFiles = Array.from(this.files);
                                                                renderEditFileChips();
                                                            });
                                                            function renderEditFileChips() {
                                                                editReplyFileList.innerHTML = '';
                                                                let totalSize = 0;
                                                                editSelectedFiles.forEach((file, idx) => {
                                                                    totalSize += file.size;
                                                                    const chip = document.createElement('div');
                                                                    chip.className = 'badge bg-secondary px-3 py-2 d-flex align-items-center';
                                                                    chip.style.fontSize = '1rem';
                                                                    chip.style.borderRadius = '1rem';
                                                                    chip.style.maxWidth = '160px';
                                                                    chip.style.overflow = 'hidden';
                                                                    chip.style.textOverflow = 'ellipsis';
                                                                    chip.style.whiteSpace = 'nowrap';
                                                                    chip.innerHTML = `<i class='fa fa-paperclip me-1'></i> <span style='overflow:hidden;text-overflow:ellipsis;max-width:90px;display:inline-block;'>${file.name}</span> <button type='button' class='btn btn-sm btn-link text-white ms-2 p-0' style='font-size:1.1rem;' onclick='removeEditFile(${idx})' tabindex='-1'><i class="fa fa-times"></i></button>`;
                                                                    editReplyFileList.appendChild(chip);
                                                                });
                                                                if (totalSize > 5 * 1024 * 1024) {
                                                                    editReplyFileList.insertAdjacentHTML('beforeend', `<span class='text-danger ms-2'>Total file size exceeds 5MB!</span>`);
                                                                }
                                                            }
                                                            window.removeEditFile = function (idx) {
                                                                editSelectedFiles.splice(idx, 1);
                                                                const dt = new DataTransfer();
                                                                editSelectedFiles.forEach(f => dt.items.add(f));
                                                                editReplyFileInput.files = dt.files;
                                                                renderEditFileChips();
                                                            }
                                                            // Existing files removal logic for edit form
                                                            let keptReplyFiles = <?php echo json_encode(is_array($reply_files) ? $reply_files : []); ?>;
                                                            function removeExistingEditFile(btn, filename) {
                                                                btn.parentElement.remove();
                                                                keptReplyFiles = keptReplyFiles.filter(f => f !== filename);
                                                                document.getElementById('keptReplyFilesInput').value = JSON.stringify(keptReplyFiles);
                                                            }
                                                        </script>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">Message not found or you don't have permission
                                                to view it.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script>
        const replyFileBtn = document.getElementById('replyFileBtn');
        const replyFileInput = document.getElementById('reply_file');
        const replyFileList = document.getElementById('replyFileList');
        let selectedFiles = [];
        replyFileBtn.addEventListener('click', () => replyFileInput.click());
        replyFileInput.addEventListener('change', function () {
            selectedFiles = Array.from(this.files);
            renderFileChips();
        });
        function renderFileChips() {
            replyFileList.innerHTML = '';
            let totalSize = 0;
            selectedFiles.forEach((file, idx) => {
                totalSize += file.size;
                const chip = document.createElement('div');
                chip.className = 'badge bg-secondary px-3 py-2 d-flex align-items-center';
                chip.style.fontSize = '1rem';
                chip.style.borderRadius = '1rem';
                chip.style.maxWidth = '160px';
                chip.style.overflow = 'hidden';
                chip.style.textOverflow = 'ellipsis';
                chip.style.whiteSpace = 'nowrap';
                chip.innerHTML = `<i class='fa fa-paperclip me-1'></i> <span style='overflow:hidden;text-overflow:ellipsis;max-width:90px;display:inline-block;'>${file.name}</span> <button type='button' class='btn btn-sm btn-link text-white ms-2 p-0' style='font-size:1.1rem;' onclick='removeFile(${idx})' tabindex='-1'><i class="fa fa-times"></i></button>`;
                replyFileList.appendChild(chip);
            });
            if (totalSize > 5 * 1024 * 1024) {
                replyFileList.insertAdjacentHTML('beforeend', `<span class='text-danger ms-2'>Total file size exceeds 5MB!</span>`);
            }
        }
        window.removeFile = function (idx) {
            selectedFiles.splice(idx, 1);
            // Update the input's FileList (not directly possible, so create a new DataTransfer)
            const dt = new DataTransfer();
            selectedFiles.forEach(f => dt.items.add(f));
            replyFileInput.files = dt.files;
            renderFileChips();
        }
    </script>
</body>

</html>