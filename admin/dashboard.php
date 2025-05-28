<?php
session_start();

$con = mysqli_connect('localhost', 'root', '', 'campus_ems');
if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// User count (excluding admin)
$user_count = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM usertable WHERE role != 'admin'"))[0];

// Event count
$event_count = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM create_events"))[0];

// Registration count
$registration_count = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM registers"))[0];

// Inbox count
$inbox_count = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM inbox"))[0];

// Fetch all events for the calendar
$events = [];
$event_query = mysqli_query($con, "SELECT id, event_title, date_time, ending_time, status FROM create_events");
while ($row = mysqli_fetch_assoc($event_query)) {
    $events[] = $row;
}
// Count event statuses for the status pie chart
$status_counts = [
    'ongoing' => 0,
    'active' => 0,
    'cancelled' => 0,
    'ended' => 0
];
foreach ($events as $ev) {
    if (isset($status_counts[$ev['status']])) {
        $status_counts[$ev['status']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub Attendee Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .dashboard-container {
            border-radius: 6px;
        }

        .card-summary {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(10px) !important;
            color: #fff !important;
        }

        .card-summary .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .calendar-container {
            padding: 10px;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .weekday {
            font-weight: 500;
            padding: 5px;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-day {
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #fff;
            position: relative;
        }

        .calendar-day.today {
            font-weight: bold;
        }

        .calendar-day.selected {
            font-weight: bold;
        }

        .calendar-day.other-month {
            opacity: 0.5;
        }

        .calendar-day:hover {
            background-color: rgba(255, 255, 255, 0.08);
        }
    </style>

</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../bg-image.php'; ?>

    <div class="container-fluid">
        <div class="d-flex align-items-start dashboard-container">
            <?php include 'sidebar.php'; ?>
            <div class="flex-grow-1 px-4 mb-4">
                <div class="row g-3 mb-2">
                    <div class="col-12">
                        <h4 class="fw-bold" style="text-indent: 10px;">Dashboard Overview</h4>
                    </div>
                </div>

                <!-- Site Metrics -->
                <div class="row mb-4 align-items-stretch" style="min-height: 320px;">
                    <div class="col-md-8 d-flex">
                        <div class="card card-summary p-3 h-100 w-100" style="min-height: 100%;">
                            <h5 class="fw-bold text-center" id="siteMetricsTitle">Site Metrics</h5>
                            <div id="legend-container" class="my-4 text-center"></div>
                            <canvas id="metricsLineChart" height="150" class="mb-3"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4 h-100">
                        <div class="row g-3 h-100">
                            <div class="col-6 h-50">
                                <a href="users.php" class="text-decoration-none h-100">
                                    <div class="card card-summary h-100 shadow border-0 text-center p-3 flex-fill">
                                        <div class="icon text-primary"><i class="fa fa-users"></i></div>
                                        <div class="fw-bold fs-4"><?php echo $user_count; ?></div>
                                        <div class="text-white">Users</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 h-50">
                                <a href="eventlists.php" class="text-decoration-none h-100">
                                    <div class="card card-summary h-100 shadow border-0 text-center p-3 flex-fill">
                                        <div class="icon text-success"><i class="fa fa-calendar-alt"></i></div>
                                        <div class="fw-bold fs-4"><?php echo $event_count; ?></div>
                                        <div class="text-white">Events</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 h-50">
                                <a href="registrations.php" class="text-decoration-none h-100">
                                    <div class="card card-summary h-100 shadow border-0 text-center p-3 flex-fill">
                                        <div class="icon text-warning"><i class="fa fa-clipboard-check"></i></div>
                                        <div class="fw-bold fs-4"><?php echo $registration_count; ?></div>
                                        <div class="text-white">Registrations</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 h-50">
                                <a href="inbox.php" class="text-decoration-none h-100">
                                    <div class="card card-summary h-100 shadow border-0 text-center p-3 flex-fill">
                                        <div class="icon text-danger"><i class="fa fa-envelope"></i></div>
                                        <div class="fw-bold fs-4"><?php echo $inbox_count; ?></div>
                                        <div class="text-white">Messages</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card card-summary p-3">
                            <div class="calendar-container">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0">Event Calendar</h5>
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-outline-secondary btn-sm me-5" id="prevMonthBtn"><i
                                                class="fa fa-chevron-left"></i></button>
                                        <span id="calendarMonthYear" class="fw-bold"></span>
                                        <button class="btn btn-outline-secondary btn-sm ms-5" id="nextMonthBtn"><i
                                                class="fa fa-chevron-right"></i></button>
                                    </div>
                                </div>
                                <div class="calendar-weekdays mb-2">
                                    <div class="weekday text-center text-white">Sun</div>
                                    <div class="weekday text-center text-white">Mon</div>
                                    <div class="weekday text-center text-white">Tue</div>
                                    <div class="weekday text-center text-white">Wed</div>
                                    <div class="weekday text-center text-white">Thu</div>
                                    <div class="weekday text-center text-white">Fri</div>
                                    <div class="weekday text-center text-white">Sat</div>
                                </div>
                                <div class="calendar-days" id="calendarDays"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-summary p-3 mb-4">
                            <h5 class="fw-bold text-center mb-4">Event Status</h5>
                            <div class="row align-items-center pb-2">
                                <div class="col-1"></div>
                                <div class="col-6">
                                    <canvas id="statusPieChart" width="120" height="120"></canvas>
                                </div>
                                <div class="col-5" id="statusLegend"></div>
                            </div>
                        </div>
                        <div class="card card-summary p-3">
                            <h5 class="fw-bold text-center mb-4">Message Status</h5>
                            <div class="row align-items-center pb-2">
                                <div class="col-1"></div>
                                <div class="col-6">
                                    <canvas id="inboxStatusPieChart" width="120" height="120"></canvas>
                                </div>
                                <div class="col-5" id="inboxStatusLegend"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-summary p-3">
                    <div class="col-md-12 d-flex justify-content-between align-items-end border-bottom">
                        <h5 class="fw-bold mb-3"> Upcoming Event</h5>
                        <a href="eventlists.php"
                            class="btn btn-link mb-1 fs-5 text-decoration-none text-white fw-bold d-flex align-items-center">
                            View all
                            <i class="fa fa-caret-right ms-2"></i>
                        </a>
                    </div>
                    <?php
                    // Use the same connection as the dashboard
                    $now = date('Y-m-d H:i:s');
                    $query = "SELECT * FROM create_events WHERE (status = 'active' OR status = 'ongoing' OR status IS NULL) AND date_time > '$now' ORDER BY date_time ASC LIMIT 3";
                    $result = mysqli_query($con, $query);
                    if (mysqli_num_rows($result) > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($row = mysqli_fetch_assoc($result)):
                                $status = isset($row['status']) ? $row['status'] : 'active';
                                $img = null;
                                if (!empty($row['attach_file'])) {
                                    $files = json_decode($row['attach_file'], true);
                                    if (is_array($files) && count($files) > 0) {
                                        foreach ($files as $file) {
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                                $img = 'uploads/' . str_replace('\\', '/', $file);
                                                break;
                                            }
                                        }
                                    }
                                }
                                $event_title = htmlspecialchars($row['event_title']);
                                $badge = ($status === 'cancelled') ? '<span class="badge text-bg-danger">Cancelled</span>' :
                                    (($status === 'ended') ? '<span class="badge text-bg-secondary">Ended</span>' :
                                        (($status === 'ongoing') ? '<span class="badge text-bg-primary">Ongoing</span>' :
                                            '<span class="badge text-bg-success">Active</span>'));
                                $date_str = '';
                                if ($status === 'cancelled' && !empty($row['date_cancelled'])) {
                                    $date_str = 'Cancelled: ' . date('F d, Y | h:i A', strtotime($row['date_cancelled']));
                                } else {
                                    $date_str = date('F d, Y | h:i A', strtotime($row['date_time']));
                                    if (!empty($row['ending_time'])) {
                                        $date_str .= ' - ' . date('F d, Y | h:i A', strtotime($row['ending_time']));
                                    }
                                }
                                ?>
                                <div class="row g-0 my-4 event-row-item"
                                    style="border-radius: 16px; overflow: hidden; position: relative; height: 250px;">
                                    <a href="../event-details.php?id=<?php echo $row['id']; ?>"
                                        style="display:block; width:100%; height:100%; position:relative; text-decoration:none;">
                                        <?php if ($img): ?>
                                            <img src="/campus_ems/<?php echo htmlspecialchars($img); ?>" alt="Event image"
                                                style="width:100%; height:100%; object-fit:cover; display:block;">
                                        <?php else: ?>
                                            <div
                                                style="width:100%; height:100%; background:#888; display:flex; align-items:center; justify-content:center;">
                                                <span class="no-image-watermark text-white-50 fw-semibold fs-4 pb-5">No
                                                    Image Found</span>
                                            </div>
                                        <?php endif; ?>
                                        <div
                                            style="position:absolute;left:0;bottom:0;width:100%;background:linear-gradient(90deg,rgba(43,45,66,0.92) 60%,rgba(43,45,66,0.0) 100%);padding:1.5rem 2.5rem 1.5rem 1.5rem;display:flex;flex-direction:column;align-items:flex-start;">
                                            <h4 class="mb-2 text-white fw-bold d-flex align-items-center" style="gap:10px;">
                                                <?php echo $event_title; ?>         <?php echo $badge; ?>
                                            </h4>
                                            <div class="text-white-50 fw-semibold fs-6">
                                                <?php echo $date_str; ?>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-white-50 text-center">No upcoming events.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        console.log('Matrix plugin assumed loaded correctly for Chart.js v3');

        fetch('dashboard-metrics.php')
            .then(res => res.json())
            .then(data => {
                // Set the Site Metrics title to current month and year
                const now = new Date();
                const monthName = now.toLocaleString('default', { month: 'long' });
                const year = now.getFullYear();
                document.getElementById('siteMetricsTitle').textContent = `Site Metrics (${monthName}, ${year})`;
                const ctx = document.getElementById('metricsLineChart').getContext('2d');
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels.map(day => String(day)), // Only day numbers
                        datasets: [
                            {
                                label: 'Users',
                                data: data.users,
                                borderColor: '#4e73df',
                                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                                fill: false,
                                tension: 0.3
                            },
                            {
                                label: 'Events',
                                data: data.events,
                                borderColor: '#1cc88a',
                                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                                fill: false,
                                tension: 0.3
                            },
                            {
                                label: 'Registrations',
                                data: data.registrations,
                                borderColor: '#f6c23e',
                                backgroundColor: 'rgba(246, 194, 62, 0.1)',
                                fill: false,
                                tension: 0.3
                            },
                            {
                                label: 'Messages',
                                data: data.inbox,
                                borderColor: '#e74a3b',
                                backgroundColor: 'rgba(231, 74, 59, 0.1)',
                                fill: false,
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false // Hide built-in legend
                            },
                            title: { display: false }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#fff' },
                                grid: { color: 'rgba(255,255,255,0.2)' }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#fff',
                                    stepSize: 1,
                                    callback: function (value) {
                                        if (Number.isInteger(value)) {
                                            return value;
                                        }
                                        return '';
                                    }
                                },
                                grid: { color: 'rgba(255,255,255,0.2)' }
                            }
                        }
                    }
                });

                // Custom HTML legend
                function createCustomLegend(chart) {
                    const container = document.getElementById('legend-container');
                    container.innerHTML = '';
                    chart.data.datasets.forEach((ds, i) => {
                        const legendItem = document.createElement('span');
                        legendItem.style.display = 'inline-flex';
                        legendItem.style.alignItems = 'center';
                        legendItem.style.marginRight = '18px';
                        legendItem.style.cursor = 'pointer';
                        legendItem.style.opacity = chart.isDatasetVisible(i) ? '1' : '0.5';
                        legendItem.innerHTML = `
                            <span style="display:inline-block;width:22px;height:6px;background:${ds.borderColor};border-radius:3px;margin-right:6px;"></span>
                            <span style="color:#fff;font-weight:500;">${ds.label}</span>
                        `;
                        legendItem.onclick = () => {
                            chart.setDatasetVisibility(i, !chart.isDatasetVisible(i));
                            chart.update();
                            createCustomLegend(chart); // Update legend opacity
                        };
                        container.appendChild(legendItem);
                    });
                }
                createCustomLegend(chart);

                // Status Pie Chart and Legend
                const statusCounts = <?php echo json_encode($status_counts); ?>;
                const statusLabels = ['Ongoing', 'Active', 'Cancelled', 'Ended'];
                const statusKeys = ['ongoing', 'active', 'cancelled', 'ended'];
                const statusColors = ['#4e73df', '#1cc88a', '#e74a3b', '#858796'];

                // Pie chart
                const statusPieCtx = document.getElementById('statusPieChart').getContext('2d');
                new Chart(statusPieCtx, {
                    type: 'pie',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusKeys.map(k => statusCounts[k]),
                            backgroundColor: statusColors,
                            borderColor: '#222',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        plugins: {
                            legend: { display: false },
                            title: { display: false }
                        }
                    }
                });

                // Legend
                const statusLegend = document.getElementById('statusLegend');
                statusLegend.innerHTML = statusLabels.map((label, i) =>
                    `<div style="display:flex;align-items:center;margin-bottom:8px;">
                            <span style="display:inline-block;width:18px;height:8px;background:${statusColors[i]};border-radius:3px;margin-right:8px;"></span>
                            <span style="color:#fff;font-weight:500;">${label}</span>
                        </div>`
                ).join('');

                // Inbox Status Pie Chart
                if (data.inbox_status && data.inbox_status.labels && data.inbox_status.data) {
                    const inboxStatusColors = ['#e74a3b', '#4e73df', '#f6c23e', '#1cc88a']; // Red (Unread), Blue (Read), Yellow (Pending), Green (Responded)
                    const inboxStatusCtx = document.getElementById('inboxStatusPieChart').getContext('2d');
                    new Chart(inboxStatusCtx, {
                        type: 'pie',
                        data: {
                            labels: data.inbox_status.labels,
                            datasets: [{
                                data: data.inbox_status.data,
                                backgroundColor: inboxStatusColors,
                                borderColor: '#222',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            plugins: {
                                legend: { display: false },
                                title: { display: false }
                            }
                        }
                    });

                    // Inbox Status Legend
                    const inboxStatusLegend = document.getElementById('inboxStatusLegend');
                    inboxStatusLegend.innerHTML = data.inbox_status.labels.map((label, i) =>
                        `<div style="display:flex;align-items:center;margin-bottom:8px;">
                            <span style="display:inline-block;width:18px;height:8px;background:${inboxStatusColors[i]};border-radius:3px;margin-right:8px;"></span>
                            <span style="color:#fff;font-weight:500;">${label}</span>
                        </div>`
                    ).join('');
                }

                // Pass PHP events to JS
                const events = <?php echo json_encode($events); ?>;

                // Event status colors
                const eventStatusColors = {
                    'ongoing': '#4e73df', // blue
                    'active': '#1cc88a', // green
                    'ended': '#858796',   // gray
                    'cancelled': '#e74a3b' // red
                };

                // Event status stacking order (top to bottom)
                const eventStatusOrder = ['ongoing', 'active', 'cancelled', 'ended'];

                // Helper: parse date string as local date
                function parseDateLocal(dateStr) {
                    // Expects 'YYYY-MM-DD HH:MM:SS'
                    const [datePart, timePart] = dateStr.split(' ');
                    const [year, month, day] = datePart.split('-').map(Number);
                    const [hour, min, sec] = timePart.split(':').map(Number);
                    return new Date(year, month - 1, day, hour, min, sec);
                }

                // Helper: compare only the date part (ignore time)
                function isSameOrBetween(day, start, end) {
                    const d = day.getFullYear() * 10000 + (day.getMonth() + 1) * 100 + day.getDate();
                    const s = start.getFullYear() * 10000 + (start.getMonth() + 1) * 100 + start.getDate();
                    const e = end.getFullYear() * 10000 + (end.getMonth() + 1) * 100 + end.getDate();
                    return d >= s && d <= e;
                }

                // Helper: get all event bars for a given day
                function getEventsForDay(year, month, day) {
                    const dayDate = new Date(year, month, day);
                    return events.filter(ev => {
                        const start = parseDateLocal(ev.date_time);
                        const end = parseDateLocal(ev.ending_time);
                        // If event spans this day (compare only date part)
                        return isSameOrBetween(dayDate, start, end);
                    });
                }

                // Helper: get event bar start/end for a week
                function getEventBarForWeek(ev, weekStart, weekEnd) {
                    const start = parseDateLocal(ev.date_time);
                    const end = parseDateLocal(ev.ending_time);
                    // Clamp event to this week
                    const barStart = Math.max(0, (start - weekStart) / (1000 * 60 * 60 * 24));
                    const barEnd = Math.min(6, (end - weekStart) / (1000 * 60 * 60 * 24));
                    return { left: Math.round(barStart), right: Math.round(barEnd) };
                }

                // Update: Calendar Implementation
                class Calendar {
                    constructor() {
                        this.date = new Date();
                        this.currentMonth = this.date.getMonth();
                        this.currentYear = this.date.getFullYear();
                        this.selectedDate = new Date();
                        this.init();
                    }

                    init() {
                        this.renderCalendar();
                        this.attachEventListeners();
                    }

                    renderCalendar() {
                        const firstDay = new Date(this.currentYear, this.currentMonth, 1);
                        const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
                        const startingDay = firstDay.getDay();
                        const monthLength = lastDay.getDate();
                        const prevMonthLastDay = new Date(this.currentYear, this.currentMonth, 0).getDate();
                        document.getElementById('calendarMonthYear').textContent = `${firstDay.toLocaleString('default', { month: 'long' })} ${this.currentYear}`;
                        const calendarDays = document.getElementById('calendarDays');
                        calendarDays.innerHTML = '';

                        // Build a 6x7 grid (weeks x days)
                        let grid = [];
                        let dayNum = 1;
                        let nextMonthDay = 1;
                        for (let week = 0; week < 6; week++) {
                            let weekRow = [];
                            for (let day = 0; day < 7; day++) {
                                let cell = {};
                                let cellDate;
                                if (week === 0 && day < startingDay) {
                                    // Previous month
                                    cell.type = 'prev';
                                    cell.day = prevMonthLastDay - (startingDay - day - 1);
                                    cellDate = new Date(this.currentYear, this.currentMonth - 1, cell.day);
                                } else if (dayNum > monthLength) {
                                    // Next month
                                    cell.type = 'next';
                                    cell.day = nextMonthDay++;
                                    cellDate = new Date(this.currentYear, this.currentMonth + 1, cell.day);
                                } else {
                                    // Current month
                                    cell.type = 'current';
                                    cell.day = dayNum;
                                    cellDate = new Date(this.currentYear, this.currentMonth, cell.day);
                                    dayNum++;
                                }
                                cell.date = cellDate;
                                weekRow.push(cell);
                            }
                            grid.push(weekRow);
                        }

                        // Render grid
                        grid.forEach(weekRow => {
                            weekRow.forEach(cell => {
                                const day = document.createElement('div');
                                day.className = 'calendar-day';
                                if (cell.type !== 'current') day.classList.add('other-month');
                                day.textContent = cell.day;

                                // Today/selected styling (border-bottom instead of circle)
                                if (cell.type === 'current') {
                                    if (
                                        cell.day === this.date.getDate() &&
                                        this.currentMonth === this.date.getMonth() &&
                                        this.currentYear === this.date.getFullYear()
                                    ) {
                                        day.classList.add('today');
                                    }
                                    if (
                                        cell.day === this.selectedDate.getDate() &&
                                        this.currentMonth === this.selectedDate.getMonth() &&
                                        this.currentYear === this.selectedDate.getFullYear()
                                    ) {
                                        day.classList.add('selected');
                                    }
                                }

                                // EVENTS: Add event bars (border-bottom)
                                if (cell.type === 'current') {
                                    const dayEvents = getEventsForDay(this.currentYear, this.currentMonth, cell.day);
                                    if (dayEvents.length > 0) {
                                        // Sort events by status order
                                        dayEvents.sort((a, b) => eventStatusOrder.indexOf(a.status) - eventStatusOrder.indexOf(b.status));
                                        // Remove any previous event bar container
                                        let barContainer = document.createElement('div');
                                        barContainer.style.position = 'absolute';
                                        barContainer.style.left = 0;
                                        barContainer.style.right = 0;
                                        barContainer.style.bottom = 0;
                                        barContainer.style.height = (dayEvents.length * 5) + 'px';
                                        barContainer.style.pointerEvents = 'none';
                                        barContainer.style.width = '100%';
                                        barContainer.style.zIndex = 2;
                                        day.style.position = 'relative';
                                        day.appendChild(barContainer);
                                        day.style.cursor = 'pointer';
                                        // Render bars in original order for stacking (bottom to top)
                                        dayEvents.forEach((ev, idx) => {
                                            const color = eventStatusColors[ev.status] || '#4e73df';
                                            let bar = document.createElement('div');
                                            bar.style.position = 'absolute';
                                            bar.style.left = 0;
                                            bar.style.right = 0;
                                            bar.style.height = '4px';
                                            bar.style.background = color;
                                            // Calculate bottom offset so first in order is on top
                                            bar.style.bottom = ((dayEvents.length - 1 - idx) * 5) + 'px';
                                            bar.style.borderRadius = '2px';
                                            bar.title = ev.event_title + ' (' + ev.status + ')';
                                            barContainer.appendChild(bar);
                                        });
                                        // Tooltip for all events
                                        day.title = dayEvents.map(e => e.event_title + ' (' + e.status + ')').join('\n');
                                    }
                                }

                                calendarDays.appendChild(day);
                            });
                        });
                    }

                    attachEventListeners() {
                        document.getElementById('prevMonthBtn').addEventListener('click', () => {
                            this.currentMonth--;
                            if (this.currentMonth < 0) {
                                this.currentMonth = 11;
                                this.currentYear--;
                            }
                            this.renderCalendar();
                        });
                        document.getElementById('nextMonthBtn').addEventListener('click', () => {
                            this.currentMonth++;
                            if (this.currentMonth > 11) {
                                this.currentMonth = 0;
                                this.currentYear++;
                            }
                            this.renderCalendar();
                        });
                        document.getElementById('calendarDays').addEventListener('click', (e) => {
                            if (e.target.classList.contains('calendar-day')) {
                                const days = document.querySelectorAll('.calendar-day');
                                days.forEach(day => day.classList.remove('selected'));
                                e.target.classList.add('selected');
                                // Update selected date
                                const day = parseInt(e.target.textContent);
                                if (!e.target.classList.contains('other-month')) {
                                    this.selectedDate = new Date(this.currentYear, this.currentMonth, day);
                                }
                            }
                        });
                    }
                }

                // Initialize calendar
                const calendar = new Calendar();
            });
    </script>
    <?php include '../footer.php'; ?>
</body>

</html>