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

    .sidebar {
        min-width: 250px;
        max-width: 250px;
        background: rgba(43, 45, 66, 0.3) !important;
        backdrop-filter: blur(10px);
        color: #fff;
        border-radius: 10px;
        border: none;
        box-shadow: none;
        position: sticky;
        top: 1rem;
    }

    .sidebar .sidebar-header {
        color: var(--primary) !important;
        background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .sidebar .d-grid a {
        color: #fff;
        font-weight: 500;
        border-radius: 8px;
        transition: background 0.2s, color 0.2s;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .sidebar .d-grid a i {
        font-size: 1.25rem;
        min-width: 24px;
        text-align: center;
        transition: color 0.2s;
    }

    .sidebar .d-grid a.active,
    .sidebar .d-grid a:hover,
    .sidebar .d-grid a:focus {
        background: linear-gradient(90deg, var(--gradient-start) 0%, var(--gradient-end) 100%) !important;
        color: #fff !important;
        font-weight: bold !important;
    }

    .sidebar .d-grid a.active i,
    .sidebar .d-grid a:hover i,
    .sidebar .d-grid a:focus i {
        color: #fff;
    }
</style>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<!-- Sidebar -->
<div class="col-md-4 col-lg-3">
    <div class="sidebar rounded shadow">
        <h5 class="sidebar-header fw-bold p-3 text-white w-100 border-bottom fs-4">
            <i class="fa fa-user mx-2"></i> Organizer
        </h5>
        <div class="d-grid gap-2 p-3 pt-0">
            <a href="dashboard.php"
                class="d-flex align-items-center text-decoration-none text-white py-2 px-2 rounded hover-shadow<?php echo $current_page == 'dashboard.php' ? ' active' : ''; ?>">
                <i class="fa fa-home me-2"></i> Overview
            </a>
            <a href="eventlists.php"
                class="d-flex align-items-center text-decoration-none text-white py-2 px-2 rounded hover-shadow<?php echo $current_page == 'eventlists.php' ? ' active' : ''; ?>">
                <i class="fa fa-calendar-plus me-2"></i> Events
            </a>
            <a href="registrants.php"
                class="d-flex align-items-center text-decoration-none text-white py-2 px-2 rounded hover-shadow<?php echo $current_page == 'registrants.php' ? ' active' : ''; ?>">
                <i class="fa fa-users me-2"></i> Registrants
            </a>
            <a href="registrations.php"
                class="d-flex align-items-center text-decoration-none text-white py-2 px-2 rounded hover-shadow<?php echo $current_page == 'registrations.php' ? ' active' : ''; ?>">
                <i class="fa fa-list me-2"></i> Registrations
            </a>
            <a href="inbox.php"
                class="d-flex align-items-center text-decoration-none text-white py-2 px-2 rounded hover-shadow<?php echo in_array($current_page, ['inbox.php', 'create.php', 'view-msg.php']) ? ' active' : ''; ?>">
                <i class="fa fa-envelope me-2"></i> Inbox
            </a>
            <a href="account.php"
                class="d-flex align-items-center text-decoration-none text-white py-2 px-2 rounded hover-shadow<?php echo $current_page == 'account.php' ? ' active' : ''; ?>">
                <i class="fa fa-user me-2"></i> Account
            </a>
        </div>
    </div>
</div>