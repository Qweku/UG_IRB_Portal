# UG IRB Portal Training Manual

## University of Ghana Human Research Ethics and Applications System

**Version 1.0**  
**Last Updated: February 2026**

---

## Table of Contents

1. [Introduction & Overview](#1-introduction--overview)
2. [Getting Started](#2-getting-started)
3. [User Roles & Permissions](#3-user-roles--permissions)
4. [Applicant Guide](#4-applicant-guide)
5. [Reviewer Guide](#5-reviewer-guide)
6. [Administrator Guide](#6-administrator-guide)
7. [Super Admin Guide](#7-super-admin-guide)
8. [Common Tasks & Workflows](#8-common-tasks--workflows)
9. [Troubleshooting & Support](#9-troubleshooting--support)

---

## 1. Introduction & Overview

### 1.1 What is UG IRB Portal?

The **UG IRB Portal** (University of Ghana Institutional Review Board Portal) is a comprehensive web-based system designed to manage the entire lifecycle of research ethics applications for the University of Ghana. This digital platform streamlines the submission, review, and approval process for research protocols involving human subjects, ensuring compliance with international ethical standards and institutional policies.

The portal serves as a centralized hub for researchers, reviewers, and administrators to collaborate on ethics reviews, track application progress, manage meeting agendas, and maintain comprehensive records of all IRB activities. By digitizing what was previously a paper-based process, the system significantly reduces processing time, improves transparency, and enhances the overall efficiency of the ethics review process.

### 1.2 Purpose and Scope

The primary purpose of the UG IRB Portal is to facilitate the ethical review of research involving human participants conducted under the auspices of the University of Ghana. The system handles various types of research, including:

- **Student Research**: Undergraduate and graduate student projects requiring ethics approval
- **Faculty Research**: University faculty members conducting academic research
- **External Research**: Non-NMIMR researchers seeking ethics clearance for studies involving University of Ghana resources or populations
- **Collaborative Studies**: Multi-institutional research projects requiring IRB oversight

The scope of the portal encompasses:

- **Application Submission**: A structured multi-step form for submitting research protocols
- **Review Management**: Workflow for assigning reviewers, tracking review status, and managing reviewer recommendations
- **Meeting Coordination**: Tools for preparing meeting agendas, recording minutes, and documenting decisions
- **Post-Approval Monitoring**: Tracking of approved studies, managing continuing reviews, and handling amendments
- **Reporting**: Generation of statistical reports and compliance documentation
- **Communication**: Facilitating correspondence between applicants, reviewers, and administrators

### 1.3 System Requirements

To access and use the UG IRB Portal effectively, users must meet the following requirements:

#### Hardware Requirements
- **Computer**: Any modern computer with internet capability
- **Display**: Minimum resolution of 1024x768 pixels (recommended: 1280x720 or higher)
- **Input**: Keyboard and mouse for form navigation

#### Software Requirements
- **Web Browser**: One of the following modern browsers:
  - Google Chrome (version 90 or higher)
  - Mozilla Firefox (version 88 or higher)
  - Microsoft Edge (version 90 or higher)
  - Safari (version 14 or higher)
- **Operating System**: Windows 10/11, macOS, or Linux
- **PDF Reader**: Adobe Acrobat Reader or equivalent for viewing generated documents
- **Email Client**: Access to your email account for system notifications

#### Network Requirements
- **Internet Connection**: Stable broadband connection (minimum 1 Mbps)
- **Access**: The portal must be accessible from your network (check with your IT department if accessing from behind a corporate firewall)

#### Account Requirements
- **Valid Email**: A working email address for account creation and notifications
- **Institutional Affiliation**: Depending on your role, you may need University of Ghana credentials or administrator-created accounts

---

## 2. Getting Started

### 2.1 Accessing the System

To access the UG IRB Portal:

1. **Open your web browser** (Chrome, Firefox, Edge, or Safari recommended)
2. **Navigate to the portal URL**: The system administrator will provide the specific URL for your institution (typically something like `https://ughares.ug.edu.gh` or similar)
3. **Verify the connection**: Ensure the URL begins with "https://" for secure communication
4. **Bookmark the page**: For convenience, add the portal to your bookmarks

> **Note**: The UG IRB Portal is optimized for desktop use. While it may function on mobile devices, a desktop computer provides the best experience for completing application forms and reviews.

### 2.2 Login Process

The login process varies slightly depending on your user role:

#### For Applicants
1. Navigate to the login page
2. Enter your **registered email address**
3. Enter your **password**
4. Click the **"Login"** button
5. If credentials are correct, you will be redirected to your applicant dashboard

#### For Admin, Reviewer, and Super Admin Users
1. Navigate to the login page
2. Enter your **username** (typically provided by the system administrator)
3. Enter your **password**
4. Click the **"Login"** button
5. If credentials are correct, you will be redirected to your role-specific dashboard

#### Security Features
- **Session Management**: Your session will expire after a period of inactivity (typically 30 minutes)
- **CSRF Protection**: All forms include security tokens to prevent cross-site request forgery
- **Password Requirements**: Passwords must meet minimum complexity requirements

### 2.3 Password Recovery

If you have forgotten your password, follow these steps:

1. Click the **"Forgot Password?"** link on the login page
2. Enter your **registered email address**
3. Click **"Submit"** or **"Reset Password"**
4. Check your email inbox for a password reset link
5. Click the link in the email (note: the link expires after a limited time, typically 1 hour)
6. Enter your **new password** and **confirm the new password**
7. Click **"Update Password"** or **"Save"**
8. Return to the login page and log in with your new credentials

> **Important**: 
> - The password reset link is single-use and time-limited
> - If you do not receive the email within 5 minutes, check your spam/junk folder
> - Contact your system administrator if you encounter issues

### 2.4 First-Time Setup

Upon your first login, you may be prompted to complete certain setup tasks:

#### For New Applicants
1. **Complete Your Profile**: Add your personal information, including:
   - Full name
   - Department/Unit
   - Phone number
   - Research interests (optional)

2. **Verify Email**: Confirm your email address if not already verified

3. **Review Dashboard**: Familiarize yourself with the applicant dashboard layout

#### For First-Time Admin/Reviewer Users
1. **Password Change**: You may be required to change your initial password on first login
2. **Profile Completion**: Update your profile with your full name, role, and contact information
3. **Dashboard Tour**: Review your dashboard to understand available features
4. **Notification Settings**: Configure how you wish to receive system notifications

> **Tip**: Take time to explore the navigation menu and familiarize yourself with the different sections before beginning your first application or review.

---

## 3. User Roles & Permissions

The UG IRB Portal implements a Role-Based Access Control (RBAC) system with four distinct user roles. Each role has specific permissions tailored to the responsibilities of that user type.

### 3.1 Role Overview

| Role | Description | Primary Functions |
|------|-------------|-------------------|
| **super_admin** | System Super Administrator | Full system access, user management, institution management |
| **admin** | IRB Administrator | Application review, workflow management, reports, agenda |
| **applicant** | Researcher/Student | Submit applications, manage drafts, track status |
| **reviewer** | IRB Reviewer | Review assigned applications, provide recommendations |

### 3.2 Super Administrator (super_admin)

The **Super Administrator** has complete control over the entire system. This role is typically held by IT personnel or senior administrators who manage the technical infrastructure.

#### Permissions and Capabilities:
- **Full System Access**: Access to all features and functionality across all modules
- **User Management**: Create, edit, suspend, and delete user accounts
- **Role Assignment**: Assign and modify user roles (admin, reviewer, applicant)
- **Institution Management**: Add, edit, and manage partner institutions
- **System Configuration**: Configure system-wide settings
- **Data Access**: View all applications, reviews, and system data
- **Audit Logs**: Access system audit logs and activity records

#### Access Path:
- Login → Dashboard (full admin view with all menu items)
- Can access: Dashboard, Applications, Studies, Agenda, Reports, Administration

### 3.3 Administrator (admin)

The **IRB Administrator** manages the day-to-day operations of the IRB review process. This role handles application workflow management, reviewer assignments, and administrative tasks.

#### Permissions and Capabilities:
- **Dashboard Access**: View comprehensive statistics and pending actions
- **Application Management**: 
  - Review submitted applications
  - Assign reviewers to applications
  - Manage application workflow status
  - Request modifications from applicants
  - Approve or reject applications
- **Study Management**: Create, edit, and manage study records
- **Agenda Management**:
  - Prepare preliminary meeting agendas
  - Manage meeting schedules
  - Record meeting minutes and decisions
- **Reports Generation**: Create and export various reports
- **Follow-up Tracking**: Monitor approved studies and schedule continuing reviews
- **Contacts Management**: Maintain database of contacts and investigators
- **Letters Generation**: Generate approval letters, rejection letters, and other correspondence
- **Limited Administration**: Some administrative functions (as permitted by super_admin)

#### Access Path:
- Login → Dashboard (admin view)
- Can access: Dashboard, Applications, Studies, Preliminary Agenda, Continue Review, Post-IRB Meeting, Agenda Records, Reports, Follow-up, Administration (limited)

### 3.4 Applicant

The **Applicant** is typically a researcher, faculty member, or student who needs to submit an IRB application for ethics review.

#### Permissions and Capabilities:
- **Registration**: Create a new account on the system
- **Application Submission**:
  - Submit new IRB applications
  - Choose application type (Student, NMIMR Researcher, Non-NMIMR Researcher)
  - Complete multi-step application forms
  - Upload required supporting documents
- **Draft Management**:
  - Save incomplete applications as drafts
  - Resume and edit draft applications
  - Delete draft applications
- **Application Tracking**:
  - View current application status
  - View review history and feedback
  - Receive status notifications
- **Profile Management**:
  - Update personal information
  - Change password
  - Manage notification preferences

#### Access Path:
- Login → Applicant Dashboard
- Cannot access: Admin functions, reviewer panels, system configuration

### 3.5 Reviewer

The **Reviewer** is a designated member of the IRB committee responsible for evaluating research applications and providing recommendations.

#### Permissions and Capabilities:
- **Dashboard Access**: View review statistics and pending assignments
- **Review Management**:
  - View assigned applications
  - Access application documents and attachments
  - Complete review forms
  - Provide recommendations (Approve, Approve with Conditions, Reject, Defer)
  - Add comments and feedback for applicants
- **Meeting Schedule**:
  - View upcoming IRB meetings
  - Access meeting agendas
  - View meeting decisions
- **Workload Statistics**:
  - View personal review statistics
  - Track completed and pending reviews

#### Access Path:
- Login → Reviewer Dashboard
- Can only access applications explicitly assigned to them by administrators

### 3.6 Role Hierarchy

```
super_admin (Level 4 - Full Access)
    │
    ├── Can create/modify all roles
    ├── Full system configuration
    └── Access to all data and functions
    │
admin (Level 3 - Administrative)
    │
    ├── Manages application workflow
    ├── Assigns reviewers
    └── Most administrative functions
    │
reviewer (Level 2 - Review)
    │
    └── Reviews assigned applications
        └── Provides recommendations
            │
applicant (Level 1 - Basic)
    │
    └── Submits and manages own applications
```

---

## 4. Applicant Guide

This section provides detailed instructions for applicants using the UG IRB Portal to submit research ethics applications.

### 4.1 Registration Process

Before submitting an application, you must create an account on the UG IRB Portal.

#### Step-by-Step Registration:

1. **Access the Registration Page**
   - Navigate to the portal URL
   - Click the **"Register"** or **"Sign Up"** link

2. **Fill in Your Information**
   - **Full Name**: Enter your complete name as it should appear on official documents
   - **Email Address**: Use a valid, active email address (this will be your username)
   - **Password**: Create a strong password meeting the following requirements:
     - Minimum 8 characters
     - At least one uppercase letter
     - At least one lowercase letter
     - At least one number
     - At least one special character
   - **Confirm Password**: Re-enter your password to confirm
   - **Department/Unit**: Select your department from the dropdown or enter your unit
   - **Phone Number**: Enter your contact phone number

3. **Select Application Type** (if prompted)
   - **Student**: If you are an undergraduate or graduate student
   - **NMIMR Researcher**: If you are a researcher at the Noguchi Memorial Institute for Medical Research
   - **Non-NMIMR Researcher**: If you are a researcher from another University of Ghana unit or external institution

4. **Agree to Terms**
   - Read the **Terms and Conditions**
   - Read the **Privacy Policy**
   - Check the **"I agree"** checkbox

5. **Submit Registration**
   - Click the **"Register"** or **"Create Account"** button

6. **Verify Email** (if required)
   - Check your email for a verification link
   - Click the link to verify your email address

7. **Login**: Use your credentials to log in to the system

> **Important**: 
> - Use a valid email address as all system communications will be sent to this address
> - If you don't receive a verification email within 10 minutes, check your spam folder
> - For student applications, you may need to wait for admin approval before accessing full features

### 4.2 Creating a New Application

Once logged in, you can create a new IRB application by following these steps:

#### Step 1: Start a New Application

1. Log in to your **Applicant Dashboard**
2. Click the **"New Application"** or **"Submit Application"** button
3. Select the **Application Type**:
   - **Student Application**: For undergraduate/graduate research projects
   - **NMIMR Researcher Application**: For research conducted at Noguchi Memorial Institute
   - **Non-NMIMR Researcher Application**: For research by external researchers

#### Step 2: Complete Application Sections

The application consists of multiple sections. Complete each section thoroughly:

**Section A: Study Information**
- **Study Title**: Enter the full title of your research project
- **Protocol Number** (if applicable): Enter any existing protocol numbers
- **Proposed Start Date**: Select when you plan to begin data collection
- **Proposed End Date**: Select when you expect to complete the study
- **Study Duration**: Enter the total duration in months

**Section B: Principal Investigator Information**
- **Full Name**: Enter your complete name
- **Email**: Your registered email address
- **Phone**: Your contact number
- **Department**: Your academic department or unit
- **Position/Title**: Your academic rank or position

**Section C: Co-Investigators** (if applicable)
- Add co-investigators by clicking **"Add Co-Investigator"**
- Enter each co-investigator's name, email, and institution

**Section D: Study Location**
- **Primary Site**: Where the main research activities will occur
- **Additional Sites**: List any other locations where research will be conducted

**Section E: Study Type**
- Select the type of research (e.g., Clinical Trial, Observational Study, Survey/Questionnaire, etc.)
- Indicate if the study involves vulnerable populations

**Section F: Funding Information** (if applicable)
- **Funding Source**: Who is funding the research
- **Grant Number**: Any applicable grant numbers
- **Total Budget**: Approximate budget for the study

**Section G: Research Description**
- **Background**: Explain the rationale for the study
- **Objectives**: List the primary and secondary objectives
- **Methodology**: Describe the study design and procedures
- **Statistical Methods**: Explain how data will be analyzed

**Section H: Participants**
- **Target Population**: Describe the population from which participants will be recruited
- **Inclusion Criteria**: Who can participate
- **Exclusion Criteria**: Who cannot participate
- **Sample Size**: Number of participants expected
- **Recruitment Methods**: How participants will be recruited

**Section I: Informed Consent**
- Describe the consent process
- Indicate if consent will be obtained from participants
- Explain how participants will be informed about the study

**Section J: Risks and Benefits**
- **Potential Risks**: Describe any potential risks to participants
- **Risk Minimization**: Explain how risks will be minimized
- **Potential Benefits**: Describe any benefits to participants or society
- **Compensation**: Describe any compensation for participants

**Section K: Data Management**
- **Data Collection**: What data will be collected
- **Data Storage**: Where and how data will be stored
- **Data Retention**: How long data will be kept
- **Confidentiality**: How participant confidentiality will be protected

**Section L: Attachments**
- Upload required documents:
  - Research Protocol
  - Informed Consent Form(s)
  - Questionnaire/Survey instruments
  - Recruitment materials
  - CV of Principal Investigator
  - Any other supporting documents

#### Step 3: Review and Submit

1. Review all entered information for completeness and accuracy
2. Make any necessary corrections
3. Click **"Submit Application"**
4. Confirm your submission

> **Note**: Once submitted, you cannot make changes to the application. If revisions are needed, the administrator will return the application with feedback.

### 4.3 Application Types Explained

#### Student Application
- Designed for undergraduate and graduate students
- Requires faculty supervisor information
- May require additional institutional approvals
- Typically has longer review timelines for academic calendar alignment

#### NMIMR Researcher Application
- For researchers affiliated with the Noguchi Memorial Institute for Medical Research
- Requires NMIMR staff credentials
- May have access to NMIMR-specific resources
- Streamlined internal review process

#### Non-NMIMR Researcher Application
- For researchers from other University of Ghana units
- For external researchers collaborating with UG
- Requires partnership documentation
- May require additional institutional agreements

### 4.4 Saving Drafts

The portal allows you to save incomplete applications as drafts and continue later.

#### To Save a Draft:
1. While completing your application, click **"Save as Draft"** or **"Save Progress"**
2. The system will save all entered information
3. A confirmation message will appear
4. You can safely log out

#### To Resume a Draft:
1. Log in to your dashboard
2. Navigate to **"My Applications"** or **"Drafts"**
3. Find your saved draft
4. Click **"Continue"** or **"Edit"**
5. Complete the remaining sections
6. Submit when finished

> **Tip**: Save your draft frequently to avoid losing work. The system may auto-save, but manual saves ensure your progress is preserved.

### 4.5 Tracking Application Status

You can monitor the status of your submitted applications at any time.

#### Status Types:
| Status | Description |
|--------|-------------|
| **Submitted** | Application has been received and is in queue for initial review |
| **Under Review** | Application is being reviewed by assigned reviewers |
| **Pending Decision** | Review is complete, awaiting IRB meeting decision |
| **Approved** | Application has been approved |
| **Approved with Conditions** | Application approved subject to specified conditions |
| **Rejected** | Application was not approved |
| **Deferred** | Application put on hold pending additional information |
| **Withdrawn** | Applicant has withdrawn the application |

#### To Check Status:
1. Log in to your **Applicant Dashboard**
2. Look for the **"My Applications"** section
3. View the **Status** column for each application
4. Click on an application to see detailed history

#### Notifications:
You will receive email notifications when:
- Your application is received
- A reviewer is assigned
- A decision is made
- Feedback is provided
- Your application requires modification

### 4.6 Profile Management

#### Updating Your Profile:
1. Log in to your dashboard
2. Navigate to **"Profile"** or **"My Profile"**
3. Edit your information:
   - Name
   - Phone number
   - Department
   - Notification preferences
4. Click **"Save Changes"**

#### Changing Your Password:
1. Go to **"Profile"** → **"Security"** or **"Change Password"**
2. Enter your **Current Password**
3. Enter your **New Password**
4. Confirm your **New Password**
5. Click **"Update Password"**

---

## 5. Reviewer Guide

This section provides detailed instructions for IRB reviewers using the UG IRB Portal to evaluate research applications.

### 5.1 Dashboard Overview

When you log in as a reviewer, you will see your personalized dashboard containing:

#### Dashboard Components:
- **Welcome Message**: Your name and role
- **Statistics Cards**:
  - **Pending Reviews**: Number of applications awaiting your review
  - **Completed Reviews**: Total reviews you have completed
  - **Upcoming Deadlines**: Near-pending review due dates
- **Recent Activity**: List of recent actions on assigned applications
- **Quick Actions**: Buttons for common tasks

### 5.2 Viewing Assigned Reviews

#### Accessing Your Reviews:
1. Log in to the **Reviewer Dashboard**
2. Click on **"Pending Reviews"** or **"My Reviews"** in the navigation menu
3. View the list of applications assigned to you

#### Review List Information:
Each application in your list shows:
- **Application ID**: Unique identifier
- **Study Title**: Name of the research project
- **Submission Date**: When the application was submitted
- **Due Date**: When your review is expected
- **Status**: Current review status

#### Opening an Application:
1. Click on the application title or **"Review"** button
2. The application details will open in a new page
3. You can view all sections of the application
4. You can download attached documents

### 5.3 Completing a Review

#### Review Process:

**Step 1: Review Application Details**
- Read through all sections of the application
- Download and review attached documents (protocol, consent forms, questionnaires, etc.)
- Note any questions or concerns

**Step 2: Complete the Review Form**
- Navigate to the review form section
- Evaluate each criterion:
  - **Scientific Design**: Is the methodology sound?
  - **Risk-Benefit Ratio**: Are risks minimized and reasonable?
  - **Participant Selection**: Are participants appropriately chosen?
  - **Informed Consent**: Is the consent process adequate?
  - **Privacy & Confidentiality**: Are protections sufficient?
  - **Vulnerable Populations**: Are special protections in place if needed?

**Step 3: Provide Recommendation**
Select one of the following recommendations:

| Recommendation | Description |
|----------------|-------------|
| **Approve** | The study meets all ethical requirements |
| **Approve with Conditions** | Minor issues that can be addressed administratively |
| **Major Revisions Required** | Significant concerns requiring resubmission |
| **Reject** | Fundamental ethical or methodological problems |
| **Defer** | Cannot make decision without additional information |

**Step 4: Add Comments**
- Provide detailed feedback for the applicant
- Explain the rationale for your recommendation
- Suggest specific improvements

**Step 5: Submit Review**
- Review your feedback
- Click **"Submit Review"** or **"Complete Review"**
- Confirm submission

> **Important**: Once you submit your review, you cannot make changes. Ensure all feedback is complete and accurate.

### 5.4 Meeting Schedule

#### Viewing Meetings:
1. Navigate to **"Meetings"** or **"Meeting Schedule"**
2. View upcoming IRB meetings
3. See which applications will be discussed

#### Meeting Information:
- **Date and Time**: When the meeting will occur
- **Location**: Physical or virtual meeting location
- **Agenda**: List of applications to be reviewed
- **Materials**: Download meeting documents

#### Attending Meetings:
- Check your email for meeting invitations
- Confirm your attendance through the portal
- Access virtual meeting links if applicable

### 5.5 Workload Statistics

#### Viewing Your Statistics:
1. Go to **"Statistics"** or **"My Stats"**
2. View your review performance metrics

#### Available Statistics:
- **Total Reviews Completed**: Number of reviews you've finished
- **Reviews by Decision Type**: Breakdown of your recommendations
- **Average Review Time**: How long you typically take to complete reviews
- **Pending Reviews**: Current workload

---

## 6. Administrator Guide

This section provides detailed instructions for administrators managing the UG IRB Portal.

### 6.1 Dashboard Overview

The admin dashboard provides a comprehensive view of IRB operations.

#### Dashboard Components:
- **Statistics Overview**:
  - Total active studies
  - Pending applications
  - Pending reviews
  - Overdue actions
  - Upcoming meetings
  
- **Quick Actions**:
  - View pending applications
  - Manage reviewers
  - Generate reports
  - View follow-ups

- **Recent Activity Feed**: Latest system activities
- **Alerts**: Important notifications and deadlines

### 6.2 Managing Applications

#### Application Review Workflow:

**1. Initial Review**
- Navigate to **"Applications"**
- View all submitted applications
- Filter by status, date, type
- Click on an application to view details

**2. Assign Reviewers**
- Open an application
- Click **"Assign Reviewer"**
- Select reviewers from the list
- Consider reviewer expertise and workload
- Confirm the assignment
- Notification sent to reviewers

**3. Track Review Progress**
- Monitor review status
- Send reminders to reviewers
- View submitted reviews
- Address any issues

**4. Make Decision**
- After reviews are complete, prepare for IRB meeting
- Present applications at meeting
- Record meeting decisions
- Generate outcome letters

#### Application Actions:
- **View**: Open full application details
- **Assign**: Assign reviewers
- **Request Modification**: Send application back to applicant for revisions
- **Approve**: Approve the application
- **Reject**: Reject the application
- **Withdraw**: Withdraw the application
- **Archive**: Archive after completion

### 6.3 Assigning Reviewers

#### Assigning Reviewers to Applications:
1. Go to **"Applications"**
2. Open the application you want to assign
3. Click **"Assign Reviewer"**
4. Select one or more reviewers:
   - Consider expertise areas
   - Check current workload
   - Avoid conflicts of interest
5. Click **"Assign"**
6. Reviewers receive notification

#### Managing Reviewer Workload:
1. Navigate to **"Reviewers"** or **"Administration"** → **"Reviewers"**
2. View each reviewer's current assignments
3. Balance assignments as needed
4. Send reminders for overdue reviews

### 6.4 Agenda Management

#### Preparing Preliminary Agenda:

1. Navigate to **"Preliminary Agenda"** or **"Agenda Preparation"**
2. Click **"Create New Agenda"**
3. Select meeting date and time
4. Add applications to the agenda:
   - Filter applications ready for review
   - Select applications to include
   - Arrange order of review
5. Assign primary reviewers for each item
6. Add any additional agenda items
7. Save and finalize agenda
8. Distribute to committee members

#### Managing Meeting Records:

1. Go to **"Agenda Records"** or **"Meeting Records"**
2. View past meeting records
3. Access meeting minutes
4. View decisions made
5. Generate meeting reports

### 6.5 Generating Reports

#### Report Types:

| Report Type | Description |
|-------------|-------------|
| **Application Summary** | Overview of all applications |
| **Review Statistics** | Reviewer performance and workload |
| **Decision Statistics** | Approval/rejection rates |
| **Study Status** | Current status of all studies |
| **Timeline Analysis** | Processing time metrics |
| **Custom Reports** | User-defined report parameters |

#### Generating a Report:
1. Navigate to **"Reports"**
2. Select the **Report Type**
3. Set **Date Range**
4. Apply **Filters** (if needed)
5. Click **"Generate Report"**
6. View report on screen
7. **Export** options: PDF, Excel, CSV

### 6.6 Follow-up Tracking

#### Managing Continuing Reviews:
1. Go to **"Continue Review"** or **"Follow-up"**
2. View list of studies requiring continuing review
3. Check due dates
4. Contact investigators for reports
5. Review submitted continuing reviews
6. Make decisions on continued approval

#### Creating Follow-up Items:
1. Navigate to **"Follow-up"** → **"Create Follow-up"**
2. Select the **Study**
3. Set **Due Date**
4. Define **Required Information**
5. Set **Reminder** notifications
6. Save the follow-up item

### 6.7 Contacts Management

#### Managing Contacts:
1. Navigate to **"Contacts"** or **"Contact Management"**
2. View all contacts in the system

#### Adding a New Contact:
1. Click **"Add Contact"**
2. Enter contact information:
   - Full name
   - Email address
   - Phone number
   - Institution
   - Department
   - Role/Position
3. Upload documents (if applicable)
4. Click **"Save"**

#### Editing/Deleting Contacts:
1. Find the contact in the list
2. Click **"Edit"** to modify
3. Click **"Delete"** to remove (confirm deletion)

### 6.8 Letters Generation

#### Generating Letters:
1. Navigate to **"Letters"** or **"Letter Generator"**
2. Select the **Letter Type**:
   - Approval Letter
   - Rejection Letter
   - Conditional Approval Letter
   - Modification Request Letter
   - Meeting Invitation
3. Select the **Application/Study**
4. The system populates relevant information
5. Edit letter content if needed
6. Preview the letter
7. Generate PDF
8. Download or send to applicant

---

## 7. Super Admin Guide

This section provides instructions for system super administrators.

### 7.1 User Management

#### Creating New Users:
1. Navigate to **"Administration"** → **"User Management"**
2. Click **"Add New User"**
3. Fill in user details:
   - Username
   - Full name
   - Email address
   - Role (admin, reviewer, applicant)
   - Department
   - Status
4. Set temporary password
5. Click **"Create User"**
6. User receives login credentials

#### Editing User Accounts:
1. Find the user in the list
2. Click **"Edit"**
3. Modify necessary fields
4. Save changes

#### Managing User Status:
1. View user account
2. Options include:
   - **Activate**: Enable user access
   - **Deactivate**: Temporarily disable access
   - **Delete**: Permanently remove account (with confirmation)

#### Resetting User Passwords:
1. Find the user
2. Click **"Reset Password"**
3. Generate new temporary password
4. User must change password on next login

### 7.2 Institution Management

#### Adding New Institutions:
1. Navigate to **"Administration"** → **"Institutions"**
2. Click **"Add Institution"**
3. Enter institution details:
   - Institution Name
   - Abbreviation
   - Address
   - Contact Information
   - Website
4. Save the institution

#### Managing Institution Settings:
1. Edit institution information
2. Configure institution-specific options
3. Manage institutional reviewers

### 7.3 System Administration

#### System Configuration:
1. Navigate to **"Administration"** → **"System Settings"**
2. Configure:
   - Session timeout duration
   - Password requirements
   - Email notification settings
   - System maintenance mode

#### Managing Roles and Permissions:
1. Go to **"Administration"** → **"Roles"**
2. View role definitions
3. Modify permissions (if necessary)

#### Viewing Audit Logs:
1. Navigate to **"Administration"** → **"Audit Logs"**
2. View system activity
3. Filter by:
   - User
   - Date range
   - Activity type
4. Export logs for analysis

---

## 8. Common Tasks & Workflows

This section provides step-by-step guides for common operations in the UG IRB Portal.

### 8.1 Application Submission Workflow

```
┌─────────────────────────────────────────────────────────────────┐
│                    APPLICATION WORKFLOW                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. REGISTER/LOGIN                                               │
│     ↓                                                            │
│  2. START NEW APPLICATION                                        │
│     → Select Application Type                                    │
│     ↓                                                            │
│  3. COMPLETE APPLICATION FORM                                    │
│     → Study Information                                          │
│     → Researcher Details                                         │
│     → Methodology                                                │
│     → Participant Information                                    │
│     → Consent Process                                            │
│     → Risks and Benefits                                         │
│     ↓                                                            │
│  4. UPLOAD DOCUMENTS                                             │
│     → Protocol                                                   │
│     → Consent Forms                                              │
│     → Instruments                                                │
│     → CVs                                                        │
│     ↓                                                            │
│  5. SUBMIT APPLICATION                                           │
│     → Review and Confirm                                         │
│     ↓                                                            │
│  6. TRACK STATUS                                                 │
│     → View Dashboard                                             │
│     → Receive Notifications                                      │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

#### Detailed Steps:

**Step 1: Register or Log In**
- If new user: Complete registration process
- If existing user: Log in with credentials

**Step 2: Start New Application**
- Click "New Application"
- Select appropriate application type

**Step 3: Complete All Sections**
- Fill in all required fields
- Save drafts frequently
- Ensure information is accurate

**Step 4: Upload Documents**
- Prepare documents in PDF format
- Ensure file sizes are within limits (typically 10MB per file)
- Name files descriptively

**Step 5: Submit**
- Review all information
- Click Submit
- Confirm submission

**Step 6: Monitor**
- Check dashboard for status updates
- Respond to any requests promptly

### 8.2 Review Assignment Workflow

```
┌─────────────────────────────────────────────────────────────────┐
│                   REVIEW ASSIGNMENT WORKFLOW                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. APPLICATION RECEIVED                                         │
│     ↓                                                            │
│  2. ADMIN INITIAL REVIEW                                         │
│     → Verify completeness                                        │
│     ↓                                                            │
│  3. ASSIGN REVIEWERS                                             │
│     → Select qualified reviewers                                │
│     → Consider workload                                          │
│     → Assign via system                                          │
│     ↓                                                            │
│  4. REVIEWERS COMPLETE REVIEWS                                   │
│     → Access assigned applications                               │
│     → Complete review forms                                      │
│     → Submit recommendations                                     │
│     ↓                                                            │
│  5. IRB MEETING                                                  │
│     → Discuss applications                                       │
│     → Make decisions                                             │
│     ↓                                                            │
│  6. COMMUNICATE DECISION                                         │
│     → Generate decision letters                                  │
│     → Notify applicants                                          │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 8.3 Continuing Review Workflow

1. **System identifies** studies due for continuing review
2. **Administrators** send reminder to investigators
3. **Investigators** submit continuing review application
4. **Administrators** assign reviewers
5. **Reviewers** evaluate and provide recommendations
6. **IRB** makes decision on continued approval
7. **Applicants** notified of decision

### 8.4 Creating a Meeting Agenda

1. Navigate to **"Preliminary Agenda"**
2. Click **"Create New Agenda"**
3. Select meeting date and time
4. Filter applications marked for agenda
5. Select applications to include
6. Assign primary reviewers to each item
7. Add other agenda items (if any)
8. Review and finalize agenda
9. Distribute to committee members
10. Archive after meeting

### 8.5 Generating Approval Letters

1. Go to **"Letters"** or **"Letter Generator"**
2. Select **"Approval Letter"**
3. Choose the approved application
4. System auto-populates:
   - Applicant name
   - Study title
   - Approval dates
   - Protocol number
5. Review letter content
6. Add any additional notes
7. Generate PDF
8. Download letter
9. Send to applicant (via system or email)

---

## 9. Troubleshooting & Support

This section addresses common issues and provides guidance for getting help.

### 9.1 Common Issues and Solutions

#### Login Issues

| Problem | Solution |
|---------|----------|
| **Forgot Password** | Use "Forgot Password" link to reset |
| **Account Locked** | Contact administrator to unlock |
| **Invalid Credentials** | Verify username/email and password |
| **Session Expired** | Log in again |

#### Application Issues

| Problem | Solution |
|---------|----------|
| **Cannot Save Draft** | Check internet connection; try different browser |
| **Upload Fails** | Ensure file format is correct (PDF); check file size limits |
| **Form Validation Errors** | Complete all required fields; check for invalid characters |
| **Cannot Submit** | Review all sections for completeness; check for errors |

#### Browser Issues

| Problem | Solution |
|---------|----------|
| **Page Not Loading** | Clear browser cache; try a different browser |
| **Slow Performance** | Close unnecessary tabs; check internet speed |
| **Pop-up Blocked** | Allow pop-ups for the portal URL |
| **Session Issues** | Disable browser extensions that interfere with cookies |

### 9.2 Frequently Asked Questions (FAQ)

#### General Questions

**Q: How long does the IRB review process take?**
A: Review times vary depending on the complexity of the study and the current workload. Initial reviews typically take 2-4 weeks. Expedited reviews may be faster. Complex studies requiring full board review may take longer.

**Q: Can I submit modifications to my application after submission?**
A: Once submitted, you cannot modify your application directly. If changes are needed, the administrator will return the application with feedback, and you can then make revisions.

**Q: How do I know if my application requires full board review?**
A: The system or administrator will inform you. Studies involving vulnerable populations, greater than minimal risk, or other specific criteria typically require full board review.

**Q: Can I withdraw my application after submission?**
A: Yes, you can withdraw your application at any time by contacting the administrator or using the withdrawal option in your dashboard.

#### Account Questions

**Q: Can I have multiple accounts?**
A: No, each user should have only one account. Multiple accounts may be suspended.

**Q: How do I update my email address?**
A: Contact the system administrator to change your registered email address.

**Q: What happens if I change institutions?**
A: Update your profile with your new institution and contact the administrator to update your affiliation.

### 9.3 Contact Information

#### For Technical Support:

| Contact Type | Method |
|--------------|--------|
| **Email** | [To be provided by administrator] |
| **Phone** | [To be provided by administrator] |
| **In-Person** | [Location to be provided] |

#### For Application Questions:

| Contact Type | Method |
|--------------|--------|
| **Email** | irb@ug.edu.gh |
| **Phone** | [IRB Office Number] |
| **Office Hours** | Monday-Friday, 8:00 AM - 4:30 PM |

#### For Urgent Issues:

If you encounter urgent issues affecting the system or requiring immediate attention, contact the system administrator directly by phone.

### 9.4 Getting Help Within the Portal

#### Built-in Help:
- Look for **"Help"** or **"?"** icons throughout the portal
- Tooltips on form fields
- Contextual guidance

#### Documentation:
- This training manual
- User guides (available in the documentation section)
- FAQ documents

---

## Appendix A: Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Save Draft | Ctrl + S |
| Submit Application | Ctrl + Enter |
| Navigate Menu | Tab |
| Go Back | Alt + Left Arrow |
| Refresh Page | F5 |

---

## Appendix B: Glossary

| Term | Definition |
|------|------------|
| **IRB** | Institutional Review Board - Committee that reviews research involving human subjects |
| **Protocol** | Detailed plan for conducting a research study |
| **Informed Consent** | Process of providing information to participants and obtaining their agreement to participate |
| **Vulnerable Populations** | Groups that may need additional protection (children, prisoners, pregnant women, etc.) |
| **Continuing Review** | Periodic review of approved research to ensure it continues to be appropriate |
| **Adverse Event** | Unfavorable outcome occurring during or after participation in research |
| **Minimal Risk** | Risk not greater than what is encountered in daily life or routine activities |
| **Expedited Review** | Review process conducted by IRB chair or designee without full board meeting |

---

## Appendix C: Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | February 2026 | Initial release |

---

*This training manual was created for the UG IRB Portal (University of Ghana Human Research Ethics and Applications System). For the latest version and updates, contact the system administrator.*

**End of Training Manual**
