<?php
session_start();
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
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Dancing+Script:wght@700&family=Caveat:wght@700&family=Lobster&display=swap"
        rel="stylesheet">
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

        .landing-title {
            font-family: 'Poppins', sans-serif;
            font-size: 3rem;
            font-weight: 900;
            width: 70%;
            letter-spacing: 2px;
            color: #fff;
            text-shadow:
                0 0 12px var(--primary),

                0 0 32px var(--gradient-end),

                0 4px 24px rgba(0, 0, 0, 0.25);
        }

        .landing-subtitle {
            font-family: 'Lobster', cursive;
            font-size: 1.5rem;
            font-weight: 500;
            color: var(--accent);
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.18);
        }

        .landing-glass {
            background: rgba(240, 240, 240, 0.1);
            border-radius: 18px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.18);
            padding: 2rem 2.5rem;
            max-width: 700px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .landing-search-btn {
            font-weight: 600;
            letter-spacing: 1px;
            border-radius: 8px;
        }

        .landing-browse-link {
            display: inline-block;
            margin-top: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
            font-size: 1.35rem;
            color: #fff;
            text-decoration: none;
            text-shadow:
                0 0 12px var(--primary),
                0 0 32px var(--gradient-start),
                0 4px 24px rgba(0, 0, 0, 0.25);
            transition: text-shadow 0.2s, color 0.2s;
        }

        .landing-browse-link:hover {
            color: var(--gradient-end);
            text-shadow: 0 0 12px var(--primary), 0 0 32px var(--gradient-end), 0 2px 12px rgba(0, 0, 0, 0.18);
        }

        .btn-search {
            position: relative;
            background: #fff;
            border: 2px solid transparent;
            z-index: 1;
            border-radius: 50px;
            overflow: hidden;
        }

        .btn-search::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            border-radius: 50px;
            padding: 2px;
            /* border thickness */
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            -webkit-mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }

        .form-control::placeholder,
        .form-select::placeholder {
            color: #fff !important;
            opacity: 1;
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

        .btn-search:hover,
        .btn-search:focus {
            border-radius: 50px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            color: #fff;
            border-color: transparent;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>
    <?php include 'bg-image.php'; ?>
    <!-- Your home page content here -->
    <div class="container mt-5 d-flex flex-column justify-content-center align-items-center text-center">
        <h1 class="landing-title mb-3">Your Central Hub for All Campus Events</h1>
        <h4 class="landing-subtitle mb-3">Stay connected and never miss out—manage, find, and join campus events with
            ease.
        </h4>
        <div class="landing-glass rounded-pill mb-4">
            <form class="w-100 row gx-2 align-items-center justify-content-center position-relative"
                action="/campus_ems/search.php" method="get" onsubmit="return true;">
                <div class="col-6 position-relative">
                    <i class="fa fa-magnifying-glass position-absolute"
                        style="left:18px;top:50%;transform:translateY(-50%);color:#fff;opacity:0.7;font-size:1.1rem;z-index:2;"></i>
                    <input class="form-control rounded-pill ps-5" type="text" name="keyword"
                        placeholder="Search events..." autocomplete="off">
                </div>
                <div class="col-auto">
                    <select class="form-select rounded-pill" name="category" style="min-width: 150px;">
                        <option value="">All Categories</option>
                        <option value="Seminar">Seminar</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Conference">Conference</option>
                        <option value="Sports">Sports</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Celebration">Celebration</option>
                        <option value="Competition">Competition</option>
                        <option value="Training">Training</option>
                        <option value="Webinar">Webinar</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-search px-4 landing-search-btn text-white" type="submit">Search</button>
                </div>
            </form>
        </div>
        <a href="/campus_ems/events.php" class="landing-browse-link">Browse Events</a>
    </div>
</body>

</html>