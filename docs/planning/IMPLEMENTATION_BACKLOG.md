# IMPLEMENTATION BACKLOG (SPRINT 0) — MEAI Edu

> **Versiune document:** 2.0.0 (Revizuit — Model Profesor Individual)  
> **Status:** Backlog Tehnic de Sarcini de Implementare  
> **Cadru de lucru:** Sprint 0 — Fundație & Felie Verticală Demonstrabilă

---

## 1. Sinteza Sarcinilor de Dezvoltare

```mermaid
gantt
    title Planificare Sarcini Sprint 0 (Next.js + Prisma)
    dateFormat  X
    axisFormat %d

    section 1. Bază & Date
    TASK-01 : Scaffolding Next.js & Schemă Prisma   :0, 2
    TASK-02 : Script Seed Date Demo (1 Prof, 4 Grupe, 20 Elevi) :1, 3
    TASK-03 : Server Guards & Izolare Notițe Private :2, 4

    section 2. UI & Navigație
    TASK-04 : Design System Cald & Demo Switcher Bar :3, 5

    section 3. Portale Roluri
    TASK-05 : Portal Profesor - Grupe, Elevi & Notițe Private :4, 7
    TASK-06 : Portal Profesor - Prezență, Teme, Materiale :6, 9
    TASK-07 : Portal Profesor - Evaluări, Feedback, Mesagerie :8, 11
    TASK-08 : Portal Părinte - Sumar Cald, Copii & Dialog :9, 12
    TASK-09 : Portal Elev - Orar, Teme, Materiale & Obiective :10, 13

    section 4. QA & Verificare
    TASK-10 : Suită Teste Automate (ReBAC & E2E)   :12, 14
    TASK-11 : Audit Accesibilitate & Polish Demo    :13, 15
```

---

## 2. Detalierea Sarcinilor de Dezvoltare

### TASK-01: Scaffolding Proiect Next.js & Schemă Prisma
- **Obiectiv:** Inițializarea aplicației Next.js cu TypeScript (`strict: true`), Tailwind CSS și definirea schemei complete în `prisma/schema.prisma`.
- **Modul afectat:** `package.json`, `prisma/schema.prisma`, `src/lib/prisma.ts`
- **Dependințe:** Niciuna.
- **Criterii de acceptare:**
  - Toate modelele (`Teacher`, `Group`, `Student`, `Guardian`, `GuardianLink`, `Lesson`, `AttendanceRecord`, `Assignment`, `LearningMaterial`, `Assessment`, `AssessmentResult`, `PublishedFeedback`, `Announcement`, `Conversation`, `Message`, `LearningGoal`) sunt declarate în Prisma.
  - Generarea clientului Prisma (`npx prisma generate`) reușește fără erori.
- **Teste necesare:** Verificare tipuri TypeScript generate.
- **Considerații de securitate:** Definirea relațiilor cascade corespunzătoare pentru ștergere sigură.

---

### TASK-02: Script de Populare Date Fictive (`prisma/seed.ts`)
- **Obiectiv:** Crearea unui set complet și coerent de date demonstrative în limba română.
- **Modul afectat:** `prisma/seed.ts`
- **Dependințe:** TASK-01
- **Criterii de acceptare:**
  - 1 Profesor: Prof. Radu Teodorescu (Matematică & Informatică).
  - 4 Grupe:
    1. Clasa a VII-a B (Școală)
    2. Pregătire Evaluare Națională (Meditații Matematică)
    3. Robotică & Algoritmi (Atelier Aplicat)
    4. Meditație Individuală 1-la-1 (Performanță / Concursuri)
  - 20 Elevi cu date realiste (ex. Matei Popescu, Sofia Popescu, Andrei Radu etc.).
  - Tutori asociați prin `GuardianLink` (Fam. Popescu având 2 copii înscriși).
  - Orar recurent, ședințe, prezențe consemnate, teme active, materiale PDF/link, rezultate evaluare, notițe private ale profesorului, feedback publicat, anunțuri, mesaje de chat și obiective.
- **Teste necesare:** Execuție cu succes a comenzii `npx prisma db seed`.
- **Considerații de securitate:** 100% date fictive, zero PII real de minori.

---

### TASK-03: Server-Side Security & Izolare Notițe Private
- **Obiectiv:** Implementarea gărzilor de autorizare pe server și a mecanismului de filtrare strictă a notițelor private.
- **Modul afectat:** `src/lib/auth.ts`, `src/lib/guards.ts`
- **Dependințe:** TASK-01
- **Criterii de acceptare:**
  - `assertCanAccessStudent(session, studentId)` blochează accesul părinților neasociați și al elevilor străini.
  - Câmpul `privateNotes` este exclus garantat din interogările executate pentru rolurile `PARENT` și `STUDENT`.
  - Nu se acceptă niciun parametru nesecurizat de client fără validare pe server.
- **Teste necesare:** Teste unitare de securitate și leak prevention.
- **Considerații de securitate:** Protecția confidențialității pedagogice a profesorului.

---

### TASK-04: Design System Cald & Bară Demo Switcher
- **Obiectiv:** Crearea componentelor UI de bază (butoane, carduri, modale, drawer-e, badge-uri) și a barei superioare de comutare a rolurilor demo.
- **Modul afectat:** `src/components/ui/*`, `src/components/layout/*`
- **Dependințe:** TASK-01
- **Criterii de acceptare:**
  - Temă caldă, calmă (indigo/albastru profund, verde salvie, chihlimbar blând).
  - Fără avertizări roșii alarmiste pentru rezultate mai mici.
  - Bara superioară permite comutarea instantanee între: `[🧑‍🏫 Profesor Teodorescu]`, `[👨‍👩‍👧 Părinte Fam. Popescu]`, `[🧑‍🎓 Elev Matei Popescu]`.
- **Teste necesare:** Teste de comutare de roluri și randare componente.
- **Considerații de securitate:** Banner clar de Mod Demonstrativ.

---

### TASK-05: Modulul Profesor — Dashboard, Grupe, Elevi & Notițe Private
- **Obiectiv:** Implementarea vederilor pentru gestionarea grupelor, vizualizarea elevilor și scrierea notițelor private confidențiale.
- **Modul afectat:** `src/app/teacher/dashboard/*`, `src/app/teacher/groups/*`, `src/app/teacher/students/*`
- **Dependințe:** TASK-02, TASK-03, TASK-04
- **Criterii de acceptare:**
  - Dashboard cu ședințele de azi și indicatori sintetici.
  - Vizualizare și editare grupe (inclusiv orar recurent).
  - Dosar elev cu secțiunea securizată „Notițe Private Profesor”.
- **Teste necesare:** Teste E2E pe crearea unei grupe și salvarea unei notițe private.

---

### TASK-06: Modulul Profesor — Prezență, Teme & Materiale Didactice
- **Obiectiv:** Construirea ecranelor pentru consemnarea rapidă a prezenței la ședințe, crearea temelor și distribuirea materialelor de studiu.
- **Modul afectat:** `src/app/teacher/attendance/*`, `src/app/teacher/lessons/*`, `src/app/teacher/assignments/*`
- **Dependințe:** TASK-05
- **Criterii de acceptare:**
  - Consemnare prezență pe butoane tactile rapide (Prezent, Absent, Întârziat, Învoit).
  - Formular de creare temă cu dată limită.
  - Încărcare link-uri / materiale de curs.
- **Teste necesare:** Teste de salvare a prezenței pe ședință.

---

### TASK-07: Modulul Profesor — Evaluări, Feedback Publicat, Anunțuri & Mesagerie
- **Obiectiv:** Implementarea catalogului de rezultate, publicarea aprecierilor către familii, transmiterea anunțurilor și chatul direct cu părinții.
- **Modul afectat:** `src/app/teacher/assessments/*`, `src/app/teacher/feedback/*`, `src/app/teacher/announcements/*`, `src/app/teacher/conversations/*`
- **Dependințe:** TASK-05
- **Criterii de acceptare:**
  - Matrice rezultate evaluare cu opțiune de salvare ciornă sau publicare imediată.
  - Distincție vizuală clară între notița internă a profesorului și feedback-ul publicat familiei.
  - Fir de conversație directă cu fiecare familie în parte.
- **Teste necesare:** Test E2E pe fluxul de publicare feedback și trimitere mesaj.

---

### TASK-08: Modulul Părinte — Dashboard Cald, Copii, Rezultate & Conversații
- **Obiectiv:** Construirea portalului familiei cu sumar săptămânal pozitiv, selector de copii, orar, teme, rezultate cu explicații și dialog cu profesorul.
- **Modul afectat:** `src/app/parent/*`
- **Dependințe:** TASK-04, TASK-07
- **Criterii de acceptare:**
  - Selector facil între Matei și Sofia Popescu.
  - Zero acces la notițele private ale profesorului.
  - Afișarea aprecierilor pozitive și a rezultatelor publicate.
  - Trimitere mesaje către profesor în firul dedicat.
- **Teste necesare:** Teste de verificare a izolării datelor și a conversației.

---

### TASK-09: Modulul Elev — Dashboard Personal, Orar, Teme, Materiale & Obiective
- **Obiectiv:** Construirea portalului elevului, centrat pe claritate, autonomie în învățare și stabilire de obiective personale.
- **Modul afectat:** `src/app/student/*`
- **Dependințe:** TASK-04, TASK-07
- **Criterii de acceptare:**
  - Orar zilnic/săptămânal clar.
  - Listă teme cu posibilitate de bifare personală ca finalizate.
  - Descărcare materiale didactice.
  - Adăugare și urmărire obiective de învățare proprii.
- **Teste necesare:** Test E2E pe fluxul de elev.

---

### TASK-10: Suită de Teste Automate (ReBAC, Leak Prevention & Playwright E2E)
- **Obiectiv:** Consolidarea tuturor testelor de securitate, domeniu și fluxuri E2E.
- **Modul afectat:** `tests/*`
- **Dependințe:** TASK-08, TASK-09
- **Criterii de acceptare:**
  - 100% teste de securitate ReBAC trecute.
  - Zero scurgeri de notițe private verificate automat.
  - Rulare fluentă a testelor Playwright pe toate cele 3 roluri.
- **Teste necesare:** `npm run test` și `npm run test:e2e`.

---

### TASK-11: Audit Accesibilitate, Polish Vizual & Verificare Finală Demo
- **Obiectiv:** Verificarea contrastului, a terminologiei în limba română, a comportamentului pe smartphone și a stabilității generale.
- **Modul afectat:** Întregul proiect
- **Dependințe:** TASK-10
- **Criterii de acceptare:**
  - Zero erori `axe-core` critice de accesibilitate.
  - Text 100% în limba română cu diacritice impecabile.
  - Timp de încărcare și tranziție între ecrane <100ms.
