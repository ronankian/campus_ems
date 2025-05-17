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
        body {
            background: #f5f6fa;
        }

        .card-summary {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .card-summary .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
    </style>

</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../bg-image.php'; ?>


    <div class="container">

        <div class="row">

            <?php include 'sidebar.php'; ?>




        </div>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>