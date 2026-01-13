  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <?php
    // Session timeout calculation for global modal
    $session_lifetime = ini_get('session.gc_maxlifetime');
    if (!isset($_SESSION['session_expire_time'])) {
        $_SESSION['session_expire_time'] = time() + $session_lifetime;
    }
    $time_remaining = $_SESSION['session_expire_time'] - time();
    ?>

  <script>
      window.sessionTimeout = <?php echo $time_remaining; ?>;
  </script>

  <script>
      document.addEventListener('DOMContentLoaded', () => {

          let warningTimer;
          let countdownInterval;

          function startWarningTimer() {
              const sessionDuration = window.sessionTimeout * 1000;
              const warningBefore = 60 * 1000;

              warningTimer = setTimeout(showWarningModal, sessionDuration - warningBefore);
          }

          function showWarningModal() {
              console.log('Showing session warning modal');
              const modalElement = document.getElementById('sessionTimeoutModal');
              console.log('Modal element:', modalElement);
              const modal = new bootstrap.Modal(modalElement);
              modal.show();

              let remaining = 60;
              const countdown = document.getElementById('countdown');
              console.log('Countdown element:', countdown);
              countdown.textContent = remaining;

              countdownInterval = setInterval(() => {
                  remaining--;
                  countdown.textContent = remaining;

                  if (remaining <= 0) {
                      clearInterval(countdownInterval);
                      window.location.href = '/logout';
                  }
              }, 1000);
          }

          // Attach these listeners ONCE
          document.getElementById('stayLoggedIn').addEventListener('click', () => {
              clearInterval(countdownInterval);
              const modal = bootstrap.Modal.getInstance(document.getElementById('sessionTimeoutModal'));
              modal.hide();
              extendSession();
          });

          document.getElementById('logoutNow').addEventListener('click', () => {
              window.location.href = '/logout';
          });

          function extendSession() {
              console.log('Extending session');
              fetch('/includes/config/extend_session.php')
                  .then(response => {
                      console.log('Extend session response:', response);
                      return response.json();
                  })
                  .then(data => {
                      console.log('Extend session data:', data);
                      if (data.status === 'ok') {
                          // Update session timeout with new remaining time
                          window.sessionTimeout = data.new_remaining;
                          console.log('Updated sessionTimeout to:', data.new_remaining);

                          // Reset timers
                          clearTimeout(warningTimer);
                          clearInterval(countdownInterval);

                          startWarningTimer();
                      }
                  })
                  .catch(err => console.error('Error extending session:', err));
          }

          // Start initial timer
          startWarningTimer();
      });
  </script>

  <script>
      // Generic Menu System for IRB Portal
      class MenuSystem {
          constructor(defaultSection = 'dashboard-content') {
              this.activeSection = defaultSection;
              this.init();
          }

          init() {
              this.bindEvents();
              this.ensureSidebarVisibility();

              window.addEventListener('resize', () => this.ensureSidebarVisibility());

              this.showContent(this.activeSection);
              this.setActiveLinkBySection(this.activeSection);
          }

          bindEvents() {
              // Main navigation links
              document.querySelectorAll('.sidebar .nav-link:not(.submenu-link)').forEach(link => {
                  link.addEventListener('click', e => this.handleMainNavClick(e, link));
              });

              // Submenu links
              document.querySelectorAll('.submenu-link').forEach(link => {
                  link.addEventListener('click', e => this.handleSubmenuClick(e, link));
              });
          }

          handleMainNavClick(e, link) {
              e.preventDefault();

              const targetId = link.dataset.target;
              if (!targetId) return;

              this.setActiveLink(link);
              this.showContent(targetId);
          }

          handleSubmenuClick(e, link) {
              e.preventDefault();

              const targetId = link.dataset.target;
              if (!targetId) return;

              this.setActiveSubmenuLink(link);
              this.showContent(targetId);
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
              this.clearAllActiveStates();
              activeLink.classList.add('active');
          }

          setActiveSubmenuLink(activeLink) {
              this.clearAllActiveStates();
              activeLink.classList.add('active');

              // Ensure parent accordion stays open
              const accordion = activeLink.closest('.accordion-collapse');
              if (accordion) {
                  accordion.classList.add('show');
              }
          }

          clearAllActiveStates() {
              document.querySelectorAll('.sidebar .nav-link').forEach(link =>
                  link.classList.remove('active')
              );
          }

          setActiveLinkBySection(sectionId) {
              const link = document.querySelector(
                  `.sidebar .nav-link[data-target="${sectionId}"],
         .sidebar .submenu-link[data-target="${sectionId}"]`
              );

              if (link) {
                  link.classList.contains('submenu-link') ?
                      this.setActiveSubmenuLink(link) :
                      this.setActiveLink(link);
              }
          }

          showContent(contentId) {
              document.querySelectorAll('.content-section').forEach(section => {
                  section.style.display = 'none';
              });

              const targetSection = document.getElementById(contentId);
              if (!targetSection) return;

              targetSection.style.display = 'block';
              this.activeSection = contentId;

              // Auto-close sidebar on mobile
              const sidebar = document.getElementById('sidebar');
              if (window.innerWidth < 768 && sidebar?.classList.contains('show')) {
                  bootstrap.Collapse.getOrCreateInstance(sidebar, {
                      toggle: false
                  }).hide();
              }
          }

          /* ----- Optional Dynamic Menu Support ----- */

          addMenuItem(sectionId, menuData) {
              const section = document.getElementById(sectionId);
              if (!section) return;

              const submenuNav = section.querySelector('.submenu-nav') || this.createSubmenuNav(section);

              const li = document.createElement('li');
              li.className = 'nav-item';

              const a = document.createElement('a');
              a.href = '#';
              a.className = 'nav-link submenu-link';
              a.dataset.target = menuData.target;
              a.innerHTML = `<i class="${menuData.icon} me-2"></i>${menuData.title}`;

              a.addEventListener('click', e => this.handleSubmenuClick(e, a));

              li.appendChild(a);
              submenuNav.appendChild(li);
          }

          createSubmenuNav(section) {
              const nav = document.createElement('ul');
              nav.className = 'nav flex-column submenu-nav';
              section.querySelector('.accordion-body')?.appendChild(nav);
              return nav;
          }
      }


      // Session Timer Function
      function updateSessionTimer() {
          console.log('updateSessionTimer called');
          let remaining;
          if (typeof window.sessionTimeout !== 'undefined') {
            //   console.log('Using window.sessionTimeout:', window.sessionTimeout);
              // Use sessionTimeout if available (synchronized with server)
              remaining = window.sessionTimeout;
              window.sessionTimeout--; // Decrement for next update
          } else if (loginTime) {
            //   console.log('Using loginTime fallback');
              // Fallback to original logic
              const now = Math.floor(Date.now() / 1000);
              const elapsed = now - loginTime;
              remaining = sessionDuration - elapsed;
          } else {
              console.log('No session data available');
              return;
          }

          if (remaining <= 0) {
              document.getElementById('timer-display').textContent = '00:00';
              document.getElementById('session-timer').classList.add('text-danger');
              // Optional: auto logout or show warning
              if (remaining < -60) { // 1 minute grace
                  window.location.href = '/logout';
              }
              return;
          }

          const minutes = Math.floor(remaining / 60);
          const seconds = remaining % 60;
          const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
          document.getElementById('timer-display').textContent = display;

          // Change color when less than 5 minutes
          if (remaining <= 300) {
              document.getElementById('session-timer').classList.add('text-warning');
          }
      }

      // Initialize the menu system when DOM is loaded
      document.addEventListener('DOMContentLoaded', function() {
          console.log('DOM loaded, initializing MenuSystem');
          try {
              console.log('MenuSystem class available:', typeof MenuSystem);
              window.menuSystem = new MenuSystem();
              console.log('MenuSystem initialized successfully');
              // Start session timer
              console.log('Starting session timer');
              updateSessionTimer();
              setInterval(updateSessionTimer, 1000);
          } catch (error) {
              console.error('Error initializing MenuSystem:', error);
          }
      });
  </script>

  <!-- Session Timeout Modal -->
  <div class="modal fade" id="sessionTimeoutModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content text-center">
              <div class="modal-header bg-danger text-light">
                  <h5 class="modal-title">⚠️ Session Expiring Soon</h5>
              </div>
              <div class="modal-body">
                  <p>Your session will expire in <span id="countdown">120</span> seconds.</p>
                  <p>Would you like to stay logged in?</p>
                  <button id="stayLoggedIn" class="btn btn-success me-2">Stay Logged In</button>
                  <button id="logoutNow" class="btn btn-danger">Logout</button>
              </div>
          </div>
      </div>
  </div>

  </body>

  </html>