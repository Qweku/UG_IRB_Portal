  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
/**
 * Sidebar Toggle Functions for Mobile Responsiveness
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');
    
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
    if (backdrop) {
        backdrop.classList.toggle('show');
    }
    
    // Prevent body scroll when sidebar is open
    document.body.classList.toggle('sidebar-open');
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');
    
    if (sidebar) {
        sidebar.classList.remove('show');
    }
    if (backdrop) {
        backdrop.classList.remove('show');
    }
    
    document.body.classList.remove('sidebar-open');
}

// Close sidebar on Escape key press
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSidebar();
    }
});
</script>

  <?php
    // Session timeout calculation for global modal
    // $session_lifetime = ini_get('session.gc_maxlifetime');
    // if (!isset($_SESSION['session_expire_time'])) {
    //     $_SESSION['session_expire_time'] = time() + $session_lifetime;
    // }
    // $time_remaining = $_SESSION['session_expire_time'] - time();
    ?>

  <!-- <script>
      window.sessionTimeout = <?php 
      //echo $time_remaining; ?>;
  </script> -->

  <!-- <script>
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
  </script> -->

  <!-- <script>

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
             try {
              
              // Start session timer
              console.log('Starting session timer');
              updateSessionTimer();
              setInterval(updateSessionTimer, 1000);
          } catch (error) {
              console.error('Error initializing :', error);
          }
      });
  </script> -->

  <!-- Session Timeout Modal -->
  <!-- <div class="modal fade" id="sessionTimeoutModal" tabindex="-1" aria-hidden="true">
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
  </div> -->

  </body>

  </html>