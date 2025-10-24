<?php

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login');
    exit;
}


?>



<div class="container-fluid">
    <div class="row">
        <!-- Side Bar -->
          <div id="sidebar" class="col-lg-2 col-md-3 d-md-block sidebar collapse">
                <div class="sidebar-sticky">
                    <!-- Dashboard - Always visible -->
                    <ul class="nav flex-column mb-3">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-target="dashboard-content">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                    </ul>

                    <!-- Main sections -->
                    <!-- Add or Modify Section -->
                    <div class="sidebar-section mb-3">
                        <h6 class="sidebar-header ms-3">
                            <i class="fas fa-plus-circle me-2"></i>Add or Modify
                        </h6>
                        <ul class="nav flex-column submenu-nav">
                            <li class="nav-item">
                                <a class="nav-link submenu-link" href="#" data-target="study-content">
                                    <i class="fas fa-file-medical me-2"></i>Study / Protocol
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link" href="#" data-target="preliminary-agenda-content">
                                    <i class="fas fa-calendar-alt me-2"></i>Preliminary Agenda Items
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link" href="#"  data-target="post-meeting-content">
                                    <i class="fas fa-clipboard-check me-2"></i>Post IRB Meeting Actions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link" href="#" data-target="reports-content">
                                    <i class="fas fa-chart-bar me-2"></i>Reports
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Task Managers Section -->
                    <div class="sidebar-section mb-3">
                        <h6 class="sidebar-header ms-3">
                            <i class="fas fa-tasks me-2"></i>Task Managers
                        </h6>
                        <ul class="nav flex-column submenu-nav">
                            <li class="nav-item">
                                <a class="nav-link submenu-link" href="#" data-target="follow-up-content">
                                    <i class="fas fa-clock me-2"></i>Follow Up Manager
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link" href="#" data-target="administration-content">
                                    <i class="fas fa-toolbox me-2"></i>Administration
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link" href="#" data-target="general-letters-content">
                                    <i class="fas fa-envelope me-2"></i>General Letter Choices
                                </a>
                            </li>
                        </ul>
                    </div>

                    
                </div>
            </div>

        <!-- Main Content Area -->
         <div id="dashboard-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">
            <?php include 'contents/dashboard_content.php'; ?>
        </div>
        <div id="study-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/study_content.php'; ?>
        </div>
        <div id="reports-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/reports_content.php'; ?>
        </div>
        <div id="search-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/search_content.php'; ?>
        </div>
        <div id="task-managers-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/task_managers_content.php'; ?>
        </div>
      
        <div id="administration-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/administration_content.php'; ?>
        </div>
      
        <div id="general-letters-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/general_letters_content.php'; ?>
        </div>
        <div id="preliminary-agenda-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/preliminary_agenda_content.php'; ?>
        </div>
        <div id="post-meeting-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/post_meeting_content.php'; ?>
        </div>

        <div id="follow-up-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/follow_up_content.php'; ?>
        </div>


    </div>
</div>

