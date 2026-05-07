# UG IRB Portal Technical Documentation

## Directory Structure

```
UG_IRB_Portal/
├── .env
├── .env.example
├── config.php
├── index.php
├── php_errors.log
├── admin/
│   ├── 403.php
│   ├── 404.php
│   ├── assets/
│   │   ├── css/
│   │   └── images/
│   │       └── js/
│   ├── handlers/
│   │   ├── add_agenda_category.php
│   │   ├── add_agenda_record.php
│   │   ├── add_benefit.php
│   │   ├── add_child.php
│   │   ├── add_classification.php
│   │   ├── add_contacts.php
│   │   ├── add_cpa_action.php
│   │   ├── add_cpa_report.php
│   │   ├── add_cpa_type.php
│   │   ├── add_department.php
│   │   ├── add_device.php
│   │   ├── add_division.php
│   │   ├── add_drug.php
│   │   ├── add_exempt.php
│   │   ├── add_expedited.php
│   │   ├── add_grant.php
│   │   ├── add_institution.php
│   │   ├── add_investigator.php
│   │   ├── add_irb_action.php
│   │   ├── add_irb_condition.php
│   │   ├── add_irb_meeting.php
│   │   ├── add_new_user.php
│   │   ├── add_personnel.php
│   │   ├── add_risk.php
│   │   ├── add_sae_report.php
│   │   ├── add_sae_type.php
│   │   ├── add_site.php
│   │   ├── add_sponsor.php
│   │   ├── add_study_codes.php
│   │   ├── add_study_handler.php
│   │   ├── add_template.php
│   │   ├── add_vulnerable.php
│   │   ├── assign_reviewer.php
│   │   ├── delete_agenda_category.php
│   │   ├── delete_agenda_item.php
│   │   ├── delete_benefit.php
│   │   ├── delete_child.php
│   │   ├── delete_classification.php
│   │   ├── delete_contact_document.php
│   │   ├── delete_contacts.php
│   │   ├── delete_cpa_action.php
│   │   ├── delete_cpa_type.php
│   │   ├── delete_department.php
│   │   ├── delete_device.php
│   │   ├── delete_division.php
│   │   ├── delete_document.php
│   │   ├── delete_drug.php
│   │   ├── delete_exempt.php
│   │   ├── delete_expedited.php
│   │   ├── delete_grant.php
│   │   ├── delete_institution.php
│   │   ├── delete_investigator.php
│   │   ├── delete_irb_action.php
│   │   ├── delete_irb_condition.php
│   │   ├── delete_irb_meeting.php
│   │   ├── delete_personnel.php
│   │   ├── delete_risk.php
│   │   ├── delete_sae_type.php
│   │   ├── delete_site.php
│   │   ├── delete_study_codes.php
│   │   ├── delete_user.php
│   │   ├── delete_vulnerable.php
│   │   ├── download_letter.php
│   │   ├── download_report.php
│   │   ├── fetch_agenda_categories.php
│   │   ├── fetch_agenda_details.php
│   │   ├── fetch_agenda_meetings.php
│   │   ├── fetch_applications.php
│   │   ├── fetch_benefits.php
│   │   ├── fetch_children.php
│   │   ├── fetch_classifications.php
│   │   ├── fetch_contact_documents.php
│   │   ├── fetch_contact_email.php
│   │   ├── fetch_contacts.php
│   │   ├── fetch_cpa_actions.php
│   │   ├── fetch_cpa_types.php
│   │   ├── fetch_departments.php
│   │   ├── fetch_detail_levels.php
│   │   ├── fetch_devices.php
│   │   ├── fetch_divisions.php
│   │   ├── fetch_documents.php
│   │   ├── fetch_drugs.php
│   │   ├── fetch_exempts.php
│   │   ├── fetch_expedited.php
│   │   ├── fetch_follow_ups.php
│   │   ├── fetch_grants.php
│   │   ├── fetch_institutions.php
│   │   ├── fetch_investigators.php
│   │   ├── fetch_irb_actions.php
│   │   ├── fetch_irb_conditions.php
│   │   ├── fetch_irb_meetings.php
│   │   ├── fetch_notifications.php
│   │   ├── fetch_personnel_emails.php
│   │   ├── fetch_personnel.php
│   │   ├── fetch_reviewers.php
│   │   ├── fetch_risks.php
│   │   ├── fetch_sae_types.php
│   │   ├── fetch_sites.php
│   │   ├── fetch_study_codes.php
│   │   ├── fetch_template.php
│   │   ├── fetch_vulnerables.php
│   │   ├── generate_report.php
│   │   ├── get_application_details.php
│   │   ├── get_cpa.php
│   │   ├── get_study_documents.php
│   │   ├── getStaffNamesByRole.php
│   │   ├── mark_notification_read.php
│   │   ├── php_errors.log
│   │   ├── send_email_handler.php
│   │   ├── send_invite.php
│   │   ├── update_agenda_category.php
│   │   ├── update_agenda_item.php
│   │   ├── update_agenda_meeting_date.php
│   │   ├── update_benefit.php
│   │   ├── update_child.php
│   │   ├── update_classification.php
│   │   ├── update_cpa_action.php
│   │   ├── update_cpa_report.php
│   │   ├── update_cpa_type.php
│   │   ├── update_department.php
│   │   ├── update_device.php
│   │   ├── update_division.php
│   │   ├── update_drug.php
│   │   ├── update_exempt.php
│   │   ├── update_expedited.php
│   │   ├── update_grant.php
│   │   ├── update_institution.php
│   │   ├── update_investigator.php
│   │   ├── update_irb_action.php
│   │   ├── update_irb_condition.php
│   │   ├── update_irb_meeting.php
│   │   ├── update_personnel.php
│   │   ├── update_risk.php
│   │   ├── update_sae_type.php
│   │   ├── update_site.php
│   │   ├── update_study_codes.php
│   │   ├── update_template.php
│   │   ├── update_user_status.php
│   │   ├── update_vulnerable.php
│   ├── includes/
│   │   ├── auth_check.php
│   │   ├── footer.php
│   │   ├── header.php
│   │   └── loading_overlay.php
│   └── pages/
│       ├── contents/
│       │   ├── account_information.php
│       │   ├── add_new_study.php
│       │   ├── administration_content.php
│       │   ├── agenda_records_content.php
│       │   ├── applications_content.php
│       │   ├── create_contact.php
│       │   ├── dashboard_content.php
│       │   ├── due_continue_review_content.php
│       │   ├── follow_up_content.php
│       │   ├── general_letters_content.php
│       │   ├── institutions_content.php
│       │   ├── minutes_preparation.php
│       │   ├── post_meeting_content.php
│       │   ├── preliminary_agenda_content.php
│       │   ├── prepare_agenda.php
│       │   ├── reports_content.php
│       │   ├── study_content.php
│       │   ├── task_managers_content.php
│       │   └── view_application.php
│       ├── dashboard.php
│       ├── dashboard/
│       │   ├── administration_content.php
│       │   ├── agenda_records_content.php
│       │   ├── applications_content.php
│       │   ├── due_continue_review_content.php
│       │   ├── follow_up_content.php
│       │   ├── general_letters_content.php
│       │   ├── index.php
│       │   ├── institutions_content.php
│       │   ├── post_meeting_content.php
│       │   ├── preliminary_agenda_content.php
│       │   ├── reports_content.php
│       │   ├── sidebar.php
│       │   ├── study_content.php
│       │   └── task_managers_content.php
│       ├── maintenance.php
│       └── partials/
│           └── administration_modals.php
├── applicant/
│   ├── assets/
│   │   └── css/
│   ├── handlers/
│   │   ├── get_application_data.php
│   │   ├── get_draft_data.php
│   │   ├── nmimr_application_handler.php
│   │   ├── non_nmimr_application_handler.php
│   │   ├── php_errors.log
│   │   └── student_application_handler.php
│   └── pages/
│       ├── applications.php
│       ├── index.php
│       ├── new_application.php
│       ├── nmimr_application.php
│       ├── non_nmimr_application.php
│       ├── profile.php
│       ├── sidebar.php
│       └── student_application.php
├── docs/
│   └── ARCHITECTURE.md
├── includes/
│   ├── config/
│   │   └── database.php
│   ├── functions/
│   │   ├── csrf.php
│   │   ├── helpers.php
│   │   ├── notification_functions.php
│   │   └── security.php
│   ├── submission_engine.php
│   ├── submission_engine/
│   │   ├── factories/
│   │   │   └── ApplicationHandlerFactory.php
│   │   ├── handlers/
│   │   │   ├── BaseAbstractHandler.php
│   │   │   ├── NmimrHandler.php
│   │   │   ├── NonNmimrHandler.php
│   │   │   └── StudentHandler.php
│   │   ├── interfaces/
│   │   │   └── IApplicationHandler.php
│   │   └── services/
│   │       ├── EmailService.php
│   │       ├── FileUploadService.php
│   │       ├── ProtocolNumberGenerator.php
│   │       ├── SessionManager.php
│   │       └── ValidationService.php
│   └── uploads/
│       ├── nmimr_applications/
│       │   └── 2026/
│       ├── non_nmimr_applications/
│       │   └── 2026/
│       └── student_applications/
│           └── 2026/
├── plans/
│   ├── applicant_modification_plan.md
│   └── maintenance-page-design-spec.md
├── reviewer/
│   ├── handlers/
│   │   ├── assign_application.php
│   │   └── submit_decision.php
│   └── pages/
│       ├── index.php
│       ├── meetings.php
│       ├── profile.php
│       ├── review_detail.php
│       ├── reviews.php
│       ├── sidebar.php
│       └── workload.php
├── sessions/
├── uploads/
│   ├── contacts/
│   │   └── 1/
│   ├── nmimr_applications/
│   │   └── 2026/
│   ├── non_nmimr_applications/
│   │   └── 2026/
│   └── templates/
│       └── 1771425923_IRBActionLetter.docx
└── user/
    ├── authenticate.php
    ├── forgot_password.php
    ├── handlers/
    │   ├── forgot_password_handler.php
    │   ├── php_errors.log
    │   └── register.php
    ├── login.php
    ├── logout.php
    ├── register.php
    ├── reset_password.php
```

## System Architecture Overview

### Core Design Patterns

The UG IRB Portal employs several design patterns in its custom logic, particularly in the unified submission engine:

1. **Factory Pattern**: Used in `ApplicationHandlerFactory` to create appropriate application handlers based on application type (student, NMIMR, non-NMIMR).

2. **Strategy Pattern**: Different application types use concrete handler classes (`StudentHandler`, `NmimrHandler`, `NonNmimrHandler`) that implement type-specific validation and saving logic.

3. **Template Method Pattern**: The `BaseAbstractHandler` defines a common workflow for all application submissions, with abstract methods allowing subclasses to customize type-specific steps.

### Data Flow

1. **User Authentication**: Users log in via role-based authentication (applicant, reviewer, admin) handled through session management.

2. **Application Submission**:
   - Applicants access type-specific forms (student, NMIMR, non-NMIMR)
   - Forms submit to unified submission engine via `SubmissionEngine::submit()`
   - Factory creates appropriate handler based on application type
   - Handler processes validation, file uploads, protocol number generation
   - Data saved to database with type-specific details

3. **Review Process**:
   - Submitted applications appear in reviewer queue
   - Reviewers access applications via review interface
   - Decisions submitted and stored in `application_reviews` table

4. **Administration**:
   - Admins manage system data (users, institutions, templates, etc.)
   - CRUD operations handled via admin handlers
   - Reporting and monitoring through dashboard

### Integration Points

1. **Database Layer**: Custom database abstraction using PDO with helper functions in `includes/functions/helpers.php`. Tables include:
   - `users`/`applicant_users`: User management
   - `applications`: Main application records
   - `application_reviews`: Review workflow
   - Supporting tables: `contacts`, `institutions`, `irb_meetings`, etc.

2. **File Upload System**: Handled by `FileUploadService` with secure file validation, directory creation, and path management. Files stored in organized directory structure under `uploads/`.

3. **Email Notifications**: `EmailService` using PHPMailer for sending confirmations, notifications, and system alerts.

4. **Session Management**: Custom `SessionManager` with CSRF protection and user state management.

5. **Security**: CSRF tokens, input sanitization, file type validation, and role-based access control.

### Key Components

- **Unified Submission Engine**: Consolidates three application types into maintainable OOP structure, reducing code duplication by ~60%
- **Role-Based Access Control**: Separate interfaces for applicants, reviewers, and administrators
- **File Management**: Organized upload system with type-specific directories and validation
- **Notification System**: Automated email notifications for application status changes and system events

The architecture is transitioning from procedural PHP to object-oriented design, with the submission engine representing a significant refactoring effort to improve maintainability and extensibility.