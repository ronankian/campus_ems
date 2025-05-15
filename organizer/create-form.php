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
        // For co-organizers, convert comma-separated to array
        $event_data['co_organizer_name'] = !empty($event_data['co_organizer_name']) ? explode(',', $event_data['co_organizer_name']) : [];
        $event_data['co_organizer_username'] = !empty($event_data['co_organizer_username']) ? explode(',', $event_data['co_organizer_username']) : [];
        $event_data['co_organizer_org'] = !empty($event_data['co_organizer_org']) ? explode(',', $event_data['co_organizer_org']) : [];
        $event_data['related_links'] = !empty($event_data['related_links']) ? json_decode($event_data['related_links'], true) : [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $event_title = mysqli_real_escape_string($con, $_POST['event_title']);
    $event_description = mysqli_real_escape_string($con, $_POST['event_description']);
    $date_time = mysqli_real_escape_string($con, $_POST['date_time']);
    $location = mysqli_real_escape_string($con, $_POST['location']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $other_contact = isset($_POST['other_contact']) ? mysqli_real_escape_string($con, $_POST['other_contact']) : '';
    $organizer_name = mysqli_real_escape_string($con, $_POST['organizer_name']);
    $organizer_username = mysqli_real_escape_string($con, $_POST['organizer_username']);
    $organizer_org = mysqli_real_escape_string($con, $_POST['organizer_org']);

    // Related links as JSON
    $related_links = isset($_POST['related_links']) ? json_encode(array_filter($_POST['related_links'])) : null;

    // Co-organizers as comma-separated strings
    $names = isset($_POST['co_organizer_name']) && is_array($_POST['co_organizer_name']) ? array_map('trim', array_filter($_POST['co_organizer_name'])) : [];
    $usernames = isset($_POST['co_organizer_username']) && is_array($_POST['co_organizer_username']) ? array_map('trim', array_filter($_POST['co_organizer_username'])) : [];
    $orgs = isset($_POST['co_organizer_org']) && is_array($_POST['co_organizer_org']) ? array_map('trim', array_filter($_POST['co_organizer_org'])) : [];
    $co_organizer_name = mysqli_real_escape_string($con, implode(',', $names));
    $co_organizer_username = mysqli_real_escape_string($con, implode(',', $usernames));
    $co_organizer_org = mysqli_real_escape_string($con, implode(',', $orgs));

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
            $query = "UPDATE create_events SET event_title=?, event_description=?, date_time=?, location=?, category=?, contact=?, other_contact=?, related_links=?, attach_file=?, organizer_name=?, organizer_username=?, organizer_org=?, co_organizer_name=?, co_organizer_username=?, co_organizer_org=?, updated_at=NOW() WHERE id=?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, 'sssssssssssssssi', $event_title, $event_description, $date_time, $location, $category, $contact, $other_contact, $related_links, $attach_files_json, $organizer_name, $organizer_username, $organizer_org, $co_organizer_name, $co_organizer_username, $co_organizer_org, $event_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
            } else {
                $errors[] = 'Database error: ' . mysqli_error($con);
            }
            mysqli_stmt_close($stmt);
        } else {
            // Insert event
            $query = "INSERT INTO create_events (event_title, event_description, date_time, location, category, contact, other_contact, related_links, attach_file, organizer_name, organizer_username, organizer_org, co_organizer_name, co_organizer_username, co_organizer_org) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, 'sssssssssssssss', $event_title, $event_description, $date_time, $location, $category, $contact, $other_contact, $related_links, $attach_files_json, $organizer_name, $organizer_username, $organizer_org, $co_organizer_name, $co_organizer_username, $co_organizer_org);
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
    <style>
        .form-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        label.form-label {
            color: black !important;
        }

        .add-btn,
        .remove-btn {
            cursor: pointer;
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
    </style>
</head>

<body>

    <?php include '../navbar.php'; ?>
    <?php include '../bg-image.php'; ?>

    <div class="container mb-3">
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
                    Event Creation Form
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
            <h2 class="form-title">Event Creation Form</h2>

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
                        <span class="success-message">Event created successfully! Redirecting to Event Lists...</span>
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
                            value="<?php echo $edit_mode ? htmlspecialchars($event_data['event_title']) : ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
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
                        <label for="date_time" class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="date_time" name="date_time" required
                            value="<?php echo $edit_mode ? htmlspecialchars($event_data['date_time']) : ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="contact" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="contact" name="contact" required
                            value="<?php echo $edit_mode ? htmlspecialchars($event_data['contact']) : ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="other_contact" class="form-label">Other Contact</label>
                        <input type="text" class="form-control" id="other_contact" name="other_contact"
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
                            // Always show at least one input
                            if (empty($links))
                                $links[] = '';
                            $last = count($links) - 1;
                            foreach ($links as $i => $link):
                                ?>
                                <div class="input-group mb-2 related-link-row">
                                    <input type="url" class="form-control" name="related_links[]"
                                        value="<?php echo htmlspecialchars($link); ?>" placeholder="https://example.com">
                                    <?php if ($i == $last): ?>
                                        <button class="btn btn-outline-secondary add-btn" type="button"><i
                                                class="fa fa-plus"></i></button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-danger remove-btn" type="button"><i
                                                class="fa fa-times"></i></button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="attach_file" class="form-label">Attach File <span class="file-size-info">(Max
                                5MB)</span></label>
                        <input type="file" class="form-control" id="attach_file" name="attach_file[]"
                            accept=".pdf,.doc,.docx,.jpg,.png,.jpeg">
                        <div id="file-list" class="mt-2" style="max-width: 100%; overflow-x hidden;"></div>
                    </div>
                </div>
                <!-- Organizer Info -->
                <h5 class="mt-3 mb-3 text-black">Organizer's Information</h5>
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label for="organizer_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="organizer_name" name="organizer_name" required
                            value="<?php echo $edit_mode ? htmlspecialchars($event_data['organizer_name']) : ''; ?>"
                            placeholder="Organizer Name">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="organizer_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="organizer_username" name="organizer_username"
                            required
                            value="<?php echo $edit_mode ? htmlspecialchars($event_data['organizer_username']) : ''; ?>"
                            placeholder="@username">
                    </div>
                    <div class="col-md-3">
                        <label for="organizer_org" class="form-label">Organization Name</label>
                        <?php include 'org.php'; ?>
                    </div>
                </div>
                <!-- Co-Organizers -->
                <div class="mb-3">
                    <h5 class="mb-3 text-black">Add Co-Organizers</h5>
                    <!-- Single label row -->
                    <div class="row mb-1">
                        <div class="col-md-1"></div>
                    </div>
                    <div id="co-organizer-template" style="display:none;">
                        <div class="row gy-2 co-organizer-row">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="co_organizer_name[]"
                                    placeholder="Co-Organizer Name">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="co_organizer_username[]"
                                    placeholder="Username">
                            </div>
                            <div class="col-md-3">
                                <?php include 'co-org.php'; ?>
                            </div>
                            <div class="col-md-1 d-flex align-items-center">
                                <button class="btn btn-outline-danger remove-co-btn" type="button"><i
                                        class="fa fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <div id="co-organizers-group">
                        <?php if ($edit_mode && !empty($event_data['co_organizer_name'])): ?>
                            <?php foreach ($event_data['co_organizer_name'] as $index => $name): ?>
                                <div class="row gy-2 co-organizer-row">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="co_organizer_name[]"
                                            value="<?php echo htmlspecialchars($name); ?>" placeholder="Co-Organizer Name">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" name="co_organizer_username[]"
                                            value="<?php echo htmlspecialchars($event_data['co_organizer_username'][$index] ?? ''); ?>"
                                            placeholder="@username">
                                    </div>
                                    <div class="col-md-3">
                                        <?php include 'co-org.php'; ?>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-center">
                                        <!-- Remove button only for extra rows, added by JS -->
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-1">
                            <button class="btn btn-outline-secondary add-co-btn" type="button"><i
                                    class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit"
                        class="btn btn-primary"><?php echo $edit_mode ? 'Confirm Changes' : 'Create Event'; ?></button>
                </div>
            </form>
        </div>
    </div>
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

            // Co-Organizers add/remove
            const coGroup = document.getElementById('co-organizers-group');
            const addBtn = document.querySelector('.add-co-btn');
            addBtn.addEventListener('click', function () {
                const template = document.getElementById('co-organizer-template');
                const clone = template.firstElementChild.cloneNode(true);

                // Remove any existing Select2 containers in the clone
                $(clone).find('.select2-container').remove();

                // Remove any previous Select2 initialization on the select
                $(clone).find('.co-organization-select').removeClass('select2-hidden-accessible').next('.select2').remove();

                coGroup.appendChild(clone);

                // Re-initialize Select2 for the new select
                $(clone).find('.co-organization-select').select2({
                    width: 'resolve',
                    placeholder: 'Select Organization',
                    allowClear: true
                });
            });
            coGroup.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-co-btn') || e.target.closest('.remove-co-btn')) {
                    e.target.closest('.co-organizer-row').remove();
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
        // Remove existing file (AJAX or mark for removal)
        fileList.querySelectorAll('.btn-remove-existing-file').forEach(btn => {
            btn.addEventListener('click', function () {
                this.parentElement.remove();
                // Optionally, add a hidden input to mark this file for removal on submit
                const idx = this.getAttribute('data-index');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remove_existing_files[]';
                input.value = idx;
                fileList.appendChild(input);
            });
        });

        // Ensure all co-organization-select elements are Select2-enhanced on page load
        $(document).ready(function () {
            $('.co-organization-select').select2({
                width: 'resolve',
                placeholder: 'Select Organization',
                allowClear: true
            });
        });
    </script>
</body>

</html>