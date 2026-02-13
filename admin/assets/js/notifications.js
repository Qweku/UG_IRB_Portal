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
    function init() {
        if (!notificationBtn || !notificationPanel) {
            console.warn('Notification elements not found');
            return;
        }

        // Load notifications from storage or use default
        loadNotifications();

        // Event listeners
        setupEventListeners();

        // Render initial state
        renderNotifications();
        updateBadge();
        updatePulseAnimation();
    }

    /**
     * Load notifications from localStorage or use defaults
     */
    function loadNotifications() {
        const stored = localStorage.getItem('ug_irb_notifications');
        if (stored) {
            try {
                notifications = JSON.parse(stored);
            } catch (e) {
                console.error('Error parsing notifications:', e);
                notifications = getDefaultNotifications();
            }
        } else {
            notifications = getDefaultNotifications();
        }
        
        // Calculate unread count
        unreadCount = notifications.filter(n => !n.read).length;
    }

    /**
     * Get default notifications for demo purposes
     */
    function getDefaultNotifications() {
        return [
            {
                id: 1,
                title: "New Application Received",
                message: "Study protocol #2024-001 has been submitted for review by Dr. John Smith.",
                time: "2 minutes ago",
                read: false,
                type: "application",
                details: "This is a new research application seeking approval for a clinical trial on diabetes treatment. Please review the submitted documents and assign reviewers.",
                actions: [
                    { text: "Review", primary: true, action: "review" },
                    { text: "Assign Reviewers", primary: false, action: "assign" }
                ]
            },
            {
                id: 2,
                title: "Review Completed",
                message: "Dr. Jane Doe has completed the review for protocol #2023-156.",
                time: "1 hour ago",
                read: false,
                type: "review",
                details: "The review has been submitted with the following recommendations: Approved with minor revisions. Please review the comments and prepare the response letter.",
                actions: [
                    { text: "View Review", primary: true, action: "view" },
                    { text: "Prepare Response", primary: false, action: "respond" }
                ]
            },
            {
                id: 3,
                title: "Upcoming IRB Meeting",
                message: "The next IRB meeting is scheduled for next Tuesday at 10:00 AM.",
                time: "3 hours ago",
                read: true,
                type: "meeting",
                details: "Agenda items include: 5 new applications, 3 continuing reviews, and 2 protocol amendments. Please ensure all materials are prepared by Friday.",
                actions: [
                    { text: "View Agenda", primary: true, action: "agenda" },
                    { text: "Add Items", primary: false, action: "add" }
                ]
            },
            {
                id: 4,
                title: "System Maintenance Scheduled",
                message: "The system will undergo maintenance this weekend.",
                time: "Yesterday",
                read: true,
                type: "system",
                details: "Scheduled maintenance will occur from Saturday 10 PM to Sunday 6 AM. During this time, the system will be unavailable. Please save your work before the maintenance window.",
                actions: [
                    { text: "Acknowledge", primary: true, action: "acknowledge" }
                ]
            }
        ];
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
    function markAllAsRead() {
        notifications.forEach(n => n.read = true);
        unreadCount = 0;
        saveNotifications();
        renderNotifications();
        updateBadge();
        updatePulseAnimation();
    }

    /**
     * Mark a single notification as read
     * @param {number} id - Notification ID
     */
    function markAsRead(id) {
        const notification = notifications.find(n => n.id === id);
        if (notification && !notification.read) {
            notification.read = true;
            unreadCount--;
            saveNotifications();
            updateBadge();
            updatePulseAnimation();
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
        
        // Re-render to update read state
        if (notificationList) {
            const notification = notifications.find(n => n.id === id);
            if (notification) {
                renderNotificationItem(notification, notificationList);
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
