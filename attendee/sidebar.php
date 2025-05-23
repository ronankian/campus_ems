<style>
    .sidebar {
        min-width: 250px;
        max-width: 250px;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .sidebar .d-grid a:hover,
    .sidebar .d-grid a:focus,
    .sidebar .d-grid a.active {
        background: #6665ee !important;
        /* Orange background */
        color: #fff !important;
        /* White text */
        font-weight: bold !important;
        /* Bold text */
        border-radius: 6px;
        /* Optional: rounded corners for a nice effect */
    }
</style>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<!-- Sidebar -->
<div class="col-md-4 col-lg-3">
    <div class="sidebar rounded shadow position-sticky" style="font-weight: 500; top: 2rem;">
        <h5 class="fw-bold p-2 text-black text-center w-100 border-bottom">
            Attendee Dashboard
        </h5>
        <div class="d-grid gap-2 p-3 pt-0">
            <a href="dashboard.php"
                class="d-flex align-items-center text-decoration-none text-black py-2 px-2 rounded hover-shadow<?php echo $current_page == 'dashboard.php' ? ' active' : ''; ?>">
                <i class="fa fa-home me-2"></i> Overview
            </a>
            <a href="eventlists.php"
                class="d-flex align-items-center text-decoration-none text-black py-2 px-2 rounded hover-shadow<?php echo $current_page == 'eventlists.php' ? ' active' : ''; ?>">
                <i class="fa fa-list me-2"></i> Registrations
            </a>
            <a href="inbox.php"
                class="d-flex align-items-center text-decoration-none text-black py-2 px-2 rounded hover-shadow<?php echo in_array($current_page, ['inbox.php', 'create.php', 'view-msg.php']) ? ' active' : ''; ?>">
                <i class="fa fa-envelope me-2"></i> Inbox
            </a>
            <a href="account.php"
                class="d-flex align-items-center text-decoration-none text-black py-2 px-2 rounded hover-shadow<?php echo $current_page == 'account.php' ? ' active' : ''; ?>">
                <i class="fa fa-user me-2"></i> Account
            </a>
        </div>
    </div>
</div>