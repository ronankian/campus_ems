<?php
session_start();
include '../login/connection.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Event Created
$event_created_count = 0;
if ($user_id) {
    $ev_result = mysqli_query($con, "SELECT COUNT(*) FROM create_events WHERE user_id = '$user_id'");
    if ($ev_result)
        $event_created_count = mysqli_fetch_row($ev_result)[0];
}

// Registrants (for all events created by the user)
$registrants_count = 0;
if ($user_id) {
    $reg_result = mysqli_query($con, "SELECT id FROM create_events WHERE user_id = '$user_id'");
    $event_ids = [];
    while ($row = mysqli_fetch_assoc($reg_result)) {
        $event_ids[] = $row['id'];
    }
    if (!empty($event_ids)) {
        $ids_str = implode(',', array_map('intval', $event_ids));
        $reg_count_result = mysqli_query($con, "SELECT COUNT(*) FROM registers WHERE event_id IN ($ids_str)");
        if ($reg_count_result)
            $registrants_count = mysqli_fetch_row($reg_count_result)[0];
    }
}

// Fetch organization from usertable using user_id
$organization = '';
if ($user_id) {
    $user_result = mysqli_query($con, "SELECT organization FROM usertable WHERE id = '$user_id' LIMIT 1");
    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user_row = mysqli_fetch_assoc($user_result);
        $organization = $user_row['organization'] ?? '';
    }
}

// Organization and Member Since
$member_since = '';
if ($user_id) {
    $user_result = mysqli_query($con, "SELECT created_at FROM usertable WHERE id = '$user_id' LIMIT 1");
    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user_row = mysqli_fetch_assoc($user_result);
        $member_since = $user_row['created_at'] ?? '';
    }
}
$member_since_formatted = $member_since ? date('F d, Y', strtotime($member_since)) : '';

// Fetch events created by the user for the calendar
$user_events = [];
if ($user_id) {
    $user_events_query = mysqli_query($con, "SELECT id, event_title, date_time, ending_time, status FROM create_events WHERE user_id = '$user_id'");
    while ($row = mysqli_fetch_assoc($user_events_query)) {
        $user_events[] = $row;
    }
}
// Fetch top 3 nearest upcoming events created by the user
$upcoming_events = [];
if ($user_id) {
    $now = date('Y-m-d H:i:s');
    $upcoming_query = mysqli_query($con, "
        SELECT * FROM create_events
        WHERE user_id = '$user_id'
        AND (
            (status = 'active' AND date_time > '$now')
            OR
            (status = 'ongoing' AND date_time <= '$now' AND ending_time > '$now')
        )
        ORDER BY date_time ASC
        LIMIT 3
    ");
    while ($row = mysqli_fetch_assoc($upcoming_query)) {
        $upcoming_events[] = $row;
    }
}

// Fetch event creation dates and registration dates for metrics
$user_event_dates = [];
$user_reg_dates = [];
if ($user_id) {
    $event_dates_query = mysqli_query($con, "SELECT created_at FROM create_events WHERE user_id = '$user_id' ORDER BY created_at ASC");
    while ($row = mysqli_fetch_assoc($event_dates_query)) {
        $user_event_dates[] = substr($row['created_at'], 0, 10);
    }
    // Get all event IDs created by user
    $event_ids = [];
    $event_id_query = mysqli_query($con, "SELECT id FROM create_events WHERE user_id = '$user_id'");
    while ($row = mysqli_fetch_assoc($event_id_query)) {
        $event_ids[] = $row['id'];
    }
    if (!empty($event_ids)) {
        $ids_str = implode(',', array_map('intval', $event_ids));
        $reg_dates_query = mysqli_query($con, "SELECT registration_date FROM registers WHERE event_id IN ($ids_str) ORDER BY registration_date ASC");
        while ($row = mysqli_fetch_assoc($reg_dates_query)) {
            $user_reg_dates[] = substr($row['registration_date'], 0, 10);
        }
    }
}
// Prepare metrics data for JS
$metrics_labels = array_unique(array_merge($user_event_dates, $user_reg_dates));
sort($metrics_labels);
$event_created_counts = [];
$registrants_counts = [];
foreach ($metrics_labels as $date) {
    $event_created_counts[] = array_reduce($user_event_dates, function ($carry, $d) use ($date) {
        return $carry + ($d == $date ? 1 : 0);
    }, 0);
    $registrants_counts[] = array_reduce($user_reg_dates, function ($carry, $d) use ($date) {
        return $carry + ($d == $date ? 1 : 0);
    }, 0);
}
// For chart title and x-axis formatting
$metrics_month = count($metrics_labels) ? date('F', strtotime($metrics_labels[0])) : date('F');
$metrics_year = count($metrics_labels) ? date('Y', strtotime($metrics_labels[0])) : date('Y');
$metrics_days = array_map(function ($d) {
    return (int) date('d', strtotime($d));
}, $metrics_labels);

// Event Registered (as attendee)
$registered_count = 0;
if ($user_id) {
    $reg_result = mysqli_query($con, "SELECT COUNT(*) FROM registers WHERE user_id = '$user_id'");
    if ($reg_result)
        $registered_count = mysqli_fetch_row($reg_result)[0];
}
// Messages
$messages_count = 0;
if ($user_id) {
    $msg_result = mysqli_query($con, "SELECT COUNT(*) FROM inbox WHERE user_id = '$user_id'");
    if ($msg_result)
        $messages_count = mysqli_fetch_row($msg_result)[0];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        .dashboard-container {
            border-radius: 6px;
        }

        .card-summary {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            background: rgba(43, 45, 66, 0.3) !important;
            backdrop-filter: blur(10px) !important;
            color: #fff !important;
            border: none !important;
        }

        .card-summary .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        #calendarTable {
            background: transparent !important;
            color: #fff !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            table-layout: fixed !important;
            width: 100% !important;
        }

        #calendarTable th,
        #calendarTable td {
            background: transparent !important;
            color: #fff !important;
            border: none !important;
            font-weight: 500;
            font-size: 1.1rem;
            width: 14.28% !important;
            /* 100/7 */
            text-align: center;
            box-sizing: border-box;
            padding: 0.5rem 0;
        }

        #calendarTable th {
            font-weight: bold;
            font-size: 1.1rem;
            color: #fff !important;
            background: transparent !important;
        }

        #calendarTable td {
            position: relative;
            height: 48px;
            vertical-align: bottom;
            background: transparent !important;
            color: #fff !important;
        }

        #calendarTable td span {
            position: relative;
            z-index: 2;
        }

        .bg-today {
            background: #4e73df !important;
            color: #fff !important;
            border-radius: 8px;
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
            <div class="col-md-9">
                <div class="row mb-4 align-items-stretch" style="min-height: 320px;">
                    <div class="col-12">
                        <h4 class="fw-bold mb-3">Dashboard Overview</h4>
                    </div>
                    <div class="col-md-9 d-flex">
                        <div class="card card-summary p-3 h-100 w-100" style="min-height: 100%;">
                            <div class="d-flex justify-content-between align-items-center mb-2"
                                style="min-height: 48px;">
                                <button class="btn btn-outline-secondary btn-sm" id="prevMetricsMonthBtn"><i
                                        class="fa fa-chevron-left"></i></button>
                                <h5 class="fw-bold text-center mb-0 flex-grow-1" style="margin: 0;">
                                    My Metrics <span style="font-weight:normal;">(<span
                                            id="metricsMonthYear"></span>)</span>
                                </h5>
                                <button class="btn btn-outline-secondary btn-sm" id="nextMetricsMonthBtn"><i
                                        class="fa fa-chevron-right"></i></button>
                            </div>
                            <div id="legend-container" class="my-2 text-center"></div>
                            <div style="height:150px;">
                                <canvas id="userMetricsChart" height="150" style="max-height:150px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex flex-column gap-3 h-100">
                        <div class="card card-summary h-100 shadow border-0 text-center p-3 mb-2">
                            <div class="icon text-primary"><i class="fa fa-calendar-plus"></i></div>
                            <div class="fw-bold fs-5"><?php echo $event_created_count; ?></div>
                            <div class="text-white">Event Created</div>
                        </div>
                        <div class="card card-summary h-100 shadow border-0 text-center p-3">
                            <div class="icon text-info"><i class="fa fa-users"></i></div>
                            <div class="fw-bold fs-5"><?php echo $registrants_count; ?></div>
                            <div class="text-white">Registrants</div>
                        </div>
                    </div>
                </div>
                <!-- The other two col-md-3 cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card card-summary shadow border-0 text-center p-3">
                            <div class="icon text-success"><i class="fa fa-clipboard-check"></i></div>
                            <div class="fw-bold fs-5"><?php echo isset($registered_count) ? $registered_count : 0; ?>
                            </div>
                            <div class="text-white">Event Registered</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-summary shadow border-0 text-center p-3">
                            <div class="icon text-warning"><i class="fa fa-envelope"></i></div>
                            <div class="fw-bold fs-5"><?php echo isset($messages_count) ? $messages_count : 0; ?></div>
                            <div class="text-white">Messages</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-summary shadow border-0 text-center p-3">
                            <div class="icon text-danger"><i class="fa fa-building"></i></div>
                            <div class="fw-bold fs-5"><?php echo htmlspecialchars($organization ?: 'N/A'); ?></div>
                            <div class="text-white">Organization</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-summary shadow border-0 text-center p-3">
                            <div class="icon" style="color: var(--primary) !important;"><i
                                    class="fa fa-calendar-day"></i></div>
                            <div class="fw-bold fs-5"><?php echo $member_since_formatted ?: 'N/A'; ?></div>
                            <div class="text-white">Member Since</div>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card card-summary p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">My Event Calendar</h5>
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

                    const userEvents = <?php echo json_encode($user_events); ?>;
                    const eventStatusColors = {
                        'ongoing': '#4e73df', // blue
                        'active': '#1cc88a', // green
                        'ended': '#858796',   // gray
                        'cancelled': '#e74a3b' // red
                    };
                    const eventStatusOrder = ['ongoing', 'active', 'cancelled', 'ended'];

                    function parseDateLocal(dateStr) {
                        const [datePart, timePart] = dateStr.split(' ');
                        const [year, month, day] = datePart.split('-').map(Number);
                        const [hour, min, sec] = timePart.split(':').map(Number);
                        return new Date(year, month - 1, day, hour, min, sec);
                    }
                    function isSameOrBetween(day, start, end) {
                        const d = day.getFullYear() * 10000 + (day.getMonth() + 1) * 100 + day.getDate();
                        const s = start.getFullYear() * 10000 + (start.getMonth() + 1) * 100 + start.getDate();
                        const e = end.getFullYear() * 10000 + (end.getMonth() + 1) * 100 + end.getDate();
                        return d >= s && d <= e;
                    }
                    function getEventsForDay(year, month, day) {
                        const dayDate = new Date(year, month, day);
                        return userEvents.filter(ev => {
                            const start = parseDateLocal(ev.date_time);
                            const end = parseDateLocal(ev.ending_time);
                            return isSameOrBetween(dayDate, start, end);
                        });
                    }

                    function renderCalendar(month, year) {
                        const monthNames = [
                            "January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December"
                        ];
                        calendarMonthYear.textContent = `${monthNames[month]} ${year}`;
                        const firstDay = new Date(year, month, 1).getDay();
                        const daysInMonth = new Date(year, month + 1, 0).getDate();
                        let table = '<thead><tr>';
                        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        for (let d of days) {
                            table += `<th>${d}</th>`;
                        }
                        table += '</tr></thead><tbody><tr>';
                        for (let i = 0; i < firstDay; i++) {
                            table += '<td></td>';
                        }
                        for (let date = 1; date <= daysInMonth; date++) {
                            const isToday = (date === today.getDate() && month === today.getMonth() && year === today.getFullYear());
                            const dayEvents = getEventsForDay(year, month, date);
                            let barHtml = '';
                            if (dayEvents.length > 0) {
                                dayEvents.sort((a, b) => eventStatusOrder.indexOf(a.status) - eventStatusOrder.indexOf(b.status));
                                barHtml = dayEvents.map((ev, idx) => {
                                    const color = eventStatusColors[ev.status] || '#4e73df';
                                    return `<div style="position:absolute;left:0;right:0;height:4px;background:${color};border-radius:2px;bottom:${idx * 5 + 2}px;width:90%;margin:0 5%;" title="${ev.event_title} (${ev.status})"></div>`;
                                }).join('');
                            }
                            table += `<td class="${isToday ? 'bg-today' : ''}" style="vertical-align:bottom;position:relative;height:48px;">
                                <span style="position:relative;z-index:2;">${date}</span>
                                ${barHtml}
                            </td>`;
                            if ((firstDay + date) % 7 === 0 && date !== daysInMonth) {
                                table += '</tr><tr>';
                            }
                        }
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
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="card card-summary shadow border-0 p-4">
                            <div class="col-md-12 d-flex mb-2 justify-content-between align-items-end border-bottom">
                                <h5 class="fw-bold mb-3"> Upcoming Event</h5>
                                <a href="events.php"
                                    class="btn btn-link mb-1 fs-5 text-decoration-none text-white fw-bold d-flex align-items-center">
                                    View all
                                    <i class="fa fa-caret-right ms-2"></i>
                                </a>
                            </div>
                            <?php if (count($upcoming_events) > 0): ?>
                                <?php foreach ($upcoming_events as $row):
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
                                    <div class="row g-0 my-2 event-row-item"
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
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-white-50 text-center">No upcoming events.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        let metricsMonth = new Date().getMonth() + 1; // Always start on current month
        let metricsYear = new Date().getFullYear();
        const ctx = document.getElementById('userMetricsChart').getContext('2d');
        let metricsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Event Created',
                        data: [],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Registrants',
                        data: [],
                        borderColor: '#0dcaf0',
                        backgroundColor: 'rgba(13, 202, 240, 0.1)',
                        fill: false,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
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

        function setMetricsMonthLabel() {
            const dateObj = new Date(metricsYear, metricsMonth - 1);
            const monthName = dateObj.toLocaleString('default', { month: 'long' });
            document.getElementById('metricsMonthYear').textContent = `${monthName}, ${metricsYear}`;
        }

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
        createCustomLegend(metricsChart);

        function updateMetricsChart() {
            fetch('metrics-data.php?month=' + metricsMonth + '&year=' + metricsYear)
                .then(res => res.json())
                .then(data => {
                    metricsChart.data.labels = data.labels;
                    metricsChart.data.datasets[0].data = data.event_created;
                    metricsChart.data.datasets[1].data = data.registrants;
                    metricsChart.update();
                    createCustomLegend(metricsChart);
                });
        }

        document.getElementById('prevMetricsMonthBtn').addEventListener('click', () => {
            metricsMonth--;
            if (metricsMonth < 1) {
                metricsMonth = 12;
                metricsYear--;
            }
            setMetricsMonthLabel();
            updateMetricsChart();
        });
        document.getElementById('nextMetricsMonthBtn').addEventListener('click', () => {
            const now = new Date();
            if (metricsYear < now.getFullYear() || (metricsYear === now.getFullYear() && metricsMonth < now.getMonth() + 1)) {
                metricsMonth++;
                if (metricsMonth > 12) {
                    metricsMonth = 1;
                    metricsYear++;
                }
                setMetricsMonthLabel();
                updateMetricsChart();
            }
        });
        setMetricsMonthLabel();
        updateMetricsChart();
    </script>
</body>

</html>