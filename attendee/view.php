<?php
session_start();
$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$reg_id = isset($_GET['reg_id']) ? intval($_GET['reg_id']) : 0;
$registration = null;
$event = null;
if ($reg_id > 0) {
    $query = "SELECT r.*, e.event_title, e.date_time, e.location FROM registers r JOIN create_events e ON r.event_id = e.id WHERE r.id = '$reg_id'";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $registration = mysqli_fetch_assoc($result);
    }
}
if (!$registration) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Registration not found.</div></div>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registration Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            background: rgba(43, 45, 66, 0.3) !important;
            backdrop-filter: blur(10px) !important;
            color: #fff !important;
            border: none !important;
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
        }

        label.form-label {
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

    <div class="container mt-2">
        <div class="form-container">
            <h2 class="form-title">View Registration Details</h2>
            <div class="mb-3 text-center">
                <span class="badge bg-info text-dark fs-6">Registered on:
                    <?php echo date('M d, Y | h:i A', strtotime($registration['registration_date'])); ?></span>
            </div>
            <form>
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                            value="<?php echo htmlspecialchars($registration['firstname']); ?>" disabled>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                            value="<?php echo htmlspecialchars($registration['lastname']); ?>" disabled>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="student_number" class="form-label">Student Number</label>
                        <input type="text" class="form-control" id="student_number" name="student_number"
                            value="<?php echo htmlspecialchars($registration['student_number']); ?>" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="year_level" class="form-label">Year Level</label>
                        <input type="text" class="form-control" id="year_level" name="year_level"
                            value="<?php echo htmlspecialchars($registration['year_level']); ?>" disabled>
                    </div>
                    <div class="col-md-4 mb-3 section-dropdown-parent">
                        <label for="section" class="form-label">Section</label>
                        <input type="text" class="form-control" id="section" name="section"
                            value="<?php echo htmlspecialchars($registration['section']); ?>" disabled>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number"
                            value="<?php echo htmlspecialchars($registration['contact_number']); ?>" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($registration['email']); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="event" class="form-label">Event</label>
                        <input type="text" class="form-control" id="event" name="event"
                            value="<?php echo htmlspecialchars($registration['event_title']); ?>" disabled>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" name="terms" <?php echo ($registration['terms_accepted'] ? 'checked' : ''); ?> disabled>
                    <label class="form-check-label text-white" for="terms">I agree to the terms and conditions</label>
                </div>
                <div class="text-center mt-4">
                    <a href="eventlists.php" class="btn btn-secondary">Go Back</a>
                </div>
            </form>
        </div>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>