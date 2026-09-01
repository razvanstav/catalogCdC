-- ==========================================================
-- Îndrumar (CdC) — Baza de Date Didactică MySQL (InnoDB)
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Workspaces (Spațiul didactic al profesorului)
CREATE TABLE IF NOT EXISTS `workspaces` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `owner_id` VARCHAR(36) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_workspaces_owner` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Utilizatori (Conturi de autentificare)
CREATE TABLE IF NOT EXISTS `users` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('teacher', 'parent', 'student') NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50) NULL,
  `avatar_url` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Profil Profesor
CREATE TABLE IF NOT EXISTS `teacher_profiles` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `user_id` VARCHAR(36) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NULL,
  `bio` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_teacher_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Profil Elev
CREATE TABLE IF NOT EXISTS `student_profiles` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `user_id` VARCHAR(36) NULL UNIQUE,
  `workspace_id` VARCHAR(36) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `father_initial` VARCHAR(10) NULL,
  `email` VARCHAR(191) NULL,
  `phone` VARCHAR(50) NULL,
  `date_of_birth` DATE NULL,
  `is_paid` TINYINT(1) NOT NULL DEFAULT 1,
  `private_notes` TEXT NULL, -- STRICT SECRET: Doar profesorul are acces
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_student_workspace` (`workspace_id`),
  CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_student_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Profil Părinte / Tutore
CREATE TABLE IF NOT EXISTS `guardian_profiles` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `user_id` VARCHAR(36) NOT NULL UNIQUE,
  `workspace_id` VARCHAR(36) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `relationship` ENUM('mother', 'father', 'legal_guardian') NOT NULL DEFAULT 'legal_guardian',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_guardian_workspace` (`workspace_id`),
  CONSTRAINT `fk_guardian_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_guardian_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Legături Tutore – Elev (ReBAC)
CREATE TABLE IF NOT EXISTS `guardian_student_links` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `guardian_id` VARCHAR(36) NOT NULL,
  `student_id` VARCHAR(36) NOT NULL,
  `status` ENUM('active', 'pending') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_guardian_student` (`guardian_id`, `student_id`),
  CONSTRAINT `fk_link_guardian` FOREIGN KEY (`guardian_id`) REFERENCES `guardian_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_link_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Grupe & Cursuri Didactice
CREATE TABLE IF NOT EXISTS `groups` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `workspace_id` VARCHAR(36) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `type` ENUM('school_class', 'tutoring_group', 'workshop', 'individual_lesson') NOT NULL DEFAULT 'tutoring_group',
  `description` TEXT NULL,
  `color_tag` VARCHAR(20) NOT NULL DEFAULT '#4A77DA',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_groups_workspace` (`workspace_id`),
  CONSTRAINT `fk_groups_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Înscrieri Elevi în Grupe
CREATE TABLE IF NOT EXISTS `group_enrollments` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `group_id` VARCHAR(36) NOT NULL,
  `student_id` VARCHAR(36) NOT NULL,
  `enrolled_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  UNIQUE KEY `uk_group_student` (`group_id`, `student_id`),
  CONSTRAINT `fk_enr_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enr_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Orare Recurente
CREATE TABLE IF NOT EXISTS `group_schedules` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `group_id` VARCHAR(36) NOT NULL,
  `day_of_week` TINYINT NOT NULL, -- 1=Luni, 7=Duminica
  `start_time` VARCHAR(10) NOT NULL,
  `end_time` VARCHAR(10) NOT NULL,
  `room_or_link` VARCHAR(255) NULL,
  INDEX `idx_schedules_group` (`group_id`),
  CONSTRAINT `fk_schedules_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Ședințe de Curs / Lecții
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `group_id` VARCHAR(36) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `lesson_date` DATE NOT NULL,
  `start_time` VARCHAR(10) NOT NULL,
  `end_time` VARCHAR(10) NOT NULL,
  `lesson_notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_lessons_group` (`group_id`),
  INDEX `idx_lessons_date` (`lesson_date`),
  CONSTRAINT `fk_lessons_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Prezență la Ședințe
CREATE TABLE IF NOT EXISTS `attendance_records` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `lesson_id` VARCHAR(36) NOT NULL,
  `student_id` VARCHAR(36) NOT NULL,
  `status` ENUM('present', 'absent', 'late', 'excused') NOT NULL DEFAULT 'present',
  `note` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_attendance_lesson_student` (`lesson_id`, `student_id`),
  CONSTRAINT `fk_att_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Teme pentru Acasă
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `group_id` VARCHAR(36) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `assigned_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_assignments_group` (`group_id`),
  CONSTRAINT `fk_asg_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Materiale Didactice
CREATE TABLE IF NOT EXISTS `learning_materials` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `group_id` VARCHAR(36) NOT NULL,
  `lesson_id` VARCHAR(36) NULL,
  `title` VARCHAR(255) NOT NULL,
  `url` VARCHAR(500) NULL,
  `file_type` ENUM('pdf', 'video', 'link', 'doc') NOT NULL DEFAULT 'pdf',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_materials_group` (`group_id`),
  CONSTRAINT `fk_mat_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mat_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Evaluări
CREATE TABLE IF NOT EXISTS `assessments` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `group_id` VARCHAR(36) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `assessment_type` ENUM('test', 'oral', 'project', 'worksheet', 'homework_check') NOT NULL DEFAULT 'test',
  `max_score` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  `assessment_date` DATE NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_assessments_group` (`group_id`),
  CONSTRAINT `fk_asm_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Rezultate Evaluare (cu notițe private și feedback publicat separat)
CREATE TABLE IF NOT EXISTS `assessment_results` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `assessment_id` VARCHAR(36) NOT NULL,
  `student_id` VARCHAR(36) NOT NULL,
  `score` DECIMAL(5,2) NOT NULL,
  `private_teacher_notes` TEXT NULL, -- STRICT SECRET: Doar profesorul are acces
  `published_feedback` TEXT NULL,     -- Feedback cald publicat familiei
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_assessment_student` (`assessment_id`, `student_id`),
  CONSTRAINT `fk_res_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Aprecieri Formative Publicate
CREATE TABLE IF NOT EXISTS `published_feedbacks` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `student_id` VARCHAR(36) NOT NULL,
  `content` TEXT NOT NULL,
  `category` ENUM('progress', 'encouragement', 'attention') NOT NULL DEFAULT 'progress',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_feedbacks_student` (`student_id`),
  CONSTRAINT `fk_fb_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Anunțuri pe Grupă
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `group_id` VARCHAR(36) NULL, -- NULL = Anunt general pe workspace
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_announcements_group` (`group_id`),
  CONSTRAINT `fk_ann_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Conversații Directe Părinte – Profesor
CREATE TABLE IF NOT EXISTS `conversations` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `teacher_id` VARCHAR(36) NOT NULL,
  `guardian_id` VARCHAR(36) NOT NULL,
  `student_id` VARCHAR(36) NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_conv_teacher_guardian` (`teacher_id`, `guardian_id`),
  CONSTRAINT `fk_conv_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_conv_guardian` FOREIGN KEY (`guardian_id`) REFERENCES `guardian_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_conv_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Mesaje în Conversație
CREATE TABLE IF NOT EXISTS `messages` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `conversation_id` VARCHAR(36) NOT NULL,
  `sender_role` ENUM('teacher', 'parent', 'student') NOT NULL,
  `sender_id` VARCHAR(36) NOT NULL,
  `content` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_messages_conversation` (`conversation_id`),
  CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Obiective Personale de Învățare (Goals)
CREATE TABLE IF NOT EXISTS `learning_goals` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `student_id` VARCHAR(36) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `target_date` DATE NULL,
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `completed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_goals_student` (`student_id`),
  CONSTRAINT `fk_goals_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Jurnal de Audit & Activitate
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` VARCHAR(36) NOT NULL PRIMARY KEY,
  `workspace_id` VARCHAR(36) NOT NULL,
  `user_id` VARCHAR(36) NOT NULL,
  `role` VARCHAR(50) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `target_type` VARCHAR(50) NOT NULL,
  `target_id` VARCHAR(36) NOT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_logs_workspace` (`workspace_id`),
  INDEX `idx_logs_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
