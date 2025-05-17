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
        .dashboard-container {
            border-radius: 6px;
            backdrop-filter: blur(8px);
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

        <div class="row dashboard-container p-3 py-4">

            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 ps-4">
                <div class="row g-3 mb-4">

                    <!-- Welcome -->
                    <div class="col-12">
                        <h4 class="fw-bold mb-3">Attedee Overview</h4>
                    </div>

                    <!-- Counts -->
                    <div class="col-md-3">
                        <div class="card card-summary shadow border-0 text-center p-3">
                            <div class="icon text-primary"><i class="fa fa-calendar-check"></i></div>
                            <div class="fw-bold fs-5">1</div>
                            <div class="text-muted">Events Attended</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-summary shadow border-0 text-center p-3">
                            <div class="icon text-primary"><i class="fa fa-clipboard-check"></i></div>
                            <div class="fw-bold fs-5">3</div>
                            <div class="text-muted">Events Registered</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-summary shadow border-0 text-center p-3">
                            <div class="icon text-primary"><i class="fa fa-clock-rotate-left"></i></div>
                            <div class="fw-bold fs-5">5</div>
                            <div class="text-muted">Pending Registration</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-summary shadow border-0 text-center p-3">
                            <div class="icon text-primary"><i class="fa fa-calendar-alt"></i></div>
                            <div class="fw-bold fs-5">5</div>
                            <div class="text-muted">Member Since</div>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card shadow border-0 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0"><i class="fa fa-calendar me-2"></i>Calendar</h5>
                                <button class="btn btn-outline-secondary btn-sm" id="prevMonthBtn"><i
                                        class="fa fa-chevron-left"></i></button>
                                <span id="calendarMonthYear" class="fw-bold"></span>
                                <button class="btn btn-outline-secondary btn-sm" id="nextMonthBtn"><i
                                        class="fa fa-chevron-right"></i></button>
                            </div>
                            <div id="calendarContainer" class="table-responsive">
                                <table class="table table-bordered text-center mb-0" id="calendarTable">
                                    <!-- Calendar will be rendered here -->
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    // Calendar rendering logic
                    const calendarTable = document.getElementById('calendarTable');
                    const calendarMonthYear = document.getElementById('calendarMonthYear');
                    const prevMonthBtn = document.getElementById('prevMonthBtn');
                    const nextMonthBtn = document.getElementById('nextMonthBtn');

                    let today = new Date();
                    let currentMonth = today.getMonth();
                    let currentYear = today.getFullYear();

                    function renderCalendar(month, year) {
                        const monthNames = [
                            "January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December"
                        ];
                        calendarMonthYear.textContent = `${monthNames[month]} ${year}`;

                        // First day of the month
                        const firstDay = new Date(year, month, 1).getDay();
                        // Number of days in the month
                        const daysInMonth = new Date(year, month + 1, 0).getDate();

                        let table = '<thead><tr>';
                        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        for (let d of days) {
                            table += `<th>${d}</th>`;
                        }
                        table += '</tr></thead><tbody><tr>';

                        // Fill in the blanks before the first day
                        for (let i = 0; i < firstDay; i++) {
                            table += '<td></td>';
                        }

                        // Fill in the days of the month
                        for (let date = 1; date <= daysInMonth; date++) {
                            const isToday = (date === today.getDate() && month === today.getMonth() && year === today.getFullYear());
                            table += `<td${isToday ? ' class="bg-primary text-white fw-bold"' : ''}>${date}</td>`;
                            if ((firstDay + date) % 7 === 0 && date !== daysInMonth) {
                                table += '</tr><tr>';
                            }
                        }

                        // Fill in the blanks after the last day
                        const lastDay = (firstDay + daysInMonth) % 7;
                        if (lastDay !== 0) {
                            for (let i = lastDay; i < 7; i++) {
                                table += '<td></td>';
                            }
                        }

                        table += '</tr></tbody>';
                        calendarTable.innerHTML = table;
                    }

                    prevMonthBtn.addEventListener('click', function () {
                        currentMonth--;
                        if (currentMonth < 0) {
                            currentMonth = 11;
                            currentYear--;
                        }
                        renderCalendar(currentMonth, currentYear);
                    });

                    nextMonthBtn.addEventListener('click', function () {
                        currentMonth++;
                        if (currentMonth > 11) {
                            currentMonth = 0;
                            currentYear++;
                        }
                        renderCalendar(currentMonth, currentYear);
                    });

                    // Initial render
                    renderCalendar(currentMonth, currentYear);
                </script>

                <!-- Upcoming Events -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="card shadow border-0 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0"><i class="fa fa-list me-2"></i>Upcoming Events</h5>
                                <a href="events.php" class="btn btn-outline-primary btn-sm">View All</a>
                            </div>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Campus Orientation
                                    <span class="badge bg-primary">Upcoming</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Tech Talk 2024
                                    <span class="badge bg-success">Attended</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Sports Fest
                                    <span class="badge bg-warning text-dark">Pending</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Recently Attended -->
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="card shadow border-0 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0"><i class="fa fa-check-circle me-2"></i>Recently Attended</h5>
                                <a href="events.php" class="btn btn-outline-primary btn-sm">View All</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>