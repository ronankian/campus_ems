<?php
include 'login/connection.php';
// Fetch 3 most recently ended events (not cancelled, already ended)
$now = date('Y-m-d H:i:s');
$query = "SELECT * FROM create_events WHERE (status IS NULL OR status != 'cancelled') AND date_time < '$now' ORDER BY date_time DESC LIMIT 3";
$result = mysqli_query($con, $query);
?>
<div class="col-md-12">
    <div>
        <div class="sidebar-recent-heading border-bottom-0 pt-2 mb-1">Recent Events</div>
        <ul class="list-unstyled">
            <?php if (mysqli_num_rows($result) === 0): ?>
                <li>
                    <div class="text-muted text-center py-4">No recently ended events found.</div>
                </li>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <li>
                        <a class="d-flex flex-row gap-3 align-items-center py-3 link-body-emphasis text-decoration-none border-top"
                            href="event-details.php?id=<?php echo $row['id']; ?>">
                            <div class="thumbnails bd-placeholder-img"
                                style="width: 100px; min-width: 100px; height: 96px; flex-shrink:0; display: flex; align-items: center; justify-content: center; background: #222;">
                                <?php
                                $img = null;
                                if (!empty($row['attach_file'])) {
                                    $files = json_decode($row['attach_file'], true);
                                    if (is_array($files) && count($files) > 0) {
                                        // Only show image files
                                        foreach ($files as $file) {
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                                $img = 'uploads/' . $file;
                                                break;
                                            }
                                        }
                                    }
                                }
                                if ($img): ?>
                                    <img src="<?php echo htmlspecialchars($img); ?>" width="100%" height="96"
                                        style="object-fit:cover; border-radius:4px;" alt="Event image">
                                <?php else: ?>
                                    <div
                                        style="width:100%; height:96px; background:#888; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:0.9rem; opacity:0.7; text-align:center; flex-direction:column; line-height:1.1;">
                                        No Image</div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-2 text-white"><?php echo htmlspecialchars($row['event_title']); ?></h6>
                                <small
                                    class="text-white-50"><?php echo date('F d, Y \| h:i A', strtotime($row['date_time'])); ?></small>
                            </div>
                        </a>
                    </li>
                <?php endwhile; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
<style>
    .sidebar-recent-heading {
        font-size: 1.25rem;
        font-weight: 700;
        font-style: italic;
        color: #fff;
    }

    .sidebar-recent-row {
        border-bottom: 1px solid #444;
        transition: background 0.15s;
        padding-left: 0;
        padding-right: 0;
    }

    .sidebar-recent-row:last-child {
        border-bottom: none;
    }

    .sidebar-recent-thumb {
        width: 56px;
        min-width: 56px;
        height: 56px;
        border-radius: 4px;
        overflow: hidden;
        background: #222;
    }

    .sidebar-recent-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
    }

    .sidebar-recent-noimg {
        width: 100%;
        height: 56px;
        background: #888;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.9rem;
        opacity: 0.7;
        text-align: center;
        flex-direction: column;
        line-height: 1.1;
    }

    .sidebar-recent-details {
        min-width: 0;
        flex: 1 1 0%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .sidebar-recent-title-ev {
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.2;
        white-space: normal;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-recent-date {
        color: #bdbdbd;
        font-size: 0.95rem;
        font-weight: 400;
        margin-top: 2px;
    }
</style>