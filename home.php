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
</head>

<body>

    <?php include 'navbar.php'; ?>
    <?php include 'bg-image.php'; ?>
    <!-- Your home page content here -->
    <div class="container mt-5 d-flex flex-column justify-content-center align-items-center"
        style="max-width: 70%; min-height: 60vh; margin-left: auto; margin-right: auto; text-align: center;">
        <h1 style="font-family: 'Poppins', sans-serif; font-size: 3rem; font-weight: 900; width: 80%; letter-spacing: 2px; margin-bottom: 1rem; color: #fff; 
            text-shadow:
                0 0 12px #fec53a, /* warm gold glow */
                0 0 32px #e48a14, /* orange glow */
                0 4px 24px rgba(0,0,0,0.25); /* subtle shadow for depth */
            ">
            Your Central Hub for All Campus Events
        </h1>
        <h4
            style="font-family: 'Lobster', cursive; font-size: 1.5rem; font-weight: 500;     color: #FEC53A; margin-bottom: 2.5rem; text-shadow: 0 2px 12px rgba(0,0,0,0.18);">
            Stay connected and never miss out—manage, find, and join campus events with ease.
        </h4>
        <div
            style="background: rgba(30, 30, 30, 0.7); border-radius: 18px; box-shadow: 0 2px 16px rgba(0,0,0,0.18); padding: 2rem 2.5rem; max-width: 500px; width: 100%; display: flex; flex-direction: column; align-items: center;">
            <form class="w-100 d-flex" action="/campus_ems/search.php" method="get" onsubmit="return true;">
                <input class="form-control me-2" type="text" name="keyword" placeholder="Search events..."
                    autocomplete="off" required style="border-radius: 8px; font-size: 1.1rem;">
                <button class="btn btn-outline-warning px-4" type="submit"
                    style="font-weight: 600; letter-spacing: 1px; border-radius: 8px;">Search</button>
            </form>
        </div>
        <a href="/campus_ems/events.php" style="
                display: inline-block;
                margin-top: 2rem;
                font-weight: 700;
                letter-spacing: 1px;
                font-size: 1.35rem;
                color: #fff;
                text-decoration: none;
                text-shadow:
                    0 0 24px #fec53a,
                    0 0 48px #e48a14,
                    0 4px 24px rgba(0,0,0,0.25);
                transition: text-shadow 0.2s, color 0.2s;
            "
            onmouseover="this.style.color='#fec53a';this.style.textShadow='0 0 12px #fec53a,0 0 32px #e48a14,0 2px 12px rgba(0,0,0,0.18)';"
            onmouseout="this.style.color='#fff';this.style.textShadow='0 0 24px #fec53a,0 0 48px #e48a14,0 4px 24px rgba(0,0,0,0.25)';">Browse
            Events</a>
    </div>
</body>

</html>