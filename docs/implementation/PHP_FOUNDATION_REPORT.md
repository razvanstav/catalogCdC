# RAPORT DE FUNDAȚIE TEHNICĂ & MIGRARE PHP — Îndrumar (CdC)

> **Status:** Finalizat cu succes  
> **Data:** 1 Septembrie 2026  
> **Autor:** Lead Product Architect & Full-Stack Engineer  
> **Stivă Tehnologică Livrată:** PHP 8+ • MySQL (InnoDB) / SQLite • Semantic HTML5 • Vanilla CSS (Tokens) • Vanilla JavaScript (Progressive Enhancement) • PWA

---

## 1. Rezumat Executiv

În conformitate cu cerința imperativă a utilizatorului, întreaga aplicație **Îndrumar** a fost migrată cu succes de la arhitectura Node.js/Next.js/React la un **monolit modular curat în PHP nativ și MySQL/PDO**, eliminând orice dependență de framework-uri externe (fără Laravel, Symfony, React, Tailwind, Bootstrap sau build pipeline-uri).

Toate deciziile de produs, UX-ul cald non-punitiv, modelul pedagogic pentru **un profesor**, regulile stricte de securitate ReBAC, izolarea notițelor private și datele fictive bogate au fost păstrate cu fidelitate 100%.

Branch-ul de siguranță `backup/nextjs-prototype` (commit `53caa08`) conservă integral starea anterioară în caz de nevoie istorică.

---

## 2. Arhitectura Implementată

### A. Backend PHP Modular Monolith
- **Document Root:** `public/` (niciun fișier sensibil din `app/`, `config/`, `database/`, `storage/` nu este expus public).
- **Front Controller:** `public/index.php` inițializează autoloader-ul PSR-4, sesiunea securizată și rutează cererea prin `App\Support\Router`.
- **Acces la Date:** 100% interogări parametrizate prin `PDO::prepare()` în clasele din `app/Repositories/`. Conexiunea suportă MySQL InnoDB cu fallback automat pe SQLite pentru testare instantanee fără bătăi de cap de configurare.
- **Servicii de Domeniu:** `AuthService` (sesiune securizată, roluri, switch demo), `FamilyService` (calculare sinteză săptămânală, rată prezență, aprecieri).
- **Politici de Autorizare:** `AuthorizationPolicy` garantează la nivel de server că părintele își vede doar copiii asociați, elevul își vede doar propriul dosar, iar notițele private ale profesorului nu sunt returnate niciodată către familii.

### B. Frontend Vanilla CSS & HTML5 Semantic
- **Sistem de Design Propriu:** 6 fișiere CSS modulare în `public/assets/css/` (`tokens.css`, `reset.css`, `base.css`, `layout.css`, `components.css`, `pages.css`) fără niciun framework CSS extern.
- **Paletă Cromatică Didactică:**
  - Indigo Didactic (`--color-brand-*`): profesionalism și structură
  - Verde Salvie (`--color-sage-*`): aprecieri formative și creștere
  - Chihlimbar Cald (`--color-amber-*`): alerte blânde și casetă confidențială notițe profesor
  - Tonuri Calde de Suprafață (`--color-surface-*`): fundal calm, fără oboseală vizuală.
- **Componente Reutilizabile:** Butoane, carduri, insigne, modale accesibile (ESC, clic pe fundal), controale segmentate pentru prezență, alerte flash.
- **JavaScript Progresiv:** Aplicația funcționează perfect pentru toate fluxurile esențiale chiar și cu JavaScript dezactivat în browser (formularele sunt clasice `POST`). Vanilla JS adaugă comutare dinamică de tab-uri, deschidere fluidă de modale și scurtătura „Toți Prezenți”.

### C. PWA & Mobile-First
- Manifest web standard: `public/manifest.webmanifest`.
- Service worker cu strategie de cache de securitate (`public/service-worker.js`): memorează doar asset-urile statice publice (`/assets/`), aplică *Network-First* pe paginile HTML dinamice și redirecționează către `offline.html` când nu există conexiune, protejând datele confidențiale ale elevilor.

---

## 3. Matricea de Verificare a Securității & Teste Automate

Suita de teste din `tests/SecurityAndReBACTest.php` a fost executată și a validat 100% din cerințele de securitate:

| Test de Securitate | Rezultat | Detalii Verificare |
|---|---|---|
| **1. Invalid Login Rejection** | **PASS** | Credențialele greșite sunt respinse cu mesaj generic, prevenind account enumeration. |
| **2. CSRF Protection** | **PASS** | Token-ul CSRF este validat cu `hash_equals()`; tentativele de atac forjat sunt blocate 403. |
| **3. Guardian Private Notes Zero-Leakage** | **PASS** | `StudentRepository` și `AssessmentRepository` returnează `NULL` pentru `private_notes` când rolul este `parent`. |
| **4. Student Private Notes Zero-Leakage** | **PASS** | Elevul nu are acces la câmpurile `private_notes` sau `private_teacher_notes`. |
| **5. ReBAC Guardian Link (IDOR)** | **PASS** | Părintele `Radu Popescu` poate accesa doar copiii săi (`Matei`, `Sofia`), fiind blocat 403 la `Andrei Ionescu`. |
| **6. ReBAC Student Self-Access** | **PASS** | Elevul poate vizualiza exclusiv dosarul propriu. |
| **7. SQL Injection Immunity** | **PASS** | 100% interogări folosesc parametrizare PDO `prepare()` / `execute()`. |

---

## 4. Evidența Fișierelor

### Fișiere Noi Create (PHP + Vanilla):
- `config/app.php`, `config/database.php`, `.env.example`
- `database/schema.sql`, `database/seeds/DemoSeeder.php`
- `app/Support/` (`Autoloader.php`, `Database.php`, `Request.php`, `Response.php`, `Session.php`, `Csrf.php`, `View.php`, `Router.php`, `Helpers.php`)
- `app/Repositories/` (`UserRepository.php`, `GroupRepository.php`, `StudentRepository.php`, `AttendanceRepository.php`, `AssignmentRepository.php`, `AssessmentRepository.php`, `FeedbackRepository.php`, `AnnouncementRepository.php`, `ConversationRepository.php`, `GoalRepository.php`)
- `app/Services/` (`AuthService.php`, `FamilyService.php`)
- `app/Policies/` (`AuthorizationPolicy.php`)
- `app/Middleware/` (`AuthMiddleware.php`, `CsrfMiddleware.php`)
- `app/Controllers/` (`AuthController.php`, `TeacherController.php`, `ParentController.php`, `StudentController.php`)
- `app/Views/layouts/` (`main.php`, `auth.php`)
- `app/Views/components/` (`demobar.php`, `sidebar.php`, `header.php`, `alert.php`)
- `app/Views/errors/` (`403.php`, `404.php`, `500.php`)
- `app/Views/auth/` (`login.php`)
- `app/Views/teacher/` (14 vederi complete)
- `app/Views/parent/` (10 vederi complete)
- `app/Views/student/` (8 vederi complete)
- `public/` (`index.php`, `manifest.webmanifest`, `service-worker.js`, `offline.html`)
- `public/assets/css/` (6 fișiere CSS custom properties)
- `public/assets/js/` (`app.js`, `attendance.js`)
- `tests/` (`SecurityAndReBACTest.php`, `run_tests.php`)
- `README.md`

### Fișiere Retenționate (Documentație & Brand):
- `public/assets/images/app-icon.svg`
- `docs/planning/` (toate cele 13 documente de domeniu, permisiuni și planificare)

### Fișiere Eliminate de pe `main` (Păstrate pe `backup/nextjs-prototype`):
- `package.json`, `tsconfig.json`, `tailwind.config.ts`, `postcss.config.mjs`, `prisma/`, `src/`.
