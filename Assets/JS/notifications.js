document.addEventListener('DOMContentLoaded', function() {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationCount = document.getElementById('notificationCount');
    const notificationList = document.getElementById('notificationList');
    const markAllRead = document.getElementById('markAllRead');

    if (!notificationBtn) return;

    // Helper to get absolute project base path
    function getBaseUrl() {
        const pathParts = window.location.pathname.split('/');
        // Find index of 'scrms' case-insensitively
        const scrmsIdx = pathParts.findIndex(part => part.toLowerCase() === 'scrms');
        
        if (scrmsIdx !== -1) {
            return window.location.origin + pathParts.slice(0, scrmsIdx + 1).join('/') + '/';
        }
        // Fallback to origin if not found
        return window.location.origin + '/';
    }

    const baseUrl = getBaseUrl();

    // Toggle Dropdown
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.classList.toggle('active');
        fetchNotifications();
    });

    // Close on click outside
    document.addEventListener('click', function() {
        notificationDropdown.classList.remove('active');
    });

    notificationDropdown.addEventListener('click', e => e.stopPropagation());

    // Mark All as Read
    markAllRead.addEventListener('click', function() {
        markAsRead(null);
    });

    // Initial Fetch
    fetchCount();
    
    // Poll for new notifications every 30 seconds
    setInterval(fetchCount, 30000);

    function fetchCount() {
        fetch(baseUrl + 'Includes/handle-notifications.php?action=fetch')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateBadge(data.unreadCount);
                }
            })
            .catch(err => console.error('Fetch count error:', err));
    }

    function fetchNotifications() {
        notificationList.innerHTML = '<li class="no-notifications">Loading...</li>';
        fetch(baseUrl + 'Includes/handle-notifications.php?action=fetch')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderNotifications(data.notifications);
                    updateBadge(data.unreadCount);
                }
            })
            .catch(err => {
                notificationList.innerHTML = '<li class="no-notifications">Error loading notifications</li>';
                console.error('Fetch notifications error:', err);
            });
    }

    function renderNotifications(notifications) {
        if (!notifications || notifications.length === 0) {
            notificationList.innerHTML = '<li class="no-notifications">No notifications found</li>';
            return;
        }

        notificationList.innerHTML = '';
        notifications.forEach(notif => {
            const li = document.createElement('li');
            li.className = `notification-item ${notif.status}`;
            li.innerHTML = `
                <div class="notification-text">${notif.message}</div>
                <div class="notification-time">${notif.created_at ? formatTimeDiff(notif.created_at) : ''}</div>
            `;
            li.addEventListener('click', () => {
                const complaint_id = notif.complaint_id;
                console.log(`Notification clicked. Complaint ID: ${complaint_id}`);
                
                const pathParts = window.location.pathname.split('/');
                let targetUrl = '';
                
                // Determine if Admin or User directory
                if (pathParts.some(p => p.toLowerCase() === 'admin')) {
                    targetUrl = baseUrl + 'Admin/complaint-management.php';
                } else if (pathParts.some(p => p.toLowerCase() === 'user')) {
                    targetUrl = baseUrl + 'User/user-complaints.php';
                } else {
                    // Default fallback
                    targetUrl = baseUrl + 'User/user-complaints.php';
                }

                if (complaint_id) {
                    targetUrl += `?id=${complaint_id}`;
                }

                console.log('Final target URL:', targetUrl);
                
                markAsRead(notif.id).finally(() => {
                    // Check if target is same as current page with different query
                    const currentPath = window.location.pathname.toLowerCase();
                    const targetPath = targetUrl.toLowerCase();
                    
                    if (targetPath.includes(currentPath) && targetUrl.includes('?')) {
                        // Different query on same page - force a reload to trigger auto-open
                        window.location.href = targetUrl;
                        window.location.reload(); 
                    } else {
                        window.location.href = targetUrl;
                    }
                });
            });
            notificationList.appendChild(li);
        });
    }

    function updateBadge(count) {
        if (!notificationCount) return;
        notificationCount.textContent = count > 9 ? '9+' : count;
        notificationCount.style.display = count > 0 ? 'flex' : 'none';
    }

    function markAsRead(id) {
        let url = baseUrl + 'Includes/handle-notifications.php?action=mark_read';
        if (id) url += `&id=${id}`;

        return fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (notificationDropdown && notificationDropdown.classList.contains('active')) {
                        fetchNotifications();
                    }
                    fetchCount();
                }
                return data;
            })
            .catch(err => {
                console.error('Mark as read error:', err);
                throw err;
            });
    }

    function formatTimeDiff(timestamp) {
        const now = new Date();
        const past = new Date(timestamp);
        const diffInSecs = Math.floor((now - past) / 1000);

        if (diffInSecs < 60) return 'Just now';
        
        const diffInMins = Math.floor(diffInSecs / 60);
        if (diffInMins < 60) return `${diffInMins}m ago`;
        
        const diffInHours = Math.floor(diffInMins / 60);
        if (diffInHours < 24) return `${diffInHours}h ago`;
        
        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 7) return `${diffInDays}d ago`;
        
        return past.toLocaleDateString();
    }
});
