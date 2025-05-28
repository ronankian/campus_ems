<!-- Footer Bar -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gameverse</title>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>


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

        .footer {
            background: rgba(43, 45, 66, 0.7) !important;
            color: var(--text-main);
            margin-top: 5%;
            background-position: bottom;
        }

        .footer-logo-text {
            background: linear-gradient(135deg, #ff3cac 0%, #38f9d7 100%);
            background-clip: text;
            color: transparent;
        }

        .f-link {
            color: white;
        }

        .f-link:hover {
            color: #FEC53A;
        }

        .f-hover:hover {
            color: #FEC53A !important;
        }

        .f-hover-primary:hover {
            background-color: #6610f2;
            border-color: #6610f2;
            color: #fff;
        }
    </style>

</head>

<body>

    <footer class="footer mt-5">
        <div class="container py-5">
            <div class="row">
                <!-- Brand/Description -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <span class="footer-logo-text fw-bold fs-3">EventHub</span>
                    <p class="mt-1 mb-3 text-white">
                        EventHub empowers the CvSU-CCAT community to seamlessly organize and manage campus events. Built
                        with Bootstrap, PHP, and MySQL, it delivers a modern and reliable event management experience.
                    </p>

                </div>
                <!-- Quick Links -->
                <div class="col-md-2 ps-5 mb-md-0">
                    <h5 class="head-text fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="home.php" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="events.php" class="text-white text-decoration-none">Events</a></li>
                        <li><a href="timeline.php" class="text-white text-decoration-none">Timeline</a></li>
                        <li><a href="contact.php" class="text-white text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <!-- Property Types -->
                <div class="col-md-3 ps-5 mb-md-0">
                    <h5 class="head-text fw-bold mb-3">Developers</h5>
                    <ul class="list-unstyled">
                        <li>Julius Christian Cuvos</li>
                        <li>Catherine Marie Manggay</li>
                        <li>Jochelle Mae Dela Torre</li>
                        <li>Ronan Kian Mangubat</li>
                    </ul>
                </div>
                <!-- Contact Us -->
                <div class="col-md-3">
                    <h5 class="head-text fw-bold mb-3">Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt mb-2 me-2"></i>Brgy. Tejeros Convention, Cavite</li>
                        <li><i class="fas fa-phone mb-2 me-2"></i>+63 22 619 519</li>
                        <li><i class="fas fa-envelope mb-2 me-2"></i>projectems0000@gmail.com</li>
                    </ul>
                    <p class="mt-5">
                        <a href="#" class="text-decoration-none text-white">
                            Back to top <i class="fas fa-caret-up"></i>
                        </a>
                    </p>
                </div>
            </div>
            <hr class="my-4" style="border-color: #fff;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <p class="mb-2 mb-md-0">&copy; 2025 EventHub. All rights reserved.</p>
                <div class="d-flex gap-2">
                    <a href="#" class="text-white fs-5 me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white fs-5 me-2"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="text-white fs-5 me-2"><i class="fab fa-reddit"></i></a>
                    <a href="#" class="text-white fs-5 me-2"><i class="fab fa-github"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Font Awesome for icons (if not already included) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</body>

</html>