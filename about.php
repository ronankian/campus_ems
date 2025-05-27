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

</head>

<body>
    <?php include 'navbar.php'; ?>
    <?php include 'bg-image.php'; ?>
    <!-- Your home page content here -->
    <div class="container">

        <div class="row featurette">
            <div class="col-md-7 order-md-2">
                <h2 class="featurette-heading fw-normal lh-1">Our Story</h2>
                <p class="lead">EventHub began as a collaborative project among students who saw the need for a better
                    way to organize and manage campus events at CvSU-CCAT. What started as a simple idea for a class
                    project soon grew into a full-featured platform, designed to help students, faculty, and
                    organizations connect and create memorable campus experiences. Our journey has been driven by a
                    passion for community, innovation, and making event management accessible to everyone in our
                    university.</p>
            </div>
            <div class="col-md-5 order-md-1">
                <img src="assets/images/abt01.png"
                    class="bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" width="500"
                    height="500" alt="image">
            </div>
        </div>

        <hr class="m-5">

        <div class="row featurette">
            <div class="col-md-7">
                <h2 class="featurette-heading fw-normal lh-1">Our Mission</h2>
                <p class="lead">At EventHub, our mission is to empower the CvSU-CCAT community by providing a modern,
                    user-friendly platform for organizing, managing, and participating in campus events. We are
                    dedicated to fostering engagement, collaboration, and a vibrant campus life by making event
                    management accessible to students, faculty, and organizations alike.</p>
            </div>
            <div class="col-md-5">
                <img src="assets/images/abt02.png"
                    class="bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" width="500"
                    height="500" alt="image">
            </div>
        </div>

        <h2 class="py-4 pb-2 mb-5 border-bottom">Meet the Team</h2>

        <div class="row">
            <div class="col-lg-3 text-center">
                <img src="assets/images/ab2.png" class="bd-placeholder-img rounded-circle" width="140" height="140"
                    alt="image">
                <h2 class="fw-normal mt-4">Julius Christian Cuvos</h2>
            </div><!-- /.col-lg-4 -->
            <div class="col-lg-3 text-center">
                <img src="assets/images/ab1.jfif" class="bd-placeholder-img rounded-circle" width="140" height="140"
                    alt="image">
                <h2 class="fw-normal mt-4">Jochelle Mae Dela Torre</h2>

            </div><!-- /.col-lg-4 -->
            <div class="col-lg-3 text-center">
                <img src="assets/images/ab3.png" class="bd-placeholder-img rounded-circle" width="140" height="140"
                    alt="image">
                <h2 class="fw-normal mt-4">Catherine Marie Manggay</h2>

            </div><!-- /.col-lg-4 -->
            <div class="col-lg-3 text-center">
                <img src="assets/images/ab4.png" class="bd-placeholder-img rounded-circle" width="140" height="140"
                    alt="image">
                <h2 class="fw-normal mt-4">Ronan Kian Mangubat</h2>
            </div><!-- /.col-lg-4 -->

        </div>

        <div class="container py-5">
            <h2 class="pb-2 border-bottom">What We Offer</h2>
            <div class="row g-4 mt-2 justify-content-center">
                <div class="col-md-12 text-center">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-calendar-plus fa-2x text-primary"></i>
                                    <div>
                                        <h5 class="card-title mb-1">Event Creation & Management</h5>
                                        <p class="card-text small">Organizers can easily create, edit, and manage events
                                            with detailed descriptions, images, and schedules.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-user-check fa-2x text-success"></i>
                                    <div>
                                        <h5 class="card-title mb-1">Online Registration</h5>
                                        <p class="card-text small">Attendees can register for events online, view event
                                            details, and receive instant confirmation.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-stream fa-2x text-info"></i>
                                    <div>
                                        <h5 class="card-title mb-1">Event Timeline & Calendar</h5>
                                        <p class="card-text small">Stay updated with a visual timeline and calendar of
                                            all upcoming and past events.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-users-cog fa-2x text-warning"></i>
                                    <div>
                                        <h5 class="card-title mb-1">User Roles & Permissions</h5>
                                        <p class="card-text small">Support for attendees, organizers, and admins, each
                                            with tailored access and controls.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-envelope-open-text fa-2x text-danger"></i>
                                    <div>
                                        <h5 class="card-title mb-1">Inbox & Messaging</h5>
                                        <p class="card-text small">Built-in messaging system for event-related
                                            communication, including file attachments and admin replies.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-flag fa-2x text-primary"></i>
                                    <div>
                                        <h5 class="card-title mb-1">Reports & Requests</h5>
                                        <p class="card-text small">Users can submit event reports, feedback, and special
                                            requests directly through the platform.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-paperclip fa-2x text-info"></i>
                                    <div>
                                        <h5 class="card-title mb-1">File Attachments</h5>
                                        <p class="card-text small">Upload and download event-related documents and
                                            images securely.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-mobile-alt fa-2x text-success"></i>
                                    <div>
                                        <h5 class="card-title mb-1">Responsive Design</h5>
                                        <p class="card-text small">Fully optimized for desktop and mobile devices.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-user-edit fa-2x text-warning"></i>
                                    <div>
                                        <h5 class="card-title mb-1">User Account Management</h5>
                                        <p class="card-text small">Profile editing, password reset, and role upgrade
                                            requests.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-user-lock fa-2x text-danger"></i>
                                    <div>
                                        <h5 class="card-title mb-1">Access Control</h5>
                                        <p class="card-text small">Special handling for banned users, including
                                            restricted actions and unban requests.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-tools fa-2x text-primary"></i>
                                    <div>
                                        <h5 class="card-title mb-1">Admin Dashboard</h5>
                                        <p class="card-text small">Powerful tools for managing users, events, and
                                            platform content.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 text-white bg-dark shadow-sm border-0 rounded-4 p-3"
                                style="background: rgba(43,45,66,0.85) !important;">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i class="fa fa-star fa-2x text-warning"></i>
                                    <div>
                                        <h5 class="card-title mb-1">And much more!</h5>
                                        <p class="card-text small">EventHub continues to evolve, bringing new features
                                            to enhance campus life and foster a connected community.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>