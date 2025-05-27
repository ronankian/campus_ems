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

        body {
            color: var(--text-main) !important;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: var(--primary) !important;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .accent {
            color: var(--accent) !important;
            backdrop-filter: brightness(0.8);
        }

        .btn-primary,
        .btn-outline-primary {
            background: var(--gradient-start) !important;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%) !important;
            border: none !important;
            color: var(--text-dark) !important;
            font-weight: 700;
        }

        .btn-primary:hover,
        .btn-outline-primary:hover {
            background: var(--accent) !important;
            color: var(--text-dark) !important;
        }

        .btn-accent {
            background-color: var(--accent) !important;
            border: none !important;
            color: var(--text-dark) !important;
            font-weight: 700;
        }

        .btn-accent:hover {
            background: var(--gradient-end) !important;
            color: var(--text-dark) !important;
        }

        .card,
        .surface {
            background: rgba(43, 45, 66, 0.85) !important;
            color: var(--text-main) !important;
        }

        a {
            color: var(--gradient-start);
            transition: color 0.2s;
        }

        a:hover {
            color: var(--gradient-end);
        }

        .secondary-text {
            color: var(--accent) !important;
        }

        .landing-glass {
            background: rgba(43, 45, 66, 0.75);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(44, 45, 66, 0.25);
            backdrop-filter: blur(12px);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            padding: 2.5rem 2.5rem 2rem 2.5rem;
            max-width: 540px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .landing-title {
            font-family: 'Poppins', sans-serif;
            font-size: 3.2rem;
            font-weight: 900;
            letter-spacing: 2.5px;
            margin-bottom: 1.2rem;
            background: linear-gradient(90deg, #ff3cac 0%, #38f9d7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow:
                0 0 24px #ff3cac,
                0 0 32px #38f9d7,
                0 4px 24px rgba(0, 0, 0, 0.25);
            animation: gradient-move 4s ease-in-out infinite alternate;
        }

        @keyframes gradient-move {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 100% 50%;
            }
        }

        .landing-subtitle {
            font-family: 'Lobster', cursive;
            font-size: 1.5rem;
            font-weight: 400;
            font-style: italic;
            margin-bottom: 2.5rem;
            color: #fff;
            text-shadow: 0 0 12px #ff3cac, 0 2px 12px rgba(0, 0, 0, 0.18);
        }

        .landing-search-form input[type=\"text\"] {
            border-radius: 32px;
            font-size: 1.1rem;
            padding: 0.75rem 1.25rem;
            border: none;
            outline: none;
            box-shadow: 0 2px 8px rgba(44, 45, 66, 0.08);
        }

        .landing-search-form .search-btn {
            border-radius: 32px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 0.75rem 2.5rem;
            margin-left: 1rem;
            background: linear-gradient(90deg, #784ba0 0%, #ffb347 100%);
            color: #2b2d42;
            border: none;
            box-shadow: 0 2px 8px rgba(44, 45, 66, 0.08);
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .landing-search-form .search-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 16px #ff3cac55;
            color: #fff;
        }

        .landing-browse-btn {
            margin-top: 2.5rem;
            font-weight: 800;
            letter-spacing: 1px;
            font-size: 1.35rem;
            border-radius: 16px;
            background: linear-gradient(90deg, #ffb347 0%, #2b2d42 100%);
            color: #fff;
            padding: 0.9rem 2.5rem;
            border: none;
            box-shadow: 0 2px 16px #ffb34755;
            transition: background 0.2s, color 0.2s, transform 0.15s;
        }

        .landing-browse-btn:hover {
            background: linear-gradient(90deg, #ff3cac 0%, #38f9d7 100%);
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 4px 24px #ff3cac55;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>
    <?php include 'bg-image.php'; ?>
    <!-- Your home page content here -->
    <div class="container mt-5 d-flex flex-column justify-content-center align-items-center"
        style="max-width: 70%; min-height: 60vh; margin-left: auto; margin-right: auto; text-align: center;">
        <h1 style="font-family: 'Poppins', sans-serif; font-size: 3rem; font-weight: 900; width: 80%; letter-spacing: 2px; margin-bottom: 1rem;"
            class="mb-1">Your Central Hub for All Campus Events</h1>
        <h4 class="accent"
            style="font-family: 'Lobster', cursive; font-size: 1.5rem; font-weight: 500; margin-bottom: 2.5rem;">Stay
            connected and never miss out—manage, find, and join campus events with ease.</h4>
        <div class="surface"
            style="border-radius: 18px; box-shadow: 0 2px 16px rgba(0,0,0,0.18); padding: 2rem 2.5rem; max-width: 500px; width: 100%; display: flex; flex-direction: column; align-items: center;">
            <form class="w-100 d-flex" action="/campus_ems/search.php" method="get" onsubmit="return true;">
                <input class="form-control me-2" type="text" name="keyword" placeholder="Search events..."
                    autocomplete="off" required style="border-radius: 8px; font-size: 1.1rem;">
                <button class="btn btn-primary px-4" type="submit"
                    style="font-weight: 600; letter-spacing: 1px; border-radius: 8px;">Search</button>
            </form>
        </div>
        <a href="/campus_ems/events.php" class="mt-4 d-inline-block btn btn-accent px-4 py-2"
            style="font-weight: 700; letter-spacing: 1px; font-size: 1.35rem; border-radius: 8px;">Browse Events</a>
    </div>
</body>

</html>