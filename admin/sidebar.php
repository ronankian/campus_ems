<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
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
        background: rgba(43, 45, 66, 0.3) !important;
        backdrop-filter: blur(10px);
        display: flex;
        flex-direction: column;
        transition: width 0.2s;
        color: #fff;
        border-radius: 0.5rem;
        position: sticky;
        top: 1rem;
        overflow-y: auto;
    }

    .sidebar.collapsed {
        width: 64px !important;
        min-width: 64px !important;
        max-width: 64px !important;
    }

    .sidebar .sidebar-header {
        color: var(--primary) !important;
        background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: flex;
        align-items: center;
        padding: 0.75rem 1.2rem;
        font-weight: bold;
        border-bottom: 1px solid #343a40;
        margin: 0 0.5rem;
        min-height: 64px;
        cursor: pointer;
        user-select: none;
    }

    .sidebar .toggle-btn {
        display: none;
    }

    .sidebar .nav {
        flex-direction: column;
        padding: 1rem 0;
        gap: 0.5rem;
        width: 100%;
    }

    .sidebar .sidebar-link {
        color: #fff;
        padding: 0.75rem 1.2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border-radius: 8px;
        margin: 0 0.5rem;
        transition: background 0.2s, color 0.2s;
        font-weight: 500;
        white-space: nowrap;
        text-decoration: none;
    }

    .sidebar .sidebar-link i {
        font-size: 1.25rem;
        min-width: 24px;
        text-align: center;
        transition: color 0.2s;
    }

    .sidebar .sidebar-link.active,
    .sidebar .sidebar-link:hover {
        background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
        color: #fff;
    }

    .sidebar .sidebar-link.active i,
    .sidebar .sidebar-link:hover i {
        color: #fff;
    }

    .sidebar .sidebar-link .label {
        transition: opacity 0.2s, width 0.2s;
        overflow: hidden;
    }

    .sidebar.collapsed .sidebar-link .label {
        opacity: 0;
        width: 0;
        padding: 0;
    }

    .sidebar.collapsed .sidebar-link {
        justify-content: center;
        padding-right: 0.5rem;
    }

    .sidebar.collapsed .sidebar-header {
        justify-content: center;
        padding-right: 0.5rem;
    }


    .sidebar .sidebar-header span {
        transition: opacity 0.1s, width 0.2s;
        display: inline-block;
        overflow: hidden;
        white-space: nowrap;
    }

    .sidebar.collapsed .sidebar-header span {
        opacity: 0;
        width: 0;
        padding: 0;
    }
</style>
<div class="sidebar col-lg-2 collapsed mb-4" id="sidebar">
    <div class="sidebar-header" id="sidebarHeader">
        <i class="fa fa-user-tie fs-3"></i>
        <span class="ms-3 fs-3">Admin</span>
    </div>
    <nav class="nav flex-column mt-2">
        <a class="sidebar-link<?php echo $current_page == 'dashboard.php' ? ' active' : ''; ?>" href="dashboard.php">
            <i class="fa fa-home"></i>
            <span class="label">Overview</span>
        </a>
        <a class="sidebar-link<?php echo $current_page == 'inbox.php' ? ' active' : ''; ?>" href="inbox.php">
            <i class="fa fa-envelope"></i>
            <span class="label">Inbox</span>
        </a>
        <a class="sidebar-link<?php echo $current_page == 'eventlists.php' ? ' active' : ''; ?>" href="eventlists.php">
            <i class="fa fa-calendar-days"></i>
            <span class="label">Events</span>
        </a>
        <a class="sidebar-link<?php echo $current_page == 'registrations.php' ? ' active' : ''; ?>"
            href="registrations.php">
            <i class="fa fa-clipboard-check"></i>
            <span class="label">Registrations</span>
        </a>
        <a class="sidebar-link<?php echo $current_page == 'users.php' ? ' active' : ''; ?>" href="users.php">
            <i class="fa fa-users"></i>
            <span class="label">Users</span>
        </a>
        <a class="sidebar-link<?php echo $current_page == 'activities.php' ? ' active' : ''; ?>" href="activities.php">
            <i class="fa fa-clock-rotate-left"></i>
            <span class="label">Activites</span>
        </a>
    </nav>
</div>
<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarHeader = document.getElementById('sidebarHeader');
    sidebarHeader.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');
    });
</script>