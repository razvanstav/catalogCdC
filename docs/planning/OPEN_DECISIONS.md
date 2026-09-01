# OPEN DECISIONS & ARCHITECTURAL TRADEOFFS — MEAI Edu

> **Versiune document:** 2.0.0 (Revizuit — Model Profesor Individual)  
> **Status:** Registru Decizii de Arhitectură (ADR) & Întrebări Deschise  
> **Platformă:** MEAI Edu (Gestiune Didactică & Conectare Familie)

---

## 1. Registru Decizii de Arhitectură (ADRs)

### ADR-01: Framework & Structură Aplicație (Next.js Monolith vs Separate Backend)
- **Context:** Dorim o arhitectură simplă, rapidă, fiabilă și ușor de întreținut pentru un profesor și utilizatorii săi.
- **Opțiuni analizate:**
  1. *Next.js Monolith (App Router + Server Actions):* Un singur depozit de cod, randare rapidă, zero duplicare de modele TypeScript, server-side auth integrat.
  2. *NestJS Backend + React SPA Frontend separată:* Introduce complexitate de rețea, CORS, DTO-uri duplicate și mentenanță dublă fără niciun beneficiu concret pentru acest stadiu.
- **Recomandare:** **Next.js Monolith**. Este cea mai curată și robustă opțiune.
- **Status:** **ADOPTAT**.

---

### ADR-02: Bază de Date & ORM (PostgreSQL + Prisma)
- **Context:** Avem nevoie de relații clare (profesor, grupe, elevi, tutori, ședințe, evaluări, mesaje) și migrații controlate.
- **Opțiuni analizate:**
  1. *PostgreSQL + Prisma ORM:* Tipare automată TypeScript de la schemă, migrații declarative simple, relații relationate fidele și script de seed facil.
  2. *Document Database (MongoDB):* Risc de inconsistență pe relațiile n-la-n dintre elevi, tutori și grupe.
- **Recomandare:** **PostgreSQL + Prisma**.
- **Status:** **ADOPTAT**.

---

### ADR-03: Modelarea Tipurilor de Grupe (Enum `GroupType`)
- **Context:** Profesorul predă atât la clase de școală, cât și la meditații particulare, ateliere aplicate sau lecții individuale 1-la-1.
- **Opțiuni analizate:**
  1. *Tabele separate pentru fiecare tip de grupă:* Structură redundantă și rigidă.
  2. *Entitate unică `Group` cu atribut discriminator `type: GroupType`:* Permite reutilizarea întregului modul de prezență, teme, materiale și orar indiferent de natura grupei.
- **Recomandare:** **Entitate unică `Group` cu enum `GroupType`** (`SCHOOL_CLASS`, `TUTORING_GROUP`, `WORKSHOP`, `INDIVIDUAL_LESSON`).
- **Status:** **ADOPTAT**.

---

### ADR-04: Mecanismul de Izolare a Notițelor Private (Private Notes)
- **Context:** Notițele interne ale profesorului despre elevi nu trebuie să poată fi interceptate niciodată de părinți sau elevi.
- **Opțiuni analizate:**
  1. *Filtrare la nivel de interogare Prisma (Query Projection `select: { privateNotes: false }`):* Soluție simplă, performantă și sigură la nivel de cod.
  2. *Tabelă separată criptată:* Adaugă overhead de criptare/decriptare simetrică.
- **Recomandare:** **Filtrare la nivel de interogare Prisma + DTO-uri separate** de răspuns pentru rolurile `PARENT` și `STUDENT`, completată de teste automate de leak detection.
- **Status:** **ADOPTAT**.

---

### ADR-05: Modulul de Conversații (Mesagerie Asincronă vs WebSocket Chat)
- **Context:** Părinții doresc să pună întrebări profesorului, dar profesorul nu trebuie întrerupt în timpul orelor de predare.
- **Opțiuni analizate:**
  1. *Mesagerie calmă asincronă (fir de conversație pe familie):* Ritm sănătos de comunicare, fără presiunea răspunsului instantaneu.
  2. *Chat WebSocket în timp real:* Încurajează un comportament intruziv de asaltare a profesorului la orice oră.
- **Recomandare:** **Mesagerie asincronă structurată** (Next.js Server Actions + polling discret sau revalidare de pagină).
- **Status:** **ADOPTAT**.

---

## 2. Întrebări Deschise pentru Revizuirea Umană

1. **Datele Demonstrative ale Profesorului:** Este agreat profilul demonstrativ **„Prof. Radu Teodorescu — Matematică & Informatică”** cu cele 4 grupe propuse?
2. **Formatele de Rezultate:** Pentru evaluări, este preferat sistemul de notare zecimal (1.00 - 10.00) sau cel pe punctaje/procente (ex. 85/100 pct)? Modelul actual suportă ambele variante prin câmpul `score` și `maxScore`.
3. **Structura Sesiunii Demo:** Sunteți de acord cu menținerea barei superioare permanente de comutare a rolurilor (`[🧑‍🏫 Profesor]`, `[👨‍👩‍👧 Părinte]`, `[🧑‍🎓 Elev]`) pentru testare rapidă și fluidă în browser?
