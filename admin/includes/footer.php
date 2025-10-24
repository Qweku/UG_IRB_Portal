  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    console.log('Bootstrap JS loaded');
    // Test if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
      console.log('Bootstrap is available');
    } else {
      console.error('Bootstrap is not available');
    }
  </script>
  <script>
    // Generic Menu System for IRB Portal
    class MenuSystem {
        constructor() {
            this.activeSection = 'dashboard-content';
            this.init();
        }

        init() {
            this.bindEvents();
            this.ensureSidebarVisibility();
            window.addEventListener('resize', () => this.ensureSidebarVisibility());
            this.showContent(this.activeSection);
        }

        bindEvents() {
            // Handle main navigation links (Dashboard)
            const mainNavLinks = document.querySelectorAll('.sidebar .nav-link:not(.submenu-link)');
            mainNavLinks.forEach(link => {
                link.addEventListener('click', (e) => this.handleMainNavClick(e, link));
            });

            // Handle submenu links
            const submenuLinks = document.querySelectorAll('.submenu-link');
            submenuLinks.forEach(link => {
                link.addEventListener('click', (e) => this.handleSubmenuClick(e, link));
            });

            // Handle accordion buttons
            const accordionButtons = document.querySelectorAll('.accordion-button');
            accordionButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    console.log('Accordion button clicked:', button);
                    // Let Bootstrap handle the accordion toggle naturally
                });
            });

            // Sidebar toggle button (mobile)
            const sidebarToggle = document.querySelector('[data-bs-toggle="collapse"][data-bs-target="#sidebar"]');
            if (sidebarToggle) {
                console.log('Sidebar toggle button found:', sidebarToggle);
                sidebarToggle.addEventListener('click', (e) => {
                    console.log('Sidebar toggle clicked');
                    e.preventDefault(); // Prevent default Bootstrap behavior
                    this.handleSidebarToggle();
                });
            } else {
                console.log('Sidebar toggle button not found');
            }

            // Sidebar toggle handled by Bootstrap data API; no manual binding required
        }

        handleMainNavClick(e, link) {
            e.preventDefault();
            this.setActiveLink(link);
            const targetId = link.getAttribute('data-target');
            this.showContent(targetId);
        }

        handleSubmenuClick(e, link) {
            e.preventDefault();
            this.setActiveSubmenuLink(link);
            const targetId = link.getAttribute('data-target');
            this.showContent(targetId);
        }

        handleSidebarToggle() {
            console.log('handleSidebarToggle called');
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                console.log('Sidebar element found:', sidebar.classList);
                try {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(sidebar, { toggle: false });
                    console.log('Bootstrap Collapse instance created');
                    bsCollapse.toggle();
                    console.log('Sidebar toggle executed');
                } catch (error) {
                    console.error('Error with Bootstrap Collapse:', error);
                }
            } else {
                console.log('Sidebar element not found');
            }
        }

        ensureSidebarVisibility() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            if (window.innerWidth >= 768) {
                sidebar.classList.add('show');
            } else {
                sidebar.classList.remove('show');
            }
        }

        setActiveLink(activeLink) {
            // Remove active class from all main nav links
            const allLinks = document.querySelectorAll('.sidebar .nav-link:not(.submenu-link)');
            allLinks.forEach(link => link.classList.remove('active'));

            // Add active class to clicked link
            activeLink.classList.add('active');

            // Remove active class from all submenu links
            const submenuLinks = document.querySelectorAll('.submenu-link');
            submenuLinks.forEach(link => link.classList.remove('active'));
        }

        setActiveSubmenuLink(activeLink) {
            // Remove active class from all submenu links
            const submenuLinks = document.querySelectorAll('.submenu-link');
            submenuLinks.forEach(link => link.classList.remove('active'));

            // Add active class to clicked submenu link
            activeLink.classList.add('active');

            // Remove active class from main nav links
            const mainLinks = document.querySelectorAll('.sidebar .nav-link:not(.submenu-link)');
            mainLinks.forEach(link => link.classList.remove('active'));
        }

        showContent(contentId) {
            // Hide all content sections
            const contentSections = document.querySelectorAll('.content-section');
            contentSections.forEach(section => {
                section.style.display = 'none';
            });

            // Show the target content section
            const targetSection = document.getElementById(contentId);
            if (targetSection) {
                targetSection.style.display = 'block';
                this.activeSection = contentId;
            }

            // On mobile, close the sidebar after clicking a menu item
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth < 768 && sidebar.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(sidebar, { toggle: false });
                bsCollapse.hide();
            }
        }

        // Generic method to add new menu items
        addMenuItem(sectionId, menuData) {
            const section = document.getElementById(sectionId);
            if (!section) return;

            const submenuNav = section.querySelector('.submenu-nav') || this.createSubmenuNav(section);

            const menuItem = document.createElement('li');
            menuItem.className = 'nav-item';

            const menuLink = document.createElement('a');
            menuLink.className = 'nav-link submenu-link';
            menuLink.href = '#';
            menuLink.setAttribute('data-target', menuData.target);
            menuLink.innerHTML = `
                <i class="${menuData.icon} me-2"></i>${menuData.title}
            `;

            menuLink.addEventListener('click', (e) => this.handleSubmenuClick(e, menuLink));

            menuItem.appendChild(menuLink);
            submenuNav.appendChild(menuItem);
        }

        createSubmenuNav(section) {
            const submenuNav = document.createElement('ul');
            submenuNav.className = 'nav flex-column submenu-nav';
            section.querySelector('.accordion-body').appendChild(submenuNav);
            return submenuNav;
        }

        // Method to dynamically add new sections
        addSection(sectionData) {
            const accordion = document.getElementById('sidebarAccordion');

            const accordionItem = document.createElement('div');
            accordionItem.className = 'accordion-item';

            accordionItem.innerHTML = `
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed sidebar-accordion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#${sectionData.id}Collapse" aria-expanded="false" aria-controls="${sectionData.id}Collapse">
                        <i class="${sectionData.icon} me-2"></i>${sectionData.title}
                    </button>
                </h2>
                <div id="${sectionData.id}Collapse" class="accordion-collapse collapse" data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body p-0">
                        <ul class="nav flex-column submenu-nav">
                            ${sectionData.items.map(item => `
                                <li class="nav-item">
                                    <a class="nav-link submenu-link" href="#" data-target="${item.target}">
                                        <i class="${item.icon} me-2"></i>${item.title}
                                    </a>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                </div>
            `;

            accordion.appendChild(accordionItem);

            // Re-bind events for new elements
            this.bindEvents();
        }
    }

    // Initialize the menu system when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing MenuSystem');
        try {
            window.menuSystem = new MenuSystem();
            console.log('MenuSystem initialized successfully');
        } catch (error) {
            console.error('Error initializing MenuSystem:', error);
        }
    });
  </script>
</body>
</html>