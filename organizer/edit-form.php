<?php
// Database connection
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$success = false;
$errors = array();
$event_data = [];

// Fetch event data for editing
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $event_id = intval($_GET['id']);
    $result = mysqli_query($con, "SELECT * FROM create_events WHERE id = $event_id LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $event_data = mysqli_fetch_assoc($result);
        $event_data['co_organizer_name'] = !empty($event_data['co_organizer_name']) ? explode(',', $event_data['co_organizer_name']) : [];
        $event_data['co_organizer_username'] = !empty($event_data['co_organizer_username']) ? explode(',', $event_data['co_organizer_username']) : [];
        $event_data['co_organizer_org'] = !empty($event_data['co_organizer_org']) ? explode(',', $event_data['co_organizer_org']) : [];
        $event_data['related_links'] = !empty($event_data['related_links']) ? json_decode($event_data['related_links'], true) : [];
        $event_data['attach_file'] = !empty($event_data['attach_file']) ? json_decode($event_data['attach_file'], true) : [];
    } else {
        $errors[] = 'Event not found.';
    }
} else {
    $errors[] = 'No event selected for editing.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($event_id)) {
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
    $names = isset($_POST['co_organizer_name']) && is_array($_POST['co_organizer_name']) ? $_POST['co_organizer_name'] : [];
    $usernames = isset($_POST['co_organizer_username']) && is_array($_POST['co_organizer_username']) ? $_POST['co_organizer_username'] : [];
    $orgs = isset($_POST['co_organizer_org']) && is_array($_POST['co_organizer_org']) ? $_POST['co_organizer_org'] : [];
    $final_names = [];
    $final_usernames = [];
    $final_orgs = [];
    for ($i = 0; $i < count($names); $i++) {
        $name = trim($names[$i]);
        $username = trim($usernames[$i]);
        $org = trim($orgs[$i]);
        if ($name !== '' || $username !== '' || $org !== '') {
            $final_names[] = $name;
            $final_usernames[] = $username;
            $final_orgs[] = $org;
        }
    }
    $co_organizer_name = mysqli_real_escape_string($con, implode(',', $final_names));
    $co_organizer_username = mysqli_real_escape_string($con, implode(',', $final_usernames));
    $co_organizer_org = mysqli_real_escape_string($con, implode(',', $final_orgs));

    // File upload (multiple files, max 10)
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
    // Handle removal of existing files
    if (isset($_POST['remove_existing_files']) && is_array($_POST['remove_existing_files']) && !empty($event_data['attach_file'])) {
        $files = $event_data['attach_file'];
        foreach ($_POST['remove_existing_files'] as $remove_idx) {
            $remove_idx = intval($remove_idx);
            if (isset($files[$remove_idx])) {
                $file_to_remove = $files[$remove_idx];
                $file_path = __DIR__ . '/../uploads/' . $file_to_remove;
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
                unset($files[$remove_idx]);
            }
        }
        // Re-index array
        $files = array_values($files);
        $event_data['attach_file'] = $files;
    }
    // If no new files uploaded, keep previous files (after removal)
    $attach_files_json = !empty($attach_files) ? json_encode($attach_files) : (isset($event_data['attach_file']) ? json_encode($event_data['attach_file']) : null);

    // Update event in database if no errors
    if (empty($errors)) {
        $query = "UPDATE create_events SET event_title=?, event_description=?, date_time=?, location=?, category=?, contact=?, other_contact=?, related_links=?, attach_file=?, organizer_name=?, organizer_username=?, organizer_org=?, co_organizer_name=?, co_organizer_username=?, co_organizer_org=?, updated_at=NOW() WHERE id=?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, 'sssssssssssssssi', $event_title, $event_description, $date_time, $location, $category, $contact, $other_contact, $related_links, $attach_files_json, $organizer_name, $organizer_username, $organizer_org, $co_organizer_name, $co_organizer_username, $co_organizer_org, $event_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            // Refresh event data for pre-fill after update
            $result = mysqli_query($con, "SELECT * FROM create_events WHERE id = $event_id LIMIT 1");
            if ($result && mysqli_num_rows($result) > 0) {
                $event_data = mysqli_fetch_assoc($result);
                $event_data['co_organizer_name'] = !empty($event_data['co_organizer_name']) ? explode(',', $event_data['co_organizer_name']) : [];
                $event_data['co_organizer_username'] = !empty($event_data['co_organizer_username']) ? explode(',', $event_data['co_organizer_username']) : [];
                $event_data['co_organizer_org'] = !empty($event_data['co_organizer_org']) ? explode(',', $event_data['co_organizer_org']) : [];
                $event_data['related_links'] = !empty($event_data['related_links']) ? json_decode($event_data['related_links'], true) : [];
                $event_data['attach_file'] = !empty($event_data['attach_file']) ? json_decode($event_data['attach_file'], true) : [];
            }
        } else {
            $errors[] = 'Database error: ' . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
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

        .add-btn,
        .remove-btn {
            cursor: pointer;
        }

        .file-size-info {
            font-size: 0.9em;
            color: #888;
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

        .file-chip .file-name {
            display: block;
            min-width: 0;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-chip .btn-remove-file {
            margin-left: 1rem;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #file-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
    </style>
</head>

<body>

    <?php include '../navbar-user.php'; ?>

    <div class="py-4 mb-3"></div>

    <div class="container mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb p-3">
                <li class="breadcrumb-item">
                    <a class="link-body-emphasis" href="#">
                        <i class="fa fa-home" aria-hidden="true"></i>
                        <span class="visually-hidden">Home</span>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a class="link-body-emphasis fw-semibold text-decoration-none" href="dashboard.php">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    Event Creation Form (Edit)
                </li>
            </ol>
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
                            value="<?php echo isset($event_data['event_title']) ? htmlspecialchars($event_data['event_title']) : ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Seminar" <?php echo isset($event_data['category']) && $event_data['category'] === 'Seminar' ? 'selected' : ''; ?>>Seminar</option>
                            <option value="Workshop" <?php echo isset($event_data['category']) && $event_data['category'] === 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
                            <option value="Conference" <?php echo isset($event_data['category']) && $event_data['category'] === 'Conference' ? 'selected' : ''; ?>>Conference</option>
                            <option value="Sports" <?php echo isset($event_data['category']) && $event_data['category'] === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                            <option value="Cultural" <?php echo isset($event_data['category']) && $event_data['category'] === 'Cultural' ? 'selected' : ''; ?>>Cultural</option>
                            <option value="Celebration" <?php echo isset($event_data['category']) && $event_data['category'] === 'Celebration' ? 'selected' : ''; ?>>Celebration</option>
                            <option value="Competition" <?php echo isset($event_data['category']) && $event_data['category'] === 'Competition' ? 'selected' : ''; ?>>Competition</option>
                            <option value="Training" <?php echo isset($event_data['category']) && $event_data['category'] === 'Training' ? 'selected' : ''; ?>>Training</option>
                            <option value="Webinar" <?php echo isset($event_data['category']) && $event_data['category'] === 'Webinar' ? 'selected' : ''; ?>>Webinar</option>
                            <option value="Outreach" <?php echo isset($event_data['category']) && $event_data['category'] === 'Outreach' ? 'selected' : ''; ?>>Outreach</option>
                            <option value="Other" <?php echo isset($event_data['category']) && $event_data['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="event_description" class="form-label">Event Description</label>
                    <textarea class="form-control" id="event_description" name="event_description" rows="3"
                        required><?php echo isset($event_data['event_description']) ? htmlspecialchars($event_data['event_description']) : ''; ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date_time" class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="date_time" name="date_time" required
                            value="<?php echo isset($event_data['date_time']) ? htmlspecialchars($event_data['date_time']) : ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="contact" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="contact" name="contact" required
                            value="<?php echo isset($event_data['contact']) ? htmlspecialchars($event_data['contact']) : ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="other_contact" class="form-label">Other Contact</label>
                        <input type="text" class="form-control" id="other_contact" name="other_contact"
                            value="<?php echo isset($event_data['other_contact']) ? htmlspecialchars($event_data['other_contact']) : ''; ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Location/Venue</label>
                    <input type="text" class="form-control" id="location" name="location" required
                        value="<?php echo isset($event_data['location']) ? htmlspecialchars($event_data['location']) : ''; ?>">
                </div>
                <!-- Related Links and Attach File in one row -->
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label">Related Links</label>
                        <div id="related-links-group">
                            <?php
                            $links = [];
                            if (!empty($event_data['related_links'])) {
                                $links = $event_data['related_links'];
                            }
                            if (empty($links))
                                $links[] = '';
                            $last = count($links) - 1;
                            foreach ($links as $i => $link): ?>
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
                        <div id="file-list" class="d-flex flex-column gap-2 mt-2">
                            <?php if (!empty($event_data['attach_file'])): ?>
                                <?php foreach ($event_data['attach_file'] as $idx => $file): ?>
                                    <div class="file-chip existing-file-row">
                                        <span class="file-name"
                                            title="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></span>
                                        <button type="button" class="btn btn-outline-danger btn-remove-existing-file"
                                            data-index="<?php echo $idx; ?>"><i class="fa fa-times"></i></button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Organizer Info -->
                <h5 class="mt-3 mb-3">Organizer's Information</h5>
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label for="organizer_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="organizer_name" name="organizer_name" required
                            value="<?php echo isset($event_data['organizer_name']) ? htmlspecialchars($event_data['organizer_name']) : ''; ?>"
                            placeholder="Organizer Name">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="organizer_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="organizer_username" name="organizer_username"
                            required
                            value="<?php echo isset($event_data['organizer_username']) ? htmlspecialchars($event_data['organizer_username']) : ''; ?>"
                            placeholder="@username">
                    </div>
                    <div class="col-md-3">
                        <label for="organizer_org" class="form-label">Organization Name</label>
                        <?php include 'org.php'; ?>
                    </div>
                </div>
                <!-- Co-Organizers -->
                <div class="mb-3">
                    <h5 class="mb-3">Co-Organizer's Information</h5>
                    <!-- Single label row -->
                    <div class="row mb-1">
                        <div class="col-md-5"><label class="form-label">Full Name</label></div>
                        <div class="col-md-3"><label class="form-label">Username</label></div>
                        <div class="col-md-3"><label class="form-label">Organization Name</label></div>
                        <div class="col-md-1"></div>
                    </div>
                    <div id="co-organizers-group">
                        <?php foreach ($event_data['co_organizer_name'] as $index => $name): ?>
                            <div class="row gy-2 co-organizer-row">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="co_organizer_name[]"
                                        value="<?php echo htmlspecialchars($name); ?>" placeholder="Co-Organizer Name">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="co_organizer_username[]"
                                        value="<?php echo htmlspecialchars($event_data['co_organizer_username'][$index]); ?>"
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
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-1">
                            <button class="btn btn-outline-secondary add-co-btn" type="button"><i
                                    class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Apply Changes</button>
                    <a href="eventlists.php" class="btn btn-secondary ms-2">Cancel Edit</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                const newRow = document.createElement('div');
                newRow.className = 'row co-organizer-row my-2';
                newRow.innerHTML = `
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="co_organizer_name[]" placeholder="Co-Organizer Name" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="co_organizer_username[]" placeholder="Username" required>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select co-organization-select" name="co_organizer_org[]" required>
                            <option value="">Select Organization</option>
                            <optgroup label="Academic Organization">
                                <option value="ACES" title="Association of Computer Engineering Students">ACES</option>
                                <option value="AHMS" title="Association of Hospitality Management Students">AHMS</option>
                                <option value="BYTE" title="Beacon of Youth Technology Enthusiasts">BYTE</option>
                                <option value="CODE" title="Computer Scientists and Developers Society">CO:DE</option>
                                <option value="FCWTS" title="Federation of Civic Welfare Training Service">FCWTS</option>
                                <option value="FEO" title="Future Educators Organization">FEO</option>
                                <option value="IIEE-CSC" title="Institute of Integrated Electrical Engineers – Council Student Chapters">IIEE-CSC</option>
                                <option value="JHSO" title="Junior High Student Organization">JHSO</option>
                                <option value="JMA" title="Junior Marketing Association">JMA</option>
                                <option value="SHSO" title="Senior High Student Organization">SHSO</option>
                                <option value="SITS" title="Society of Industrial Technology Students">SITS</option>
                                <option value="SPEAR" title="Sports Physical Education and Recreation Club">SPEAR</option>
                            </optgroup>
                            <optgroup label="Non-Academic Organization">
                                <option value="CCERT" title="CvSU-CCAT Emergency Response Team">CCERT</option>
                                <option value="NEXUS" title="The CvSU-R Nexus (Official Student Publication)">NEXUS</option>
                                <option value="ROTARACT" title="Rotaract Club of CvSU-CCAT">ROTARACT</option>
                                <option value="SEC" title="Sikat E-Sports Club">SEC</option>
                            </optgroup>
                            <optgroup label="Performing Arts Groups">
                                <option value="ARTRADS" title="ARTRADS Dance Crew">ARTRADS Dance Crew</option>
                                <option value="CHORALE" title="CvSU-CCAT Chorale">CvSU-CCAT Chorale</option>
                                <option value="SONIC-PISTONS" title="CvSU-CCAT Sonic Pistons Live Band">CvSU-CCAT Sonic Pistons Live Band</option>
                            </optgroup>
                            <optgroup label="Student Body Organization">
                                <option value="CSG" title="Central Student Government of CvSU-CCAT">CSG</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-center">
                        <button class="btn btn-outline-danger remove-co-btn" type="button"><i class="fa fa-times"></i></button>
                    </div>
                `;
                coGroup.appendChild(newRow);
                // Initialize Select2 for the new select
                $(newRow).find('.co-organization-select').select2({
                    width: '100%',
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

        // Attach file: limit to 10 files client-side
        $(document).ready(function () {
            $('#attach_file').on('change', function () {
                if (this.files.length > 10) {
                    alert('You can only upload a maximum of 10 files.');
                    this.value = '';
                }
            });
        });

        // File input preview and removal
        const fileInput = document.getElementById('attach_file');
        const fileList = document.getElementById('file-list');
        fileInput.addEventListener('change', function () {
            // Remove only the new file preview rows
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
                // Remove file from input when X is clicked
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
    </script>
</body>

</html>