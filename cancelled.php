<?php
include 'login/connection.php';
// Fetch 3 most recently cancelled events, ordered by date_cancelled DESC
$query = "SELECT * FROM create_events WHERE status = 'cancelled' AND date_cancelled IS NOT NULL ORDER BY date_cancelled DESC LIMIT 3";
$result = mysqli_query($con, $query);
?>
<div class="col-md-12">
    <div>
        <div class="sidebar-recent-heading border-bottom-0 pt-2 mb-1">Recently Cancelled</div>
        <ul class="list-unstyled">
            <?php if (mysqli_num_rows($result) === 0): ?>
                <li>
                    <div class="text-muted text-center py-4">No recently cancelled events found.</div>
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
                                <small class="text-white-50">
                                    <?php echo date('F d, Y \| h:i A', strtotime($row['date_cancelled'])); ?>
                                </small>
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
</style>