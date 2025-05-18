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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">

    <style>
        .navbar-logo-text {
            color: white;
        }

        .navbar-logo-text:hover {
            background: linear-gradient(to bottom, rgb(223, 7, 7), rgb(223, 202, 15));
            background-clip: text;
            color: transparent;
        }

        .navbar1,
        .navbar2,
        .offcanvas {
            background: linear-gradient(rgba(26, 34, 40, 1), rgba(0, 0, 0, 0.3)) !important;
            box-shadow: none !important;
            padding: 0.5rem 0;
        }

        .navbar1 .container,
        .navbar2 .container {
            max-width: 1200px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-left: 1rem;
            padding-right: 1rem;
            margin-left: auto;
            margin-right: auto;
        }

        .navbar-logo {
            font-family: 'Montserrat', 'Arial', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: #333;
            letter-spacing: -1px;
            margin-right: 2rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            flex-shrink: 0;
            max-width: 250px;
            white-space: nowrap;
        }

        .navbar-logo img {
            margin-right: 0.5rem;
            max-width: 2.5rem;
            max-height: 2rem;
            width: 100%;
            height: auto;
        }

        .navbar-center {
            flex: 1 1 0%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 2rem;
            min-width: 0;
        }

        .nav,
        .offcanvas-body .nav {
            display: flex;
            flex-wrap: nowrap;
            gap: 2.5rem;
            margin: 0;
            padding: 0;
        }

        .nav-link {
            color: white !important;
            font-family: 'Montserrat', 'Arial', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            background: none;
            border: none;
            padding: 0.5rem 0;
            transition: color 0.2s;
            position: relative;
            white-space: nowrap;
        }

        .nav-link:hover,
        .nav-link.active {
            color: rgb(228, 138, 20) !important;
        }

        .nav-link::after {
            content: '';
            display: block;
            margin: 0 auto;
            width: 0;
            height: 2px;
            background: rgb(228, 138, 20);
            transition: width 0.2s;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 60%;
        }

        .navbar-icons {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            flex-shrink: 0;
        }

        .navbar-icons i,
        .navbar-icons .fa {
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s;
            margin-right: 0.7rem;
        }

        .navbar-icons i:hover,
        .navbar-icons .fa:hover {
            color: rgb(228, 138, 20);
        }

        .navbar-icons i:last-child,
        .navbar-icons .fa:last-child {
            margin-right: 0;
        }

        .offcanvas {
            background: rgb(50, 50, 50) !important;
            color: white;
        }

        .offcanvas-header .navbar-logo {
            font-size: 1.5rem;
        }

        .offcanvas .nav {
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        .offcanvas .nav-link {
            color: white !important;
            font-size: 1.1rem;
            padding: 0.4rem 0;
        }

        .offcanvas .nav-link:hover,
        .offcanvas .nav-link.active {
            color: rgb(228, 138, 20) !important;
        }

        .offcanvas .nav-link::after {
            background: rgb(228, 138, 20);
        }

        .offcanvas-btn {
            background: none;
            border: 1px solid #bbb;
            color: white;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }

        .offcanvas-btn:hover {
            border-color: rgb(228, 138, 20);
            color: rgb(228, 138, 20);
        }

        .btn-close {
            background: #eee;
        }

        @media (max-width: 991px) {
            .navbar1 {
                display: none !important;
            }

            .navbar2 {
                display: flex !important;
            }

            .navbar-logo {
                font-size: 1.2rem;
                max-width: 150px;
            }

            .navbar-logo img {
                max-width: 2rem;
                max-height: 2rem;
            }

            .navbar-icons i,
            .navbar-icons .fa {
                font-size: 1.2rem;
                margin-right: 0.5rem;
            }
        }

        @media (min-width: 992px) {
            .navbar2 {
                display: none !important;
            }

            .navbar1 {
                display: flex !important;
            }
        }

        @media (max-width: 600px) {

            .navbar2 .container,
            .navbar1 .container {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .navbar-logo {
                font-size: 1.2rem;
            }

            .nav-link {
                font-size: 0.9rem;
            }
        }

        .offcanvas-end {
            width: 320px !important;
            max-width: 90vw;
        }

        @media (max-width: 400px) {
            .offcanvas-end {
                width: 100vw !important;
            }

            .navbar-logo {
                font-size: 0.8rem;
                max-width: 80px;
            }

            .navbar-logo img {
                max-width: 1rem;
                max-height: 1rem;
            }

            .nav-link {
                font-size: 0.7rem !important;
                padding: 0.2rem 0.3rem;
            }

            .navbar-icons i,
            .navbar-icons .fa {
                font-size: 0.8rem;
                margin-right: 0.3rem;
            }

            .navbar-icons {
                gap: 0.3rem;
            }

            .container {
                padding-left: 0.2rem !important;
                padding-right: 0.2rem !important;
            }

            .navbar1,
            .navbar2,
            .offcanvas {
                padding-top: 0.2rem !important;
                padding-bottom: 0.2rem !important;
            }
        }

        .dropdown-menu {
            background: #222;
            border: none;
            min-width: 140px;
        }

        .dropdown-item {
            color: #fff;
            font-family: 'Montserrat', Arial, sans-serif;
            font-size: 0.97rem;
            padding: 0.5rem 1rem;
            transition: background 0.2s, color 0.2s;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: rgb(228, 138, 20);
            color: #fff;
        }

        .navbar1,
        .navbar2 {
            transition: top 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .search-input-wrapper {
            position: relative;
            width: 100%;
        }

        .search-input-wrapper .fa-magnifying-glass {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 1.1rem;
            pointer-events: none;
            z-index: 2;
        }

        .search-input-wrapper input[type="text"] {
            padding-left: 2.2rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: none;
            font-size: 1rem;
            background: #fff;
            transition: border-color 0.2s;
        }

        .search-input-wrapper input[type="text"]:focus {
            border-color: #bfa14a;
            outline: none;
            box-shadow: 0 0 0 2px rgba(228, 138, 20, 0.08);
        }

        .dropdown-menu.p-2 {
            min-width: 250px;
        }
    </style>

</head>

<body>

    <?php
    // session_start(); // Do NOT call session_start() here. Only in main files before including the navbar.
    $is_logged_in = isset($_SESSION['username']) && isset($_SESSION['firstname']) && isset($_SESSION['role']);
    $base_path = '';
    if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
        $base_path = '../';
    }
    $current_page = $_SERVER['PHP_SELF'];
    $is_admin_dashboard = strpos($current_page, '/admin/dashboard.php') !== false;
    $is_attendee_dashboard = strpos($current_page, '/attendee/dashboard.php') !== false;
    $is_organizer_dashboard = strpos($current_page, '/organizer/dashboard.php') !== false;
    ?>

    <nav class="navbar1 fixed-top" id="navbar">
        <div class="container">
            <a href="/campus_ems/home.php" class="navbar-logo">
                <img src="/campus_ems/assets/images/calendar.png" width="32" height="32" alt="logo">
                <span class="navbar-logo-text">EventHub</span>
            </a>
            <div class="navbar-center">
                <ul class="nav mb-0">
                    <li><a href="/campus_ems/home.php" class="nav-link">Home</a></li>
                    <li><a href="/campus_ems/events.php" class="nav-link">Events</a></li>
                    <li><a href="/campus_ems/timeline.php" class="nav-link">timeline</a></li>
                    <!-- <li><a href="#" class="nav-link">Albums</a></li> -->
                    <li><a href="/campus_ems/about.php" class="nav-link">About</a></li>
                </ul>
            </div>
            <div class="navbar-icons">
                <div class="dropdown d-inline-block" id="searchDropdownDesktop" style="position: relative;">
                    <i class="fa fa-search" id="searchDropdownToggleDesktop" style="cursor:pointer"></i>
                    <div class="dropdown-menu p-2" id="searchDropdownMenuDesktop"
                        style="min-width: 250px; right: 0; left: auto;">
                        <form class="d-flex" action="/campus_ems/search.php" method="get"
                            onsubmit="return searchSubmitHandler();" style="width:100%;">
                            <div class="search-input-wrapper w-100">
                                <i class="fa fa-magnifying-glass"></i>
                                <input class="form-control" type="text" name="keyword" id="navbarSearchInputDesktop"
                                    placeholder="Search events..." autocomplete="off" required>
                            </div>
                        </form>
                    </div>
                </div>
                <?php if (!$is_logged_in): ?>
                    <!-- Guest: just icon -->
                    <i class="fa fa-user" onclick="window.location='login/login-user.php'" style="cursor:pointer"></i>
                <?php else: ?>
                    <!-- Logged-in: show name and dropdown -->
                    <div class="dropdown d-inline-block">
                        <span class="fw-bold" style="color:white;">
                            <?php echo isset($_SESSION['firstname']) ? htmlspecialchars($_SESSION['firstname']) : ''; ?>
                        </span>
                        <i class="fa fa-user dropdown-toggle ms-1" id="userDropdown" data-bs-toggle="dropdown"
                            aria-expanded="false" style="cursor:pointer;"></i>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <?php if ($_SESSION['role'] === 'admin' && !$is_admin_dashboard): ?>
                                <li><a class="dropdown-item" href="/campus_ems/admin/dashboard.php">Dashboard</a></li>
                            <?php elseif ($_SESSION['role'] === 'attendee' && !$is_attendee_dashboard): ?>
                                <li><a class="dropdown-item" href="/campus_ems/attendee/dashboard.php">Dashboard</a></li>
                            <?php elseif ($_SESSION['role'] === 'organizer' && !$is_organizer_dashboard): ?>
                                <li><a class="dropdown-item" href="/campus_ems/organizer/dashboard.php">Dashboard</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="/campus_ems/login/logout-user.php">Logout</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <nav class="navbar2 fixed-top navbar" id="navbar" aria-label="Offcanvas navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="/campus_ems/home.php" class="navbar-logo">
                <img src="/campus_ems/assets/images/calendar.png" width="32" height="32" alt="logo">
                <span class="navbar-logo-text text-white">EventHub</span>
            </a>
            <div class="d-flex align-items-center ms-auto">
                <div class="navbar-icons me-2">
                    <div class="dropdown d-inline-block" id="searchDropdownMobile" style="position: relative;">
                        <i class="fa fa-search" id="searchDropdownToggleMobile" style="cursor:pointer"></i>
                        <div class="dropdown-menu p-2" id="searchDropdownMenuMobile"
                            style="min-width: 250px; right: 0; left: auto;">
                            <form class="d-flex" action="/campus_ems/search.php" method="get"
                                onsubmit="return searchSubmitHandler();" style="width:100%;">
                                <div class="search-input-wrapper w-100">
                                    <i class="fa fa-magnifying-glass"></i>
                                    <input class="form-control" type="text" name="keyword" id="navbarSearchInputMobile"
                                        placeholder="Search events..." autocomplete="off" required>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php if (!$is_logged_in): ?>
                        <!-- Guest: just icon -->
                        <i class="fa fa-user" onclick="window.location='login/login-user.php'" style="cursor:pointer"></i>
                    <?php else: ?>
                        <!-- Logged-in: show name and dropdown -->
                        <div class="dropdown d-inline-block">
                            <span class="fw-bold" style="color:white;">
                                <?php echo isset($_SESSION['firstname']) ? htmlspecialchars(string: $_SESSION['firstname']) : ''; ?>
                            </span>
                            <i class="fa fa-user dropdown-toggle ms-1" id="userDropdown" data-bs-toggle="dropdown"
                                aria-expanded="false" style="cursor:pointer;"></i>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <?php if ($_SESSION['role'] === 'admin' && !$is_admin_dashboard): ?>
                                    <li><a class="dropdown-item" href="/campus_ems/admin/dashboard.php">Dashboard</a></li>
                                <?php elseif ($_SESSION['role'] === 'attendee' && !$is_attendee_dashboard): ?>
                                    <li><a class="dropdown-item" href="/campus_ems/attendee/dashboard.php">Dashboard</a></li>
                                <?php elseif ($_SESSION['role'] === 'organizer' && !$is_organizer_dashboard): ?>
                                    <li><a class="dropdown-item" href="/campus_ems/organizer/dashboard.php">Dashboard</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/campus_ems/login/logout-user.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
                <button class="offcanvas-btn btn fw-bolder border-0" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions"><i
                        class="fa fa-bars"></i></button>
            </div>
            <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
                aria-labelledby="offcanvasWithBothOptionsLabel">
                <div class="offcanvas-header">
                    <a href="/campus_ems/home.php" class="navbar-logo">
                        <img src="/campus_ems/assets/images/calendar.png" width="28" height="28" alt="logo">
                        <span class="navbar-logo-text text-white">EventHub</span>
                    </a>
                    <button type="button" class="btn-close z d-flex align-items-center justify-content-center"
                        data-bs-dismiss="offcanvas" aria-label="Close"
                        style="width: 2.5rem; height: 2.5rem; background: #eee; border-radius: 50%;">
                        <i class="fa fa-x"></i>
                    </button>
                </div>
                <div class="offcanvas-body">
                    <ul class="nav flex-column align-items-center justify-content-center w-100 mb-4">
                        <li><a href="/campus_ems/home.php" class="nav-link">Home</a></li>
                        <li><a href="/campus_ems/events.php" class="nav-link">Events</a></li>
                        <li><a href="/campus_ems/timeline.php" class="nav-link">timeline</a></li>
                        <li><a href="/campus_ems/about.php" class="nav-link">About</a></li>
                    </ul>
                    <div class="navbar-icons d-flex justify-content-center mb-2">
                        <i class="fa-brands fa-facebook-f"></i>
                        <i class="fa-brands fa-x-twitter"></i>
                        <i class="fa-brands fa-reddit"></i>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="b-example-divider"></div>

    <script>
        const navbars = document.querySelectorAll('.navbar1, .navbar2');
        const navbarHeight = 80; // Adjust to your navbar's height in px
        navbars.forEach((navbar) => {
            let lastScrollTop = 0;
            window.addEventListener('scroll', () => {
                const scrollTop = window.scrollY;
                if (scrollTop > lastScrollTop) {
                    // scrolling down
                    navbar.style.top = `-${navbarHeight}px`;
                } else {
                    // scrolling up
                    navbar.style.top = '0';
                }
                lastScrollTop = scrollTop;
            });
        });
    </script>

    <script>
        function setupSearchDropdown(toggleId, menuId, inputId) {
            const searchIcon = document.getElementById(toggleId);
            const searchMenu = document.getElementById(menuId);
            const searchInput = document.getElementById(inputId);
            let isOpen = false;
            if (!searchIcon || !searchMenu || !searchInput) return;
            searchIcon.addEventListener('click', function (e) {
                e.stopPropagation();
                searchMenu.classList.toggle('show');
                if (searchMenu.classList.contains('show')) {
                    searchInput.focus();
                }
                isOpen = searchMenu.classList.contains('show');
            });
            document.addEventListener('click', function (e) {
                if (isOpen && !searchMenu.contains(e.target) && e.target !== searchIcon) {
                    searchMenu.classList.remove('show');
                    isOpen = false;
                }
            });
        }
        document.addEventListener('DOMContentLoaded', function () {
            setupSearchDropdown('searchDropdownToggleDesktop', 'searchDropdownMenuDesktop', 'navbarSearchInputDesktop');
            setupSearchDropdown('searchDropdownToggleMobile', 'searchDropdownMenuMobile', 'navbarSearchInputMobile');
        });
        function searchSubmitHandler() {
            return true;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>