<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header('Location: ../login/login-user.php');
    exit();
}
if ($_SESSION['role'] !== 'organizer' && $_SESSION['role'] !== 'admin') {
    header('Location: ../attendee/account.php');
    exit();
}
// Database connection
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Fetch current user info
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($con, "SELECT firstname, lastname, organization FROM usertable WHERE id = '$user_id' LIMIT 1");
$user = mysqli_fetch_assoc($user_query);
$fullname = $user ? $user['firstname'] . ' ' . $user['lastname'] : '';
$org_name = $user ? $user['organization'] : '';

$success = false;
$errors = array();

// Detect edit mode
$edit_mode = false;
$event_data = [];
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $edit_mode = true;
    $event_id = intval($_GET['id']);
    $result = mysqli_query($con, "SELECT * FROM create_events WHERE id = $event_id LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $event_data = mysqli_fetch_assoc($result);
        $event_data['related_links'] = !empty($event_data['related_links']) ? json_decode($event_data['related_links'], true) : [];
    }
}

// Revert to original: process form on any POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $event_title = mysqli_real_escape_string($con, $_POST['event_title']);
    $event_description = mysqli_real_escape_string($con, $_POST['event_description']);
    $date_time = mysqli_real_escape_string($con, $_POST['date_time']);
    $ending_time = mysqli_real_escape_string($con, $_POST['ending_time']);
    $location = mysqli_real_escape_string($con, $_POST['location']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $other_contact = isset($_POST['other_contact']) ? mysqli_real_escape_string($con, $_POST['other_contact']) : '';
    $related_links = isset($_POST['related_links']) ? json_encode(array_filter($_POST['related_links'])) : null;

    // File upload (single file, max 1)
    $attach_files = [];
    if (isset($_FILES['attach_file']) && count($_FILES['attach_file']['name']) > 0) {
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        $maxFiles = 10;
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileCount = 0;
        foreach ($_FILES['attach_file']['name'] as $key => $name) {
            if ($_FILES['attach_file']['error'][$key] === UPLOAD_ERR_OK) {
                if ($_FILES['attach_file']['size'][$key] > $maxFileSize) {
                    $errors[] = 'File size exceeds 5MB limit for ' . htmlspecialchars($name);
                } else {
                    if ($fileCount >= $maxFiles) {
                        $errors[] = 'You can only upload a maximum of 10 files.';
                        break;
                    }
                    $filename = uniqid() . '_' . basename($name);
                    $targetFile = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['attach_file']['tmp_name'][$key], $targetFile)) {
                        $attach_files[] = $filename;
                        $fileCount++;
                    } else {
                        $errors[] = 'Failed to upload file: ' . htmlspecialchars($name);
                    }
                }
            }
        }
    }
    $attach_files_json = !empty($attach_files) ? json_encode($attach_files) : null;

    // Insert or update into database if no errors
    if (empty($errors)) {
        if ($edit_mode) {
            // Update event
            $query = "UPDATE create_events SET event_title=?, event_description=?, date_time=?, ending_time=?, location=?, category=?, contact=?, other_contact=?, related_links=?, attach_file=?, fullname=?, organization=?, user_id=?, updated_at=NOW() WHERE id=?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, 'sssssssssssiii', $event_title, $event_description, $date_time, $ending_time, $location, $category, $contact, $other_contact, $related_links, $attach_files_json, $fullname, $org_name, $user_id, $event_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
            } else {
                $errors[] = 'Database error: ' . mysqli_error($con);
            }
            mysqli_stmt_close($stmt);
        } else {
            // Insert event
            $query = "INSERT INTO create_events (event_title, event_description, date_time, ending_time, location, category, contact, other_contact, related_links, attach_file, fullname, organization, user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, 'ssssssssssssi', $event_title, $event_description, $date_time, $ending_time, $location, $category, $contact, $other_contact, $related_links, $attach_files_json, $fullname, $org_name, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
            } else {
                $errors[] = 'Database error: ' . mysqli_error($con);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        .form-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
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

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        label.form-label {
            color: #f8f9fa !important;
        }

        .add-btn,
        .remove-btn {
            cursor: pointer;
        }

        .file-size-info {
            font-size: 0.9em;
            color: #fff;
        }

        #file-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .file-chip {
            border: 1px solid var(--primary);
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

        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            /* Inverts icon color */
        }

        .custom-select-wrapper {
            position: relative;
            display: inline-block;
        }

        .custom-select {
            appearance: none;
            /* Remove default arrow */
            -webkit-appearance: none;
            /* For Safari */
            -moz-appearance: none;
            /* For Firefox */
            background-color: #fff;
            color: white;
            padding-right: 2.5rem;
            /* Leave space for custom arrow */
        }

        .custom-select-wrapper::after {
            content: '▼';
            /* Or use an SVG/icon font */
            color: white;
            position: absolute;
            right: 2rem;
            top: 75%;
            transform: translateY(-50%);
            pointer-events: none;
        }
    </style>
</head>

<body>

    <?php include '../navbar.php'; ?>
    <?php include '../bg-image.php'; ?>

    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-5" style="background: transparent; color: #fff;">
                <li class="breadcrumb-item">
                    <a class="link" href="../home.php" style="color: #fff;">
                        <i class="fa fa-home" aria-hidden="true"></i>
                        <span class="visually-hidden">Home</span>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a class="fw-semibold text-decoration-none" href="../events.php" style="color: #fff;">
                        Events
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page" style="color: #fff;">
                    Event Creation Form (Edit)
                </li>
            </ol>

            <style>
                .breadcrumb {
                    --bs-breadcrumb-divider-color: #fff;
                }

                .breadcrumb-item+.breadcrumb-item::before {
                    color: #fff !important;
                }
            </style>
        </nav>
    </div>

    <div class="container mt-2">
        <div class="form-container">
            <h2 class="form-title text-white">Event Creation Form (Edit)</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p class="error-message"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success d-flex align-items-center gap-3">
                    <div>
                        <div class="spinner-border text-success" role="status" style="width: 1.5rem; height: 1.5rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div>
                        <span class="success-message">Event edited successfully! Redirecting to Event Lists...</span>
                    </div>
                </div>
                <script>
                    setTimeout(function () {
                        window.location.href = 'eventlists.php';
                    }, 3000);
                </script>
            <?php endif; ?>

            <form method="POST" action="#" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="event_title" class="form-label">Event Title</label>
                        <input type="text" class="form-control" id="event_title" name="event_title" required
                            maxlength="50"
                            value="<?php echo $edit_mode ? htmlspecialchars($event_data['event_title']) : ''; ?>">
                    </div>
                    <div class="custom-select-wrapper col-md-4 mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select custom-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Seminar" <?php echo $edit_mode && $event_data['category'] === 'Seminar' ? 'selected' : ''; ?>>Seminar</option>
                            <option value="Workshop" <?php echo $edit_mode && $event_data['category'] === 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
                            <option value="Conference" <?php echo $edit_mode && $event_data['category'] === 'Conference' ? 'selected' : ''; ?>>Conference</option>
                            <option value="Sports" <?php echo $edit_mode && $event_data['category'] === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                            <option value="Cultural" <?php echo $edit_mode && $event_data['category'] === 'Cultural' ? 'selected' : ''; ?>>Cultural</option>
                            <option value="Celebration" <?php echo $edit_mode && $event_data['category'] === 'Celebration' ? 'selected' : ''; ?>>Celebration</option>
                            <option value="Competition" <?php echo $edit_mode && $event_data['category'] === 'Competition' ? 'selected' : ''; ?>>Competition</option>
                            <option value="Training" <?php echo $edit_mode && $event_data['category'] === 'Training' ? 'selected' : ''; ?>>Training</option>
                            <option value="Webinar" <?php echo $edit_mode && $event_data['category'] === 'Webinar' ? 'selected' : ''; ?>>Webinar</option>
                            <option value="Other" <?php echo $edit_mode && $event_data['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="event_description" class="form-label">Event Description</label>
                    <textarea class="form-control" id="event_description" name="event_description" rows="3"
                        required><?php echo $edit_mode ? htmlspecialchars($event_data['event_description']) : ''; ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date_time" class="form-label">Opening Time</label>
                        <input type="datetime-local" class="form-control" id="date_time" name="date_time" required
                            value="<?php echo $edit_mode ? htmlspecialchars($event_data['date_time']) : ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ending_time" class="form-label">Closing Time</label>
                        <input type="datetime-local" class="form-control" id="ending_time" name="ending_time" required
                            value="<?php echo $edit_mode ? htmlspecialchars($event_data['ending_time']) : ''; ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="contact" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contact" name="contact" required
                            value="<?php echo $edit_mode ? htmlspecialchars($event_data['contact']) : ''; ?>"
                            pattern="[0-9]{7,11}" maxlength="11" minlength="7" inputmode="numeric"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11);">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="other_contact" class="form-label">Other Contact</label>
                        <input type="text" class="form-control" placeholder="Optional" id="other_contact"
                            name="other_contact"
                            value="<?php echo $edit_mode ? htmlspecialchars($event_data['other_contact']) : ''; ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Location/Venue</label>
                    <input type="text" class="form-control" id="location" name="location" required
                        value="<?php echo $edit_mode ? htmlspecialchars($event_data['location']) : ''; ?>">
                </div>
                <!-- Related Links and Attach File in one row -->
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label">Related Links</label>
                        <div id="related-links-group">
                            <?php
                            $links = [];
                            if ($edit_mode && !empty($event_data['related_links'])) {
                                $links = $event_data['related_links'];
                            }
                            if (empty($links))
                                $links[] = '';
                            foreach ($links as $i => $link): ?>
                                <div class="input-group mb-2 related-link-row">
                                    <input type="url" class="form-control" name="related_links[]"
                                        value="<?php echo htmlspecialchars($link); ?>" placeholder="https://example.com">
                                    <?php if ($i === 0): ?>
                                        <button class="btn btn-outline-secondary add-btn" type="button"><i
                                                class="fa fa-plus"></i></button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-danger remove-btn" type="button"><i
                                                class="fa fa-times"></i></button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Organizer Info -->
                        <h5 class="mt-3 mb-3 text-white">Organizer Information</h5>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname"
                                    value="<?php echo htmlspecialchars($fullname); ?>" readonly disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="organization" class="form-label">Organization</label>
                                <input type="text" class="form-control" id="organization" name="organization"
                                    value="<?php echo htmlspecialchars($org_name); ?>" readonly disabled>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="attach_file" class="form-label">Attach File <span class="file-size-info">(Max
                                5MB)</span></label>
                        <input type="file" class="form-control" id="attach_file" name="attach_file[]"
                            accept=".pdf,.doc,.docx,.jpg,.png,.jpeg">
                        <div id="image-preview-container" class="mt-2" style="max-width: 100%; overflow-x: hidden;">
                        </div>
                        <?php if ($edit_mode && !empty($event_data['attach_file'])):
                            $existing_files = json_decode($event_data['attach_file'], true);
                            if (!is_array($existing_files))
                                $existing_files = [$event_data['attach_file']];
                            foreach ($existing_files as $file):
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                    <div id="existing-image-preview" class="mb-2 position-relative" style="display: inline-block;">
                                        <img src="../uploads/<?php echo htmlspecialchars($file); ?>" alt="Attached Image"
                                            style="width: 100%; max-width: 320px; height: 150px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                                        <button type="button" id="remove-existing-image"
                                            class="btn btn-outline-danger rounded-circle"
                                            style="position: absolute; top: 8px; right: 8px;">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="keep_existing_image" id="keep_existing_image" value="1">
                                <?php endif;
                            endforeach;
                        endif; ?>
                    </div>
                </div>
                <div class="text-center d-flex justify-content-center gap-3 mt-4">
                    <button type="submit" class="btn btn-primary">Confirm</button>
                    <a href="eventlists.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php include '../footer.php'; ?>
    <script>
        // Related Links add/remove
        document.addEventListener('DOMContentLoaded', function () {
            const linksGroup = document.getElementById('related-links-group');
            linksGroup.addEventListener('click', function (e) {
                if (e.target.classList.contains('add-btn') || (e.target.tagName === 'I' && e.target.parentElement.classList.contains('add-btn'))) {
                    const newRow = document.createElement('div');
                    newRow.className = 'input-group mb-2 related-link-row';
                    newRow.innerHTML = `<input type=\"url\" class=\"form-control\" name=\"related_links[]\" placeholder=\"https://example.com\">\n<button class=\"btn btn-outline-danger remove-btn\" type=\"button\"><i class=\"fa fa-times\"></i></button>`;
                    linksGroup.appendChild(newRow);
                } else if (e.target.classList.contains('remove-btn') || (e.target.tagName === 'I' && e.target.parentElement.classList.contains('remove-btn'))) {
                    e.target.closest('.related-link-row').remove();
                }
            });
        });

        // Attach file
        $(document).ready(function () {
            $('#attach_file').on('change', function () {
                if (this.files.length > 10) {
                    alert('You can only upload a maximum of 5MB.');
                    this.value = '';
                }
            });
        });

        // Image preview and removal for image files
        document.addEventListener('DOMContentLoaded', function () {
            const attachFileInput = document.getElementById('attach_file');
            const imagePreviewContainer = document.getElementById('image-preview-container');
            attachFileInput.addEventListener('change', function () {
                imagePreviewContainer.innerHTML = '';
                const file = this.files[0];
                if (file && file.type.match('image.*')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.style.position = 'relative';
                        previewDiv.style.display = 'inline-block';
                        previewDiv.innerHTML = `
                            <img src="${e.target.result}" alt="Preview" style="width: 100%; max-width: 320px; height: 150px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                            <button type="button" class="btn btn-outline-danger rounded-circle" style="position: absolute; top: 8px; right: 8px;">
                                <i class="fa fa-times"></i>
                            </button>
                        `;
                        imagePreviewContainer.appendChild(previewDiv);
                        const removeBtn = previewDiv.querySelector('button');
                        removeBtn.addEventListener('click', function () {
                            attachFileInput.value = '';
                            imagePreviewContainer.innerHTML = '';
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        // Remove existing image preview
        document.addEventListener('DOMContentLoaded', function () {
            var removeBtn = document.getElementById('remove-existing-image');
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    var preview = document.getElementById('existing-image-preview');
                    if (preview) preview.style.display = 'none';
                    var keepInput = document.getElementById('keep_existing_image');
                    if (keepInput) keepInput.value = '0';
                });
            }
        });
    </script>
</body>

</html>