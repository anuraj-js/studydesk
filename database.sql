-- =============================================
-- STUDYDESK DATABASE SCHEMA
-- MySQL 8.0+
-- =============================================

CREATE DATABASE IF NOT EXISTS studydesk
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE studydesk;


-- =============================================
-- 1. ADMINS
-- =============================================

CREATE TABLE admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_username (username),
    UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- 2. USERS
-- =============================================

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    academic_level ENUM(
        'school',
        'plus_two',
        'bachelor',
        'master',
        'others'
    ) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_user_username (username),
    UNIQUE KEY uq_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- 3. ADMIN AUDIT LOGS
-- =============================================

CREATE TABLE admin_audit_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_id INT UNSIGNED DEFAULT NULL,
    target_type VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_admin_audit_admin_id (admin_id),

    CONSTRAINT fk_admin_audit_admin
        FOREIGN KEY (admin_id)
        REFERENCES admins(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- 4. TASKS
-- =============================================

CREATE TABLE tasks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    priority ENUM('high', 'medium', 'low') DEFAULT 'medium',
    due_date DATE DEFAULT NULL,
    completed TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_tasks_user_id (user_id),

    CONSTRAINT fk_tasks_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- 5. EXAMS
-- =============================================

CREATE TABLE exams (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    exam_date DATE NOT NULL,
    exam_time TIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_exams_user_id (user_id),

    CONSTRAINT fk_exams_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- 6. TOPICS
-- =============================================

CREATE TABLE topics (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    exam_id INT UNSIGNED NOT NULL,
    text VARCHAR(500) NOT NULL,
    completed TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_topics_exam_id (exam_id),

    CONSTRAINT fk_topics_exam
        FOREIGN KEY (exam_id)
        REFERENCES exams(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- 7. NOTES
-- =============================================

CREATE TABLE notes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) DEFAULT 'Untitled Note',
    body MEDIUMTEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_notes_user_id (user_id),

    CONSTRAINT fk_notes_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- 8. POMODORO SETTINGS
-- =============================================

CREATE TABLE pomodoro_settings (
    user_id INT UNSIGNED NOT NULL,
    work_min TINYINT UNSIGNED DEFAULT 25,
    work_sec TINYINT UNSIGNED DEFAULT 0,
    short_min TINYINT UNSIGNED DEFAULT 5,
    short_sec TINYINT UNSIGNED DEFAULT 0,
    long_min TINYINT UNSIGNED DEFAULT 15,
    long_sec TINYINT UNSIGNED DEFAULT 0,
    long_break_after TINYINT UNSIGNED DEFAULT 4,

    PRIMARY KEY (user_id),

    CONSTRAINT fk_pomodoro_settings_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- 9. POMODORO SESSIONS
-- =============================================

CREATE TABLE pomodoro_sessions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    session_date DATE NOT NULL,
    count SMALLINT UNSIGNED DEFAULT 0,

    PRIMARY KEY (id),
    UNIQUE KEY uq_user_date (user_id, session_date),

    CONSTRAINT fk_pomodoro_sessions_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- 10. LOGIN ATTEMPTS
-- =============================================

CREATE TABLE login_attempts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    identifier VARCHAR(150) NOT NULL,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_identifier (identifier),
    KEY idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- 11. PASSWORD RESETS
-- =============================================

CREATE TABLE password_resets (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_password_reset_token (token),
    KEY idx_password_resets_user_id (user_id),

    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- DATABASE CREATION COMPLETED
-- TOTAL: 11 TABLES
-- =============================================