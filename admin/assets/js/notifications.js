/**
 * Notification System JavaScript
 * Handles notification panel toggle, expansion, and management
 */

(function() {
    'use strict';

    // Notification data store
    let notifications = [];
    let unreadCount = 0;

    // DOM Elements
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationPanel = document.getElementById('notificationPanel');
    const notificationOverlay = document.getElementById('notificationOverlay');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const closeNotification = document.getElementById('closeNotification');

    /**
     * Initialize the notification system
     */
    async function init() {
        if (!notificationBtn || !notificationPanel) {
            console.warn('Notification elements not found');
            return;
        }
        console.log("Loading Notifications");
        // Load notifications from storage or fetch from database
        await loadNotifications();

        // Event listeners
        setupEventListeners();

        // Render initial state
        renderNotifications();
        updateBadge();
        updatePulseAnimation();
    }

    /**
     * Load notifications from localStorage or fetch from database
     */
    async function loadNotifications() {
        const stored = localStorage.getItem('ug_irb_notifications');
       
            try {
                notifications = await getAllNotifications();
            } catch (e) {
                console.error('Error parsing notifications:', e);
                notifications = []
            }
        // else {
        //     notifications = []
        // }
        
        // Calculate unread count
        unreadCount = notifications.filter(n => !n.read).length;
    }

    /**
     * Fetch notifications from the database
     * @returns {Promise<Array>} Array of notifications
     */
    async function getAllNotifications() {
        try {
            const response = await fetch('/admin/handlers/fetch_notifications.php');
            const data = await response.json();
            
            if (data.status === 'success' && data.notifications && data.notifications.length > 0) {
                return data.notifications;
            } else {
                console.warn('No notifications found or fetch failed');
                return [];
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
            return [];
        }
    }

   

    /**
     * Save notifications to localStorage
     */
    function saveNotifications() {
        localStorage.setItem('ug_irb_notifications', JSON.stringify(notifications));
    }

    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        // Toggle notification panel
        if (notificationBtn) {
            notificationBtn.addEventListener('click', togglePanel);
        }

        // Close notification panel
        if (closeNotification) {
            closeNotification.addEventListener('click', closePanel);
        }

        // Close on overlay click
        if (notificationOverlay) {
            notificationOverlay.addEventListener('click', closePanel);
        }

        // Mark all as read
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', markAllAsRead);
        }

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePanel();
            }
        });
    }

    /**
     * Toggle notification panel
     */
    function togglePanel() {
        if (notificationPanel && notificationOverlay) {
            notificationPanel.classList.toggle('open');
            notificationOverlay.classList.toggle('show');
            
            // Prevent body scroll when panel is open
            document.body.style.overflow = notificationPanel.classList.contains('open') ? 'hidden' : '';
        }
    }

    /**
     * Close notification panel
     */
    function closePanel() {
        if (notificationPanel && notificationOverlay) {
            notificationPanel.classList.remove('open');
            notificationOverlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    /**
     * Update notification badge count
     */
    function updateBadge() {
        if (notificationBadge) {
            notificationBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            notificationBadge.setAttribute('data-count', unreadCount);
            
            if (unreadCount > 0) {
                notificationBadge.classList.add('show');
            } else {
                notificationBadge.classList.remove('show');
            }
        }
    }

    /**
     * Update pulsing glow animation
     */
    function updatePulseAnimation() {
        if (notificationBtn) {
            if (unreadCount > 0) {
                notificationBtn.classList.add('has-unread');
            } else {
                notificationBtn.classList.remove('has-unread');
            }
        }
    }

    /**
     * Mark all notifications as read
     */
    async function markAllAsRead() {
        // Update local state
        notifications.forEach(n => n.read = true);
        unreadCount = 0;
        saveNotifications();
        renderNotifications();
        updateBadge();
        updatePulseAnimation();
        
        // Sync with database
        try {
            await fetch('/admin/handlers/mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'mark_all=true'
            });
        } catch (error) {
            console.error('Error syncing read status to database:', error);
        }
    }

    /**
     * Mark a single notification as read
     * @param {number} id - Notification ID
     */
    async function markAsRead(id) {
        const notification = notifications.find(n => n.id === id);
        if (notification && !notification.read) {
            notification.read = true;
            unreadCount--;
            saveNotifications();
            updateBadge();
            updatePulseAnimation();
            
            // Sync with database
            try {
                await fetch('/admin/handlers/mark_notification_read.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'notification_id=' + id
                });
            } catch (error) {
                console.error('Error syncing read status to database:', error);
            }
        }
    }

    /**
     * Toggle notification expansion
     * @param {number} id - Notification ID
     */
    function toggleExpand(id) {
        const details = document.getElementById(`notification-details-${id}`);
        const item = document.getElementById(`notification-item-${id}`);
        
        if (details) {
            details.classList.toggle('show');
        }
        
        // Mark as read when expanded
        markAsRead(id);
        
        // Update the existing DOM element to reflect read state
        if (item) {
            const notification = notifications.find(n => n.id === id);
            if (notification && notification.read) {
                item.classList.remove('unread');
                // Remove unread indicator if it exists
                const indicator = item.querySelector('.unread-indicator');
                if (indicator) {
                    indicator.remove();
                }
            }
        }
    }

    /**
     * Handle notification action
     * @param {string} action - Action type
     * @param {number} id - Notification ID
     */
    function handleAction(action, id) {
        // Mark as read
        markAsRead(id);
        
        // Perform action
        switch (action) {
            case 'review':
                window.location.href = `/admin/pages/contents/review_content.php?id=${id}`;
                break;
            case 'assign':
                window.location.href = `/admin/pages/contents/assign_reviewers.php?id=${id}`;
                break;
            case 'view':
                window.location.href = `/admin/pages/contents/view_review.php?id=${id}`;
                break;
            case 'respond':
                window.location.href = `/admin/pages/contents/prepare_response.php?id=${id}`;
                break;
            case 'agenda':
                window.location.href = `/admin/pages/contents/preliminary_agenda_content.php`;
                break;
            case 'add':
                window.location.href = `/admin/pages/contents/add_agenda_item.php`;
                break;
            case 'acknowledge':
                closePanel();
                break;
            default:
                console.log('Unknown action:', action);
        }
    }

    /**
     * Get icon class based on notification type
     * @param {string} type - Notification type
     * @returns {string} FontAwesome icon class
     */
    function getIconClass(type) {
        const icons = {
            application: 'fa-file-alt',
            review: 'fa-clipboard-check',
            meeting: 'fa-calendar-alt',
            system: 'fa-cog'
        };
        return icons[type] || 'fa-bell';
    }

    /**
     * Render a single notification item
     * @param {object} notification - Notification data
     * @param {HTMLElement} container - Container element
     */
    function renderNotificationItem(notification, container) {
        const li = document.createElement('li');
        li.id = `notification-item-${notification.id}`;
        li.className = `notification-item ${notification.read ? '' : 'unread'}`;
        
        const iconClass = getIconClass(notification.type);
        
        li.innerHTML = `
            <div class="notification-content" onclick="Notifications.toggleExpand(${notification.id})">
                <div class="notification-icon ${notification.type}">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="notification-text">
                    <div class="notification-title">${escapeHtml(notification.title)}</div>
                    <div class="notification-message">${escapeHtml(notification.message)}</div>
                    <div class="notification-time">${escapeHtml(notification.time)}</div>
                </div>
                ${!notification.read ? '<div class="unread-indicator"></div>' : ''}
            </div>
            <div class="notification-details" id="notification-details-${notification.id}">
                <div class="notification-details-content">
                    <p>${escapeHtml(notification.details || notification.message)}</p>
                    ${notification.actions ? `
                        <div class="notification-actions">
                            ${notification.actions.map(action => `
                                <button 
                                    class="notification-btn-action ${action.primary ? 'primary' : 'secondary'}" 
                                    onclick="Notifications.handleAction('${action.action}', ${notification.id}); event.stopPropagation();"
                                >
                                    ${escapeHtml(action.text)}
                                </button>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        container.appendChild(li);
    }

    /**
     * Render all notifications
     */
    function renderNotifications() {
        if (!notificationList) return;
        
        notificationList.innerHTML = '';
        
        if (notifications.length === 0) {
            notificationList.innerHTML = `
                <li class="notification-empty">
                    <div class="notification-empty-icon">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <div class="notification-empty-text">No notifications yet</div>
                </li>
            `;
            return;
        }
        
        // Sort notifications: unread first, then by time
        const sorted = [...notifications].sort((a, b) => {
            if (a.read !== b.read) return a.read ? 1 : -1;
            return b.id - a.id; // Assuming higher ID = more recent
        });
        
        sorted.forEach(notification => {
            renderNotificationItem(notification, notificationList);
        });
    }

    /**
     * Escape HTML to prevent XSS
     * @param {string} text - Text to escape
     * @returns {string} Escaped text
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Add a new notification
     * @param {object} notification - Notification data
     */
    function addNotification(notification) {
        notifications.unshift({
            id: Date.now(),
            read: false,
            time: 'Just now',
            ...notification
        });
        unreadCount++;
        saveNotifications();
        renderNotifications();
        updateBadge();
        updatePulseAnimation();
    }

    /**
     * Refresh notifications from database (bypass localStorage)
     */
    async function refreshNotifications() {
        try {
            const response = await fetch('../handlers/fetch_notifications.php');
            const data = await response.json();
            
            if (data.status === 'success' && data.notifications) {
                notifications = data.notifications;
                unreadCount = data.unread_count || notifications.filter(n => !n.read).length;
                saveNotifications();
                renderNotifications();
                updateBadge();
                updatePulseAnimation();
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error refreshing notifications:', error);
            return false;
        }
    }

    /**
     * Clear all notifications
     */
    function clearAll() {
        notifications = [];
        unreadCount = 0;
        saveNotifications();
        renderNotifications();
        updateBadge();
        updatePulseAnimation();
    }

    // Expose public API
    window.Notifications = {
        togglePanel,
        closePanel,
        toggleExpand,
        markAsRead,
        markAllAsRead,
        handleAction,
        addNotification,
        clearAll,
        refreshNotifications,
        getNotifications: () => notifications,
        getUnreadCount: () => unreadCount
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
