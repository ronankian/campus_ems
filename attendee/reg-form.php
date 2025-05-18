<?php
session_start();
// Database connection
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');

// Initialize variables
$errors = array();
$success = false;

// Fetch event_id from event title
$event_id = null;
if (isset($_GET['event_title'])) {
    $event_title = mysqli_real_escape_string($con, $_GET['event_title']);
    $event_query = "SELECT id FROM create_events WHERE event_title = '$event_title' LIMIT 1";
    $event_result = mysqli_query($con, $event_query);
    if ($event_result && mysqli_num_rows($event_result) > 0) {
        $event_row = mysqli_fetch_assoc($event_result);
        $event_id = $event_row['id'];
    }
}

// Get user_id from session (assuming user is logged in and user_id is stored in session)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_data = null;
if ($user_id) {
    $user_query = "SELECT firstname, lastname FROM usertable WHERE id = '$user_id'";
    $user_result = mysqli_query($con, $user_query);
    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user_data = mysqli_fetch_assoc($user_result);
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $year_level = mysqli_real_escape_string($con, $_POST['year_level']);
    $section = mysqli_real_escape_string($con, $_POST['section']);
    $student_number = mysqli_real_escape_string($con, $_POST['student_number']);
    $contact_number = mysqli_real_escape_string($con, $_POST['contact_number']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $terms_accepted = isset($_POST['terms']) ? 1 : 0;

    // Fetch event_id again from POST (in case of form submission)
    $event_title_post = mysqli_real_escape_string($con, $_POST['event']);
    $event_id = null;
    $event_query = "SELECT id FROM create_events WHERE event_title = '$event_title_post' LIMIT 1";
    $event_result = mysqli_query($con, $event_query);
    if ($event_result && mysqli_num_rows($event_result) > 0) {
        $event_row = mysqli_fetch_assoc($event_result);
        $event_id = $event_row['id'];
    }

    // Use the user's firstname and lastname from usertable
    $first_name = isset($user_data['firstname']) ? mysqli_real_escape_string($con, $user_data['firstname']) : '';
    $last_name = isset($user_data['lastname']) ? mysqli_real_escape_string($con, $user_data['lastname']) : '';

    // Validation
    if (empty($first_name))
        $errors[] = "First name is required";
    if (empty($last_name))
        $errors[] = "Last name is required";
    if (empty($year_level))
        $errors[] = "Year level is required";
    if (empty($section))
        $errors[] = "Section is required";
    if (empty($student_number))
        $errors[] = "Student number is required";
    if (empty($contact_number))
        $errors[] = "Contact number is required";
    if (empty($email))
        $errors[] = "Email is required";
    if (!$terms_accepted)
        $errors[] = "You must agree to the terms and conditions";

    // Check if student number or email already exists
    $check_query = "SELECT * FROM registers WHERE student_number = '$student_number' OR email = '$email'";
    $result = mysqli_query($con, $check_query);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Student number or email already registered";
    }

    // Check if already registered for this event by this user
    if ($user_id && $event_id) {
        $check_unique = "SELECT * FROM registers WHERE user_id = '$user_id' AND event_id = '$event_id'";
        $unique_result = mysqli_query($con, $check_unique);
        if ($unique_result && mysqli_num_rows($unique_result) > 0) {
            $errors[] = "Already registered on this event.";
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $query = "INSERT INTO registers (user_id, event_id, firstname, lastname, year_level, section, student_number, contact_number, email, terms_accepted) 
                  VALUES ('$user_id', '$event_id', '$first_name', '$last_name', '$year_level', '$section', '$student_number', '$contact_number', '$email', $terms_accepted)";

        if (mysqli_query($con, $query)) {
            $success = true;
            // Clear form data after successful submission
            $year_level = $section = $student_number = $contact_number = $email = '';
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

if (!isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    // Fetch user_id from the database using the username
    $username = $_SESSION['username'];
    $user_result = mysqli_query($con, "SELECT id FROM usertable WHERE username = '$username' LIMIT 1");
    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user_row = mysqli_fetch_assoc($user_result);
        $_SESSION['user_id'] = $user_row['id'];
    }
}
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .form-container {
            max-width: 800px;
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

        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
        }

        .success-message {
            color: #198754;
            margin-bottom: 15px;
        }

        .select2-container--default .select2-selection--single {
            width: 100% !important;
            height: 38px !important;
            padding: 0.375rem 0.75rem !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            font-size: 1rem !important;
            line-height: 1.5 !important;
            box-sizing: border-box;
            background-color: #fff !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-results__option {
            color: #111 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #111 !important;
            line-height: 1.5 !important;
            padding-left: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            top: 50% !important;
            transform: translateY(-50%);
            right: 6px !important;
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
                    Event Registration Form
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
            <h2 class="form-title">Event Registration Form</h2>

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
                        <span class="success-message">Registration Successful! Redirecting to Event Lists...</span>
                    </div>
                    <script>
                        setTimeout(function () {
                            window.location.href = '../events.php';
                        }, 3000);
                    </script>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                            value="<?php echo isset($user_data['firstname']) ? htmlspecialchars($user_data['firstname']) : ''; ?>"
                            readonly disabled>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                            value="<?php echo isset($user_data['lastname']) ? htmlspecialchars($user_data['lastname']) : ''; ?>"
                            readonly disabled>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="student_number" class="form-label">Student Number</label>
                        <input type="text" class="form-control" id="student_number" name="student_number"
                            value="<?php echo isset($student_number) ? htmlspecialchars($student_number) : ''; ?>"
                            required inputmode="numeric" pattern="[0-9]{9}" maxlength="9" minlength="9"
                            autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,9)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="year_level" class="form-label">Year Level</label>
                        <select class="form-select" id="year_level" name="year_level" required>
                            <option value="">Select Year Level</option>
                            <option value="1st Year" <?php echo (isset($year_level) && $year_level == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                            <option value="2nd Year" <?php echo (isset($year_level) && $year_level == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                            <option value="3rd Year" <?php echo (isset($year_level) && $year_level == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                            <option value="4th Year" <?php echo (isset($year_level) && $year_level == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3 section-dropdown-parent">
                        <label for="section" class="form-label">Section</label>
                        <select name="section" class="form-select section-select" id="section" required>
                            <option value="">Select Section</option>
                            <option value="BSBA 102A">BSBA 102A</option>
                            <option value="BSBA 102B">BSBA 102B</option>
                            <option value="BSBA 102C">BSBA 102C</option>
                            <option value="BSBA 102D">BSBA 102D</option>
                            <option value="BSBA 102E">BSBA 102E</option>
                            <option value="BSBA 102F">BSBA 102F</option>
                            <option value="BSBA 102G">BSBA 102G</option>
                            <option value="BSBA 202A">BSBA 202A</option>
                            <option value="BSBA 202B">BSBA 202B</option>
                            <option value="BSBA 202C">BSBA 202C</option>
                            <option value="BSBA 202D">BSBA 202D</option>
                            <option value="BSBA 202E">BSBA 202E</option>
                            <option value="BSBA 202F">BSBA 202F</option>
                            <option value="BSBA 202G">BSBA 202G</option>
                            <option value="BSBM 302A">BSBM 302A</option>
                            <option value="BSBM 302A PETITION_MKTG 80A">BSBM 302A PETITION_MKTG 80A</option>
                            <option value="BSBM 302B">BSBM 302B</option>
                            <option value="BSBM 302B PETITION_MKTG 80A">BSBM 302B PETITION_MKTG 80A</option>
                            <option value="BSBM 302C">BSBM 302C</option>
                            <option value="BSBM 302D">BSBM 302D</option>
                            <option value="BSBM 302E">BSBM 302E</option>
                            <option value="BSBM 302F">BSBM 302F</option>
                            <option value="BSBM 402A">BSBM 402A</option>
                            <option value="BSBM 402B">BSBM 402B</option>
                            <option value="BSBM 402C">BSBM 402C</option>
                            <option value="BSBM 402D">BSBM 402D</option>
                            <option value="BSBM 402E">BSBM 402E</option>
                            <option value="BSBM 402F">BSBM 402F</option>
                            <option value="BSCOS 102A">BSCOS 102A</option>
                            <option value="BSCOS 102B">BSCOS 102B</option>
                            <option value="BSCOS 102C">BSCOS 102C</option>
                            <option value="BSCOS 102D">BSCOS 102D</option>
                            <option value="BSCOS 102E">BSCOS 102E</option>
                            <option value="BSCOS 202A">BSCOS 202A</option>
                            <option value="BSCOS 202B">BSCOS 202B</option>
                            <option value="BSCOS 202C">BSCOS 202C</option>
                            <option value="BSCOS 302A">BSCOS 302A</option>
                            <option value="BSCOS 302B">BSCOS 302B</option>
                            <option value="BSCOS 302C">BSCOS 302C</option>
                            <option value="BSCOS 302D">BSCOS 302D</option>
                            <option value="BSCOS 402A">BSCOS 402A</option>
                            <option value="BSCOS 402B">BSCOS 402B</option>
                            <option value="BSCOS PETITION_COSC 111C">BSCOS PETITION_COSC 111C</option>
                            <option value="BSCOS PETITION_COSC 200A-1">BSCOS PETITION_COSC 200A-1</option>
                            <option value="BSCPE 102 NYT">BSCPE 102 NYT</option>
                            <option value="BSCPE 102A">BSCPE 102A</option>
                            <option value="BSCPE 102B">BSCPE 102B</option>
                            <option value="BSCPE 102C">BSCPE 102C</option>
                            <option value="BSCPE 202A">BSCPE 202A</option>
                            <option value="BSCPE 202B">BSCPE 202B</option>
                            <option value="BSCPE 302 NYT">BSCPE 302 NYT</option>
                            <option value="BSCPE 302A">BSCPE 302A</option>
                            <option value="BSCPE 302B">BSCPE 302B</option>
                            <option value="BSCPE 402A">BSCPE 402A</option>
                            <option value="BSCPE 402A NYT">BSCPE 402A NYT</option>
                            <option value="BSCPE 402B">BSCPE 402B</option>
                            <option value="BSCPE 402B NYT">BSCPE 402B NYT</option>
                            <option value="BSCPE 402C">BSCPE 402C</option>
                            <option value="BSE 102A ENGL">BSE 102A ENGL</option>
                            <option value="BSE 102A MATH">BSE 102A MATH</option>
                            <option value="BSE 102A SCIENCE">BSE 102A SCIENCE</option>
                            <option value="BSE 102B ENGL">BSE 102B ENGL</option>
                            <option value="BSE 102B MATH">BSE 102B MATH</option>
                            <option value="BSE 102B SCIENCE">BSE 102B SCIENCE</option>
                            <option value="BSE 202A ENGL">BSE 202A ENGL</option>
                            <option value="BSE 202A MATH">BSE 202A MATH</option>
                            <option value="BSE 202A SCIENCE">BSE 202A SCIENCE</option>
                            <option value="BSE 202B ENGL">BSE 202B ENGL</option>
                            <option value="BSE 202B MATH">BSE 202B MATH</option>
                            <option value="BSE 202B SCIENCE">BSE 202B SCIENCE</option>
                            <option value="BSE 302 MATH">BSE 302 MATH</option>
                            <option value="BSE 302 SCIENCE">BSE 302 SCIENCE</option>
                            <option value="BSE 302A ENGL">BSE 302A ENGL</option>
                            <option value="BSE 302B ENGL">BSE 302B ENGL</option>
                            <option value="BSE 302C ENGL">BSE 302C ENGL</option>
                            <option value="BSE 402 MATH">BSE 402 MATH</option>
                            <option value="BSE 402 SCIENCE">BSE 402 SCIENCE</option>
                            <option value="BSE 402A ENGL">BSE 402A ENGL</option>
                            <option value="BSE 402B ENGL">BSE 402B ENGL</option>
                            <option value="BSE 402C ENGL">BSE 402C ENGL</option>
                            <option value="BSEE 102A">BSEE 102A</option>
                            <option value="BSEE 102B">BSEE 102B</option>
                            <option value="BSEE 102C">BSEE 102C</option>
                            <option value="BSEE 202A">BSEE 202A</option>
                            <option value="BSEE 202B">BSEE 202B</option>
                            <option value="BSEE 202C">BSEE 202C</option>
                            <option value="BSEE 302A">BSEE 302A</option>
                            <option value="BSEE 302B">BSEE 302B</option>
                            <option value="BSEE 302C">BSEE 302C</option>
                            <option value="BSEE 402A">BSEE 402A</option>
                            <option value="BSEE 402B">BSEE 402B</option>
                            <option value="BSEE 402C">BSEE 402C</option>
                            <option value="BSEE 402D">BSEE 402D</option>
                            <option value="BSHM 102A">BSHM 102A</option>
                            <option value="BSHM 102B">BSHM 102B</option>
                            <option value="BSHM 102C">BSHM 102C</option>
                            <option value="BSHM 102D">BSHM 102D</option>
                            <option value="BSHM 102E">BSHM 102E</option>
                            <option value="BSHM 102F">BSHM 102F</option>
                            <option value="BSHM 202A">BSHM 202A</option>
                            <option value="BSHM 202B">BSHM 202B</option>
                            <option value="BSHM 202C">BSHM 202C</option>
                            <option value="BSHM 202D">BSHM 202D</option>
                            <option value="BSHM 202E">BSHM 202E</option>
                            <option value="BSHM 202F">BSHM 202F</option>
                            <option value="BSHM 302A">BSHM 302A</option>
                            <option value="BSHM 302B">BSHM 302B</option>
                            <option value="BSHM 302C">BSHM 302C</option>
                            <option value="BSHM 302D">BSHM 302D</option>
                            <option value="BSHM 302E">BSHM 302E</option>
                            <option value="BSHM 402A">BSHM 402A</option>
                            <option value="BSHM 402B">BSHM 402B</option>
                            <option value="BSHM 402C">BSHM 402C</option>
                            <option value="BSHM 402D">BSHM 402D</option>
                            <option value="BSHM 402E">BSHM 402E</option>
                            <option value="BSINFOTECH 102A">BSINFOTECH 102A</option>
                            <option value="BSINFOTECH 102B">BSINFOTECH 102B</option>
                            <option value="BSINFOTECH 102C">BSINFOTECH 102C</option>
                            <option value="BSINFOTECH 102D">BSINFOTECH 102D</option>
                            <option value="BSINFOTECH 102E">BSINFOTECH 102E</option>
                            <option value="BSINFOTECH 202A">BSINFOTECH 202A</option>
                            <option value="BSINFOTECH 202B">BSINFOTECH 202B</option>
                            <option value="BSINFOTECH 202C">BSINFOTECH 202C</option>
                            <option value="BSINFOTECH 302A">BSINFOTECH 302A</option>
                            <option value="BSINFOTECH 302B">BSINFOTECH 302B</option>
                            <option value="BSINFOTECH 302C">BSINFOTECH 302C</option>
                            <option value="BSINFOTECH 302D">BSINFOTECH 302D</option>
                            <option value="BSINFOTECH 302E">BSINFOTECH 302E</option>
                            <option value="BSINFOTECH 402A">BSINFOTECH 402A</option>
                            <option value="BSINFOTECH 402B">BSINFOTECH 402B</option>
                            <option value="BSINFOTECH 402C">BSINFOTECH 402C</option>
                            <option value="BSINFOTECH 402D">BSINFOTECH 402D</option>
                            <option value="BSINFOTECH 402E">BSINFOTECH 402E</option>
                            <option value="BSINFOTECH PETITION_DCIT 65A">BSINFOTECH PETITION_DCIT 65A</option>
                            <option value="BSINFOTECH PETITION_ITEC 106A">BSINFOTECH PETITION_ITEC 106A</option>
                            <option value="BSINFOTECH PETITION_ITEC 111A">BSINFOTECH PETITION_ITEC 111A</option>
                            <option value="BSINFOTECH PETITION_ITEC 95">BSINFOTECH PETITION_ITEC 95</option>
                            <option value="BSIT 102 FAT">BSIT 102 FAT</option>
                            <option value="BSIT 102 HVAC-R">BSIT 102 HVAC-R</option>
                            <option value="BSIT 102 SMTE">BSIT 102 SMTE</option>
                            <option value="BSIT 102A AUTO">BSIT 102A AUTO</option>
                            <option value="BSIT 102A DRAF">BSIT 102A DRAF</option>
                            <option value="BSIT 102A ELEC">BSIT 102A ELEC</option>
                            <option value="BSIT 102A ELEX">BSIT 102A ELEX</option>
                            <option value="BSIT 102A MECH">BSIT 102A MECH</option>
                            <option value="BSIT 102A WAFT">BSIT 102A WAFT</option>
                            <option value="BSIT 102B AUTO">BSIT 102B AUTO</option>
                            <option value="BSIT 102B DRAF">BSIT 102B DRAF</option>
                            <option value="BSIT 102B ELEC">BSIT 102B ELEC</option>
                            <option value="BSIT 102B ELEX">BSIT 102B ELEX</option>
                            <option value="BSIT 102B MECH">BSIT 102B MECH</option>
                            <option value="BSIT 102B WAFT">BSIT 102B WAFT</option>
                            <option value="BSIT 102C AUTO">BSIT 102C AUTO</option>
                            <option value="BSIT 102C DRAF">BSIT 102C DRAF</option>
                            <option value="BSIT 102C ELEC">BSIT 102C ELEC</option>
                            <option value="BSIT 102C ELEX">BSIT 102C ELEX</option>
                            <option value="BSIT 202 FAT">BSIT 202 FAT</option>
                            <option value="BSIT 202 HVAC-R">BSIT 202 HVAC-R</option>
                            <option value="BSIT 202 MECH">BSIT 202 MECH</option>
                            <option value="BSIT 202A AUTO">BSIT 202A AUTO</option>
                            <option value="BSIT 202A DRAF">BSIT 202A DRAF</option>
                            <option value="BSIT 202A ELEC">BSIT 202A ELEC</option>
                            <option value="BSIT 202A ELEX">BSIT 202A ELEX</option>
                            <option value="BSIT 202A SMTE">BSIT 202A SMTE</option>
                            <option value="BSIT 202A WAFT">BSIT 202A WAFT</option>
                            <option value="BSIT 202B AUTO">BSIT 202B AUTO</option>
                            <option value="BSIT 202B DRAF">BSIT 202B DRAF</option>
                            <option value="BSIT 202B ELEC">BSIT 202B ELEC</option>
                            <option value="BSIT 202B ELEX">BSIT 202B ELEX</option>
                            <option value="BSIT 202B SMTE">BSIT 202B SMTE</option>
                            <option value="BSIT 202B WAFT">BSIT 202B WAFT</option>
                            <option value="BSIT 202C AUTO">BSIT 202C AUTO</option>
                            <option value="BSIT 202C DRAF">BSIT 202C DRAF</option>
                            <option value="BSIT 202C ELEC">BSIT 202C ELEC</option>
                            <option value="BSIT 202C ELEX">BSIT 202C ELEX</option>
                            <option value="BSIT 302 AUTO_2021">BSIT 302 AUTO_2021</option>
                            <option value="BSIT 302 DRAF_2021">BSIT 302 DRAF_2021</option>
                            <option value="BSIT 302 ELEC_2021">BSIT 302 ELEC_2021</option>
                            <option value="BSIT 302 ELEX_2021">BSIT 302 ELEX_2021</option>
                            <option value="BSIT 302 FAT_2021">BSIT 302 FAT_2021</option>
                            <option value="BSIT 302 FAT_2022">BSIT 302 FAT_2022</option>
                            <option value="BSIT 302 HVAC-R_2022">BSIT 302 HVAC-R_2022</option>
                            <option value="BSIT 302 MECH_2021">BSIT 302 MECH_2021</option>
                            <option value="BSIT 302 MECH_2022">BSIT 302 MECH_2022</option>
                            <option value="BSIT 302 SMTE_2021">BSIT 302 SMTE_2021</option>
                            <option value="BSIT 302 SMTE_2022">BSIT 302 SMTE_2022</option>
                            <option value="BSIT 302 WAFT_2021">BSIT 302 WAFT_2021</option>
                            <option value="BSIT 302 WAFT_2022">BSIT 302 WAFT_2022</option>
                            <option value="BSIT 302A AUTO_2022">BSIT 302A AUTO_2022</option>
                            <option value="BSIT 302A DRAF_2022">BSIT 302A DRAF_2022</option>
                            <option value="BSIT 302A ELEC_2022">BSIT 302A ELEC_2022</option>
                            <option value="BSIT 302A ELEX_2022">BSIT 302A ELEX_2022</option>
                            <option value="BSIT 302B AUTO_2022">BSIT 302B AUTO_2022</option>
                            <option value="BSIT 302B DRAF_2022">BSIT 302B DRAF_2022</option>
                            <option value="BSIT 302B ELEC_2022">BSIT 302B ELEC_2022</option>
                            <option value="BSIT 302B ELEX_2022">BSIT 302B ELEX_2022</option>
                            <option value="BSIT 402 AUTO_2020">BSIT 402 AUTO_2020</option>
                            <option value="BSIT 402 DRAF_2020">BSIT 402 DRAF_2020</option>
                            <option value="BSIT 402 ELEC_2020">BSIT 402 ELEC_2020</option>
                            <option value="BSIT 402 ELEX_2019">BSIT 402 ELEX_2019</option>
                            <option value="BSIT 402 ELEX_2020">BSIT 402 ELEX_2020</option>
                            <option value="BSIT 402 FAT_2019">BSIT 402 FAT_2019</option>
                            <option value="BSIT 402 FAT_2020">BSIT 402 FAT_2020</option>
                            <option value="BSIT 402 HVAC-R_2019">BSIT 402 HVAC-R_2019</option>
                            <option value="BSIT 402 MECH_2019">BSIT 402 MECH_2019</option>
                            <option value="BSIT 402 MECH_2020">BSIT 402 MECH_2020</option>
                            <option value="BSIT 402 MECH_JAPAN">BSIT 402 MECH_JAPAN</option>
                            <option value="BSIT 402 SMTE_2019">BSIT 402 SMTE_2019</option>
                            <option value="BSIT 402 SMTE_2020">BSIT 402 SMTE_2020</option>
                            <option value="BSIT 402 WAFT_2019">BSIT 402 WAFT_2019</option>
                            <option value="BSIT 402 WAFT_2020">BSIT 402 WAFT_2020</option>
                            <option value="BSIT 402 WAFT_JAPAN">BSIT 402 WAFT_JAPAN</option>
                            <option value="BSIT 402A AUTO_2019">BSIT 402A AUTO_2019</option>
                            <option value="BSIT 402A DRAF_2019">BSIT 402A DRAF_2019</option>
                            <option value="BSIT 402A ELEC_2019">BSIT 402A ELEC_2019</option>
                            <option value="BSIT 402B AUTO_2019">BSIT 402B AUTO_2019</option>
                            <option value="BSIT 402B DRAF_2019">BSIT 402B DRAF_2019</option>
                            <option value="BSIT 402B ELEC_2019">BSIT 402B ELEC_2019</option>
                            <option value="BSIT PETITION_ELEX 199C">BSIT PETITION_ELEX 199C</option>
                            <option value="BSIT PETITION_SMTE 199C">BSIT PETITION_SMTE 199C</option>
                            <option value="BTVTED 102A">BTVTED 102A</option>
                            <option value="BTVTED 102B">BTVTED 102B</option>
                            <option value="BTVTED 202 AUTO">BTVTED 202 AUTO</option>
                            <option value="BTVTED 202 FSM">BTVTED 202 FSM</option>
                            <option value="BTVTED 302A FSM">BTVTED 302A FSM</option>
                            <option value="BTVTED 302B AUTO">BTVTED 302B AUTO</option>
                            <option value="BTVTED 302B ELEC">BTVTED 302B ELEC</option>
                            <option value="BTVTED 302B ELEX">BTVTED 302B ELEX</option>
                            <option value="BTVTED 302B GFD">BTVTED 302B GFD</option>
                            <option value="BTVTED 402A FSM">BTVTED 402A FSM</option>
                            <option value="BTVTED 402B AUTO">BTVTED 402B AUTO</option>
                            <option value="BTVTED 402B ELEC">BTVTED 402B ELEC</option>
                            <option value="BTVTED 402B GFD">BTVTED 402B GFD</option>
                            <option value="TCP A">TCP A</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" id="contact_number" name="contact_number"
                            value="<?php echo isset($contact_number) ? htmlspecialchars($contact_number) : ''; ?>"
                            required inputmode="numeric" pattern="[0-9]{7,11}" maxlength="11" minlength="7"
                            autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="event" class="form-label">Event</label>
                        <select class="form-select event-select" id="event" name="event" required>
                            <option value="">Select Event</option>
                            <?php
                            // Get active events from database
                            $current_date = new DateTime();
                            $query = "SELECT id, event_title FROM create_events 
                                    WHERE (status = 'active' OR status IS NULL) 
                                    AND date_time > NOW() 
                                    ORDER BY date_time ASC";
                            $result = mysqli_query($con, $query);

                            // Get event title from URL parameter
                            $selected_event = isset($_GET['event_title']) ? $_GET['event_title'] : '';

                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($row['event_title'] === $selected_event) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($row['event_title']) . '" ' . $selected . '>' .
                                    htmlspecialchars($row['event_title']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                    <label class="form-check-label text-black" for="terms">
                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and
                            conditions</a>
                    </label>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    </div>

    <script>
        $(document).ready(function () {
            $('.section-select').select2({
                width: 'resolve',
                placeholder: 'Select Section',
                allowClear: true,
                dropdownParent: $('.section-dropdown-parent')
            });

            $('.event-select').select2({
                width: 'resolve',
                placeholder: 'Select Event',
                allowClear: true
            });
        });
    </script>

    <!-- Add Terms and Conditions Modal -->
    <?php include 'terms.php'; ?>

</body>

</html>