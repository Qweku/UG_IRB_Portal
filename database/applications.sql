
DROP TABLE IF EXISTS applications;
CREATE TABLE applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    applicant_id INT UNSIGNED NOT NULL,
    application_type ENUM('student','nmimr','non_nmimr') NOT NULL,

    protocol_number VARCHAR(100) NULL,
    version_number VARCHAR(20) NULL,
    study_title TEXT NULL,

    research_type VARCHAR(100) NULL,
    research_type_other VARCHAR(255) NULL,

    abstract TEXT NULL,
    ethical_considerations TEXT NULL,
    work_plan TEXT NULL,
    budget TEXT NULL,

    status ENUM('draft','submitted','under_review','approved','rejected','withdrawn')
           NOT NULL DEFAULT 'draft',

    current_step TINYINT UNSIGNED DEFAULT 1,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_applicant (applicant_id),
    INDEX idx_type (application_type),
    INDEX idx_status (status),
    INDEX idx_protocol (protocol_number)
);


DROP TABLE IF EXISTS student_application_details;
CREATE TABLE student_application_details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id INT UNSIGNED NOT NULL,

    student_name VARCHAR(255),
    student_institution VARCHAR(255),
    student_department VARCHAR(255),
    student_address VARCHAR(255),
    student_number VARCHAR(100),
    student_phone VARCHAR(50),
    student_email VARCHAR(255),

    supervisor1_name VARCHAR(255),
    supervisor1_institution VARCHAR(255),
    supervisor1_address VARCHAR(255),
    supervisor1_phone VARCHAR(50),
    supervisor1_email VARCHAR(255),

    supervisor2_name VARCHAR(255),
    supervisor2_institution VARCHAR(255),
    supervisor2_address VARCHAR(255),
    supervisor2_phone VARCHAR(50),
    supervisor2_email VARCHAR(255),

    student_status VARCHAR(100),

    study_duration_years VARCHAR(50),
    study_start_date DATE,
    study_end_date DATE,

    funding_sources VARCHAR(255),
    approval_letter TEXT,
    prior_irb_review ENUM('yes','no','pending'),
    collaborating_institutions TEXT,
    collaboration_letter TEXT,

    background TEXT,
    methods TEXT,
    expected_outcome TEXT,
    key_references TEXT,

    consent_form TEXT,
    assent_form TEXT,
    data_instruments TEXT,
    additional_documents TEXT,

    declarations TEXT,
    student_declaration_name VARCHAR(255),
    student_declaration_date DATE,
    student_declaration_signature VARCHAR(255),

    supervisor_declaration_name VARCHAR(255),
    supervisor_declaration_date DATE,
    supervisor_declaration_signature VARCHAR(255),

    FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE
);


DROP TABLE IF EXISTS nmimr_application_details;
CREATE TABLE nmimr_application_details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id INT UNSIGNED NOT NULL,

    submission_date DATE,

    pi_name VARCHAR(255),
    pi_institution VARCHAR(255),
    pi_address VARCHAR(255),
    pi_phone VARCHAR(50),
    pi_email VARCHAR(255),

    co_investigators TEXT,

    project_duration VARCHAR(100),
    funding_source VARCHAR(255),
    prior_irb ENUM('yes','no','pending'),

    introduction TEXT,
    literature_review TEXT,
    study_aims TEXT,
    methodology TEXT,
    expected_outcomes TEXT,
    nmimr_references TEXT,

    pi_signature VARCHAR(255),
    pi_date DATE,

    copi_signature VARCHAR(255),
    copi_date DATE,

    final_confirmation TINYINT(1),

    submitted_at DATETIME,
    reviewed_by INT UNSIGNED,
    review_notes TEXT,

    FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE
);


DROP TABLE IF EXISTS non_nmimr_application_details;
CREATE TABLE non_nmimr_application_details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id INT UNSIGNED NOT NULL,

    pi_details TEXT,
    co_pi TEXT,

    prior_scientific_review ENUM('yes','no','pending'),
    prior_irb_review ENUM('yes','no','pending'),
    collaborating_institutions TEXT,

    funding_source VARCHAR(255),
    duration VARCHAR(100),

    introduction TEXT,
    literature_review TEXT,
    aims TEXT,
    methodology TEXT,
    expected_outcomes TEXT,
    application_references TEXT,

    consent_form TEXT,
    assent_form TEXT,
    data_instruments TEXT,

    pi_name VARCHAR(255),
    pi_institution VARCHAR(255),
    pi_address VARCHAR(255),
    pi_phone_number VARCHAR(50),
    pi_fax VARCHAR(50),
    pi_email VARCHAR(255),

    pi_signature VARCHAR(255),
    pi_date DATE,

    co_pi_name VARCHAR(255),
    co_pi_qualification VARCHAR(255),
    co_pi_department VARCHAR(255),
    co_pi_address VARCHAR(255),
    co_pi_phone_number VARCHAR(50),
    co_pi_fax VARCHAR(50),
    co_pi_email VARCHAR(255),
    co_pi_signature VARCHAR(255),
    co_pi_date DATE,

    submission_notes TEXT,
    final_pdf VARCHAR(255),

    FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE
);


CREATE TABLE applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
) ENGINE=InnoDB;

CREATE TABLE application_documents (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    application_id INT UNSIGNED NOT NULL,

    document_type VARCHAR(100) NOT NULL,
    /*
       Examples:
       approval_letter
       collaboration_letter
       consent_form
       assent_form
       data_instruments
       final_pdf
       supporting_document
       required_forms
       nmimr_pdf
       other
    */

    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT UNSIGNED NULL,
    mime_type VARCHAR(100) NULL,

    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    uploaded_by INT UNSIGNED NULL,

    /*CONSTRAINT fk_application_documents
        FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE,*/

    INDEX idx_application_id (application_id),
    INDEX idx_document_type (document_type),
    INDEX idx_uploaded_at (uploaded_at)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;