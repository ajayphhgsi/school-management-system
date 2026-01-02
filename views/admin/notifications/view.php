<?php
$active_page = 'notifications';
$page_title = 'View Notifications';
ob_start();

// Helper functions
function getBootstrapColor($type) {
    $colors = [
        'info' => 'primary',
        'warning' => 'warning',
        'success' => 'success',
        'danger' => 'danger'
    ];
    return $colors[$type] ?? 'primary';
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-bell text-primary me-2"></i>Notifications</h4>
        <p class="text-muted mb-0">View all your notifications and updates</p>
    </div>
    <div>
        <a href="/admin/notifications" class="btn btn-outline-primary">
            <i class="fas fa-paper-plane me-2"></i>Send Notification
        </a>
    </div>
</div>

<!-- Notifications List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Notifications</h5>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-outline-secondary active" onclick="filterNotifications('all')">All</button>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="filterNotifications('unread')">Unread</button>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="filterNotifications('info')">Info</button>
            <button type="button" class="btn btn-sm btn-outline-warning" onclick="filterNotifications('warning')">Warning</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="notificationsList">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No notifications yet</h5>
                    <p class="text-muted">You'll see notifications here when important events occur.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <a href="#" class="list-group-item list-group-item-action py-3 notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" data-type="<?php echo $notification['type']; ?>" data-id="<?php echo $notification['id']; ?>">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-<?php echo getBootstrapColor($notification['type']); ?> bg-opacity-10 rounded-circle p-2">
                                    <i class="<?php echo $notification['icon']; ?> text-<?php echo getBootstrapColor($notification['type']); ?>"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($notification['title']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($notification['message']); ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo timeAgo($notification['created_at']); ?></small>
                                </div>
                            </div>
                            <?php if (!$notification['is_read']): ?>
                                <div class="flex-shrink-0 ms-2">
                                    <span class="badge bg-primary rounded-pill">New</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-footer text-center">
        <button class="btn btn-outline-primary" onclick="loadMoreNotifications()">
            <i class="fas fa-chevron-down me-2"></i>Load More
        </button>
    </div>
</div>

<script>
function filterNotifications(type) {
    const items = document.querySelectorAll('.notification-item');
    const buttons = document.querySelectorAll('.btn-group .btn');

    // Update button states
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');

    // Filter items
    items.forEach(item => {
        if (type === 'all') {
            item.style.display = 'block';
        } else if (type === 'unread') {
            item.style.display = item.classList.contains('unread') ? 'block' : 'none';
        } else {
            item.style.display = item.dataset.type === type ? 'block' : 'none';
        }
    });
}

function loadMoreNotifications() {
    // In a real app, this would load more from server
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';

    setTimeout(() => {
        button.disabled = false;
        button.innerHTML = originalText;
        // For demo, just show alert
        alert('In a real application, this would load more notifications from the server.');
    }, 1000);
}

// Mark notification as read when clicked
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationId = this.dataset.id;
            const isUnread = this.classList.contains('unread');

            if (isUnread && notificationId) {
                // Mark as read via AJAX
                fetch('/admin/notifications/mark-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ notification_id: notificationId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.remove('unread');
                        const badge = this.querySelector('.badge');
                        if (badge) {
                            badge.remove();
                        }
                        // Update unread count in header if it exists
                        updateUnreadCount();
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
            }
        });
    });
});

function updateUnreadCount() {
    // Update the notification badge in the header
    const badge = document.querySelector('.navbar .badge');
    if (badge) {
        const currentCount = parseInt(badge.textContent) || 0;
        if (currentCount > 0) {
            const newCount = currentCount - 1;
            if (newCount > 0) {
                badge.textContent = newCount;
            } else {
                badge.style.display = 'none';
            }
        }
    }
}
</script>

<style>
.notification-item.unread {
    background-color: rgba(102, 126, 234, 0.02);
    border-left: 4px solid #667eea;
}

.notification-item:hover {
    background-color: rgba(248, 249, 250, 0.8);
}
</style>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>