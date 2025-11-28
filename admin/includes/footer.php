  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
      // Generic Menu System for IRB Portal
      class MenuSystem {
          constructor() {
              this.activeSection = localStorage.getItem('activeSection') || 'dashboard-content';
              this.init();
          }

          init() {
              this.bindEvents();
              this.ensureSidebarVisibility();
              window.addEventListener('resize', () => this.ensureSidebarVisibility());
              this.showContent(this.activeSection);
              this.setActiveLinkBySection(this.activeSection); // set sidebar active class
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
                      // Let Bootstrap handle the accordion toggle naturally
                  });
              });

              // Sidebar toggle button (mobile)
              const sidebarToggle = document.querySelector('[data-bs-toggle="collapse"][data-bs-target="#sidebar"]');
              if (sidebarToggle) {
                  sidebarToggle.addEventListener('click', (e) => {
                      e.preventDefault(); // Prevent default Bootstrap behavior
                      this.handleSidebarToggle();
                  });
              }

              // Sidebar toggle handled by Bootstrap data API; no manual binding required
          }

          handleMainNavClick(e, link) {
              e.preventDefault();
              this.setActiveLink(link);
              const targetId = link.getAttribute('data-target');

              if (targetId) {
                  this.showContent(targetId);
                  // Save current section in localStorage
                  localStorage.setItem('activeSection', targetId);
              }
          }

          handleSubmenuClick(e, link) {
              e.preventDefault();
              this.setActiveSubmenuLink(link);
              const targetId = link.getAttribute('data-target');
              if (targetId) {
                  this.showContent(targetId);
                  // Save current section in localStorage
                  localStorage.setItem('activeSection', targetId);
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

          setActiveLinkBySection(sectionId) {
              // Check main nav links
              const mainLink = document.querySelector(`.sidebar .nav-link[data-target="${sectionId}"]`);
              if (mainLink) {
                  this.setActiveLink(mainLink);
                  return;
              }

              // Check submenu links
              const submenuLink = document.querySelector(`.sidebar .submenu-link[data-target="${sectionId}"]`);
              if (submenuLink) {
                  this.setActiveSubmenuLink(submenuLink);
              }
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
                  const bsCollapse = bootstrap.Collapse.getOrCreateInstance(sidebar, {
                      toggle: false
                  });
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



      }

      // Initialize the menu system when DOM is loaded
      document.addEventListener('DOMContentLoaded', function() {
          try {
              window.menuSystem = new MenuSystem();
          } catch (error) {
              console.error('Error initializing MenuSystem:', error);
          }
      });
  </script>
  </body>

  </html>