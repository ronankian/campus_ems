<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "campus_ems");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test if we can query the database
$test_query = "SELECT 1 FROM create_events LIMIT 1";
if (!mysqli_query($con, $test_query)) {
    die("Error accessing create_events table: " . mysqli_error($con));
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        h3,
        h5,
        p,
        small,
        .text-dark,
        .text-secondary {
            color: #fff !important;
        }

        .timeline {
            position: relative;
            margin: 0 auto;
            padding: 2rem 0;
            max-width: 900px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            transform: translateX(-50%);
            width: 4px;
            height: 100%;
            background: #fff;
            border-radius: 2px;
        }

        .timeline-event {
            position: relative;
            width: 50%;
            padding: 0 40px;
            box-sizing: border-box;
            margin-bottom: 60px;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .timeline-event.left {
            left: 0;
            justify-content: flex-end;
            text-align: right;
        }

        .timeline-event.right {
            left: 50%;
            justify-content: flex-start;
            text-align: left;
        }

        .timeline-dot {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            background: #fff;
            border-radius: 50%;
            border: 4px solid #FEC53A;
            z-index: 1;
        }

        .timeline-event.left .timeline-dot {
            right: -12px;
        }

        .timeline-event.right .timeline-dot {
            left: -12px;
        }

        .timeline-box-link {
            display: block;
            width: 100%;
        }

        .timeline-arrow-box {
            background: rgba(30, 30, 30, 0.85);
            border-radius: 8px;
            padding: 1rem 1.5rem 1.2rem 1.5rem;
            color: #fff;
            max-width: 320px;
            min-width: 220px;
            width: 100%;
            min-height: 180px;
            box-shadow: 0 2px 8px #0002;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            position: relative;
            margin-left: auto;
            margin-right: auto;
        }

        .timeline-arrow-box.left::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-top: 18px solid transparent;
            border-bottom: 18px solid transparent;
            border-left: 24px solid rgba(30, 30, 30, 0.85);
        }

        .timeline-arrow-box.right::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 100%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-top: 18px solid transparent;
            border-bottom: 18px solid transparent;
            border-right: 24px solid rgba(30, 30, 30, 0.85);
        }

        .timeline-title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 0.7rem;
            text-align: center;
        }

        .timeline-img-box {
            width: 100%;
            height: 120px;
            background: #888;
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.7rem;
            position: relative;
        }

        .no-image-box {
            width: 100%;
            height: 100%;
            background: #888;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .no-image-watermark {
            color: #fff;
            opacity: 0.5;
            font-size: 1.1rem;
            font-weight: bold;
            text-align: center;
            pointer-events: none;
            user-select: none;
        }

        .timeline-date-box {
            margin-top: 0.7rem;
            font-weight: 500;
            font-size: 1.05rem;
            color: #FEC53A;
            text-align: center;
            width: 100%;
        }

        @media (max-width: 900px) {
            .timeline {
                max-width: 100%;
            }

            .timeline-arrow-box {
                max-width: 100%;
            }
        }

        @media (max-width: 600px) {
            .timeline {
                padding-left: 0;
                max-width: 100%;
            }

            .timeline-event {
                width: 100%;
                left: 0 !important;
                text-align: left !important;
                padding: 0 20px 0 60px;
            }

            .timeline-dot {
                left: 18px !important;
                right: auto !important;
            }

            .timeline-arrow-box {
                max-width: 100%;
                min-width: 0;
            }
        }
    </style>
</head>

<body>


    <?php include 'navbar.php'; ?>
    <?php include 'bg-image.php'; ?>
    <div class="container">
        <div class="row">

            <div class="col-md-12">
                <style>
                    .timeline-view-all-link:hover,
                    .timeline-view-all-link:focus {
                        color: #FEC53A !important;
                    }
                </style>
                <div class="col-md-12 d-flex justify-content-between align-items-end border-bottom">
                    <h3 class="pb-2 fst-italic">Events Timeline</h3>
                    <a href="events.php"
                        class="btn btn-link text-decoration-none text-white fw-bold d-flex align-items-center timeline-view-all-link"
                        style="font-size: 1.1rem;">
                        View all events
                        <i class="fa fa-caret-right ms-2"></i>
                    </a>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="timeline">
                        <?php
                        $query = "SELECT id, event_title, date_time, attach_file FROM create_events ORDER BY date_time ASC";
                        $result = mysqli_query($con, $query);
                        if (mysqli_num_rows($result) === 0): ?>
                            <div class="text-white-50 py-5">No events found.</div>
                        <?php else:
                            $events = [];
                            while ($row = mysqli_fetch_assoc($result)) {
                                $events[] = $row;
                            }
                            // Sort so nearest date_time is at the top
                            usort($events, function ($a, $b) {
                                return strtotime($a['date_time']) <=> strtotime($b['date_time']);
                            });
                            foreach ($events as $i => $event):
                                $side = ($i % 2 === 0) ? 'left' : 'right';
                                $img = null;
                                if (!empty($event['attach_file'])) {
                                    $files = json_decode($event['attach_file'], true);
                                    if (is_array($files) && count($files) > 0) {
                                        foreach ($files as $file) {
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                                $img = 'uploads/' . $file;
                                                break;
                                            }
                                        }
                                    }
                                }
                                ?>
                                <div class="timeline-event <?php echo $side; ?>">
                                    <div class="timeline-dot"></div>
                                    <a href="event-details.php?id=<?php echo $event['id']; ?>" class="timeline-box-link"
                                        style="text-decoration:none; color:inherit; width:100%;">
                                        <div class="timeline-arrow-box <?php echo $side; ?>">
                                            <div class="timeline-title">
                                                <?php echo htmlspecialchars($event['event_title']); ?>
                                            </div>
                                            <div class="timeline-img-box">
                                                <?php if ($img): ?>
                                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Event image"
                                                        style="width:100%; height:100%; object-fit:cover; border-radius:6px;">
                                                <?php else: ?>
                                                    <div class="no-image-box">
                                                        <span class="no-image-watermark">No Image Found</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="timeline-date-box">
                                        <?php echo date('F d, Y', strtotime($event['date_time'])); ?>
                                    </div>
                                </div>
                            <?php endforeach;
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    </div>
</body>

</html>