# STACK MIGRATION TO PHP & MYSQL — Îndrumar (CdC)

> **Versiune document:** 1.0.0  
> **Status:** Plan Oficial de Migrare Tehnologică  
> **Noua Stivă Tehnică Obligatorie:** PHP 8+ • MySQL (InnoDB) • Semantic HTML5 • Vanilla CSS • Vanilla JS (Progressive Enhancement)  
> **Data:** 1 Septembrie 2026

---

## 1. Starea Curentă și Motivația Migrării

Proiectul **Îndrumar** a fost conceput și specificat corect ca un asistent de gestiune didactică și conectare caldă cu familiile pentru **un profesor**.

Pentru a asigura o simplitate maximă, independență de pipeline-uri de build, viteză de execuție și compatibilitate garantată cu orice server standard de hosting web (cPanel, LAMP, Docker, servere Linux de școală), întreaga arhitectură trece pe **PHP nativ cu MySQL**, eliminând orice dependență de Node.js runtime, Next.js, React sau Tailwind.

---

## 2. Păstrarea Muncii & Checkpoint de Siguranță

Tot codul prototip anterior a fost salvat și securizat într-un branch dedicat de backup:
- **Git Branch Backup:** `backup/nextjs-prototype` (commit `53caa08`)
- **Documentația de domeniu:** 100% validă și reutilizabilă.

### A. Fișiere și Concepte Reutilizabile
- Toate specificațiile de ecrane din `docs/planning/SCREEN_MAP.md`.
- Modelele de permisiuni și izolarea notițelor private din `docs/planning/USER_ROLES_AND_PERMISSIONS.md`.
- Conținutul bogat și realist al datelor demonstrative (Prof. Radu Teodorescu, 4 grupe, 20 elevi, Fam. Popescu etc.).
- Identitatea vizuală, logo-ul (`public/assets/images/app-icon.svg`) și paleta cromatică caldă (Indigo, Verde Salvie, Chihlimbar Cald, Crem).
- Principiile pedagogice non-punitive (fără clasamente toxice, fără etichetări automate).

### B. Fișiere Incompatibile care se Elimină / Înlocuiesc
- `package.json`, `tsconfig.json`, `tailwind.config.ts`, `postcss.config.mjs`
- `prisma/` (înlocuit cu `database/schema.sql` și migrații/seeder PHP)
- `src/` (înlocuit cu structura PHP `app/`, `public/`, `routes/`, `config/`)

---

## 3. Noua Arhitectură PHP Modular Monolith

```text
catalogCdC/
├── app/
│   ├── Controllers/             # Controlere web (Teacher, Parent, Student, Auth)
│   │   ├── AuthController.php
│   │   ├── Teacher/
│   │   │   ├── DashboardController.php
│   │   │   ├── GroupController.php
│   │   │   ├── StudentController.php
│   │   │   ├── AttendanceController.php
│   │   │   ├── LessonController.php
│   │   │   ├── AssignmentController.php
│   │   │   ├── AssessmentController.php
│   │   │   ├── FeedbackController.php
│   │   │   ├── AnnouncementController.php
│   │   │   ├── ConversationController.php
│   │   │   ├── CalendarController.php
│   │   │   ├── ReportController.php
│   │   │   └── SettingController.php
│   │   ├── Parent/
│   │   │   ├── DashboardController.php
│   │   │   ├── TimetableController.php
│   │   │   ├── AttendanceController.php
│   │   │   ├── AssignmentController.php
│   │   │   ├── ResultController.php
│   │   │   ├── FeedbackController.php
│   │   │   ├── GoalController.php
│   │   │   ├── AnnouncementController.php
│   │   │   └── ConversationController.php
│   │   └── Student/
│   │       ├── DashboardController.php
│   │       ├── TimetableController.php
│   │       ├── AssignmentController.php
│   │       ├── MaterialController.php
│   │       ├── ResultController.php
│   │       ├── FeedbackController.php
│   │       ├── GoalController.php
│   │       └── AnnouncementController.php
│   ├── Models/                  # Entități de domeniu
│   ├── Services/                # Logică de business (ReBAC, Digest, Grading)
│   ├── Repositories/            # Acces la date prin PDO & Prepared Statements
│   ├── Middleware/              # Auth, CSRF, RoleGuard, SessionGuard
│   ├── Policies/                # Reguli ReBAC de autorizare
│   ├── Support/                 # Router, View Renderer, Database, Session, CSRF, Helpers
│   └── Views/                   # Șabloane HTML5 semantice
│       ├── layouts/             # main.php, auth.php
│       ├── components/          # header.php, sidebar.php, demobar.php, alert.php
│       ├── teacher/             # Vederi profesor
│       ├── parent/              # Vederi părinte
│       ├── student/             # Vederi elev
│       ├── auth/                # login.php, demo-switcher.php
│       └── errors/              # 403.php, 404.php, 500.php
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   ├── schema.sql               # Schemă completă MySQL DDL
│   └── seeds/
│       └── DemoSeeder.php       # Script populare date demonstrative
├── public/                      # DOCUMENT ROOT
│   ├── index.php                # Front Controller unic
│   ├── assets/
│   │   ├── css/
│   │   │   ├── tokens.css       # Variabile CSS (culori, spațieri, fonturi)
│   │   │   ├── reset.css        # Reset modern
│   │   │   ├── base.css         # Tipografie & elemente de bază
│   │   │   ├── layout.css       # Grilă & structură ecran
│   │   │   ├── components.css   # Butoane, carduri, insigne, tabele, formulare
│   │   │   ├── pages.css        # Stilizări specifice paginilor
│   │   │   └── print.css        # Stilizare rapoarte printate
│   │   ├── js/
│   │   │   ├── app.js           # Meniu mobil, modale, comutator tab-uri
│   │   │   └── attendance.js    # Scurtături rapide tastatură/touch prezență
│   │   └── images/
│   │       └── app-icon.svg
│   ├── manifest.webmanifest
│   └── service-worker.js
├── routes/
│   └── web.php                  # Mapare rute GET/POST către controlere
├── storage/
│   ├── logs/
│   └── cache/
├── tests/                       # Teste automate PHP
├── .env.example
└── README.md
```

---

## 4. Arhitectura Bazei de Date MySQL (`database/schema.sql`)

Baza de date folosește **MySQL 8+ cu InnoDB, utf8mb4_unicode_ci**, chei străine (`FOREIGN KEY`), constrângeri unice și indexare optimizată.

### Tabele Cheie:
1. `workspaces` (id, name, owner_id, created_at, updated_at)
2. `users` (id, email, password_hash, role, first_name, last_name, phone, avatar_url, is_active, created_at)
3. `teachers` (id, user_id, title, bio, created_at)
4. `students` (id, user_id, workspace_id, first_name, last_name, father_initial, email, phone, date_of_birth, private_notes, created_at)
5. `guardians` (id, user_id, workspace_id, first_name, last_name, email, phone, relationship, created_at)
6. `guardian_student_links` (id, guardian_id, student_id, status, created_at)
7. `groups` (id, workspace_id, name, type, description, color_tag, created_at)
8. `group_enrollments` (id, group_id, student_id, enrolled_at, status)
9. `group_schedules` (id, group_id, day_of_week, start_time, end_time, room_or_link)
10. `lessons` (id, group_id, title, lesson_date, start_time, end_time, lesson_notes, created_at)
11. `attendance_records` (id, lesson_id, student_id, status, note, created_at)
12. `assignments` (id, group_id, title, description, assigned_date, due_date, created_at)
13. `learning_materials` (id, group_id, lesson_id, title, url, file_type, created_at)
14. `assessments` (id, group_id, title, assessment_type, max_score, assessment_date, created_at)
15. `assessment_results` (id, assessment_id, student_id, score, private_teacher_notes, published_feedback, is_published, created_at, updated_at)
16. `published_feedbacks` (id, student_id, content, category, created_at)
17. `announcements` (id, group_id, title, content, created_at)
18. `conversations` (id, teacher_id, guardian_id, student_id, updated_at, created_at)
19. `messages` (id, conversation_id, sender_role, sender_id, content, is_read, sent_at)
20. `learning_goals` (id, student_id, title, target_date, is_completed, completed_at, created_at)
21. `activity_logs` (id, workspace_id, user_id, role, action, target_type, target_id, details, ip_address, created_at)

---

## 5. Sistemul de Design Vanilla CSS

Fără Tailwind sau Bootstrap! Creăm un sistem CSS custom modular bazat pe variabile CSS (`tokens.css`):
- `--color-brand-*`: Paleta de indigo calm (`#365FAF`, `#4A77DA`, `#EFF4FF`).
- `--color-sage-*`: Paleta de verde salvie pentru aprecieri și confirmări (`#059669`, `#10B981`, `#DCFCE7`).
- `--color-amber-*`: Paleta caldă de chihlimbar pentru alerte blânde și notițe private (`#D97706`, `#FEF3C7`).
- `--color-surface-*`: Nuanțe calde de fundal (`#FBFBFA`, `#FFFFFF`, `#F4F4F1`).
- `--radius-*`: Raze moderne de rotunjire (`8px`, `16px`, `24px`).
- `--shadow-*`: Umbre delicate, fără exagerare.

---

## 6. Securitate & ReBAC în PHP

1. **Sesiuni Securizate:** `session_start()` cu setări `cookie_httponly = true`, `cookie_samesite = 'Strict'`, `cookie_secure = true` (în HTTPS).
2. **Protecție CSRF:** Token CSRF generat pe sesiune și verificat la fiecare cerere POST (`CsrfMiddleware`).
3. **Prepared Statements Obligatorii:** 100% din interogări folosesc `PDO::prepare()` cu parametri numiți sau indexați.
4. **Izolarea Notițelor Private:** Metodele din `StudentRepository` și `AssessmentRepository` exclud explicit `private_notes` și `private_teacher_notes` când `session.role !== 'teacher'`.
5. **ReBAC Guardian Link:** Părintele poate accesa datele unui copil doar dacă există o înregistrare activă în `guardian_student_links`.
6. **XSS Protection:** Funcție globală de escaping `e($string)` care apelează `htmlspecialchars($string, ENT_QUOTES, 'UTF-8')`.

---

## 7. Planul de Execuție

1. **Curățarea fișierelor Node/Next.js de pe branch-ul `main`** (fiind deja arhivate pe `backup/nextjs-prototype`).
2. **Crearea structurii de directoare PHP.**
3. **Crearea schemelor de bază de date (`database/schema.sql`) și a seeder-ului PHP.**
4. **Implementarea nucleului PHP (Router, View, Database, Session, CSRF, ReBAC Policy).**
5. **Implementarea sistemului de design Vanilla CSS.**
6. **Implementarea Controlerelor și Vederilor pentru toate cele 3 roluri (Teacher, Parent, Student) + Demo Switcher.**
7. **Adăugarea fișierelor PWA (manifest, service-worker, offline fallback).**
8. **Verificare și Raport Tehnic.**
