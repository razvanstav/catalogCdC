# USER FLOWS & INTERACTION JOURNEYS — MEAI Edu

> **Versiune document:** 2.0.0 (Revizuit — Model Profesor Individual)  
> **Status:** Specificație Fluxuri Utilizator & Diagrame de Interacțiune  
> **Platformă:** MEAI Edu (Gestiune Didactică & Conectare Familie)

---

## 1. Fluxul Profesorului: Consemnare Rezultat Evaluare & Publicare Feedback

Acest flux descrie cum profesorul (Prof. Radu Teodorescu) introduce rezultatul unei evaluări (ex. Testul la Geometrie la Grupa de Pregătire Evaluare Națională), adaugă o notiță privată confidențială și publică o apreciere caldă către părinți.

```mermaid
sequenceDiagram
    autonumber
    actor T as Profesor (Radu Teodorescu)
    participant UI as Portal Profesor (Teacher UI)
    participant Auth as Server Auth & ReBAC Guard
    participant DB as Bază Date (Prisma / PostgreSQL)
    participant Feed as Feed Părinte / Elev

    T->>UI: Deschide Evaluarea "Test Geometrie - Triunghiuri Asemenea"
    T->>UI: Selectează elevul Matei Popescu
    T->>UI: Introduce Notă/Punctaj: 9.50 / 10
    T->>UI: Scrie Notiță Privată: "A ezitat la cazul 2 de asemănare, dar a recuperat pe parcurs."
    T->>UI: Scrie Feedback Publicat: "Felicitări, Matei! O lucrare foarte îngrijită și logică."
    T->>UI: Bifează "Publică Feedback către Părinți" și apasă "Salvează"
    
    UI->>Auth: Validează rolul de TEACHER (Owner)
    Auth->>DB: Salvează AssessmentResult (score: 9.50, privateNotes, publishedFeedback, status: PUBLISHED)
    DB-->>UI: Confirmare salvare
    
    Note over DB,Feed: Notița privată rămâne strict în DB-ul profesorului
    DB->>Feed: Publică rezultatul 9.50 și mesajul cald în portalul părintelui și al elevului
    UI-->>T: Afișează confirmarea salvării și insigna verde "Publicat către Familie"
```

---

## 2. Fluxul Profesorului: Desfășurare Ședință, Prezență & Notițe de Curs

```mermaid
flowchart TD
    Start["Profesorul deschide /teacher/attendance"] --> SelectGroup["Selectează Grupa: 'Robotică & Algoritmi'"]
    SelectGroup --> MarkAtt["Marchează prezența rapid (Prezent / Absent / Întârziat)"]
    MarkAtt --> LessonNotes["Adaugă Notiță Privată de Ședință: 'Am lucrat pe senzorul ultrasonic'"]
    LessonNotes --> Homework["Creează Tema pentru acasă: 'Rezolvare fișă algoritmi #3'"]
    Homework --> Attach["Atașează fișierul PDF suport și link util"]
    Attach --> Save["Salvare Ședință & Trimitere Notificare Calmă către Elevi și Părinți"]
```

---

## 3. Fluxul Părintelui: Sumar Săptămânal, Comutare Copii & Conversație Directă

```mermaid
sequenceDiagram
    autonumber
    actor P as Părinte (Radu Popescu)
    participant UI as Portal Părinte (Parent UI)
    participant Auth as Server ReBAC Guard (GuardianLink)
    participant DB as Bază Date (Prisma)

    P->>UI: Accesează /parent/dashboard
    UI->>Auth: Identifică copiii validați ai părintelui Popescu
    Auth->>DB: Interogare GuardianLink (status=ACTIVE)
    DB-->>UI: Returnează [Matei Popescu, Sofia Popescu]
    
    UI->>DB: Încarcă Sumarul pentru Matei (orare, teme, aprecieri recente, rezultate)
    DB-->>UI: Date fără Notițele Private ale profesorului
    UI-->>P: Afișează Dashboard-ul cald cu aprecierile lui Matei
    
    P->>UI: Deschide /parent/conversations
    P->>UI: Scrie mesaj: "Bună ziua, domnule profesor! Matei a lucrat cu mult drag la geometrie."
    UI->>DB: Salvează Message (senderId=părinte, receiverId=profesor)
    DB-->>UI: Mesaj transmis
    UI-->>P: Mesajul apare în firul de conversație
```

---

## 4. Fluxul Elevului: Orar, Teme, Materiale & Obiective de Învățare

```mermaid
flowchart LR
    E["Elevul Matei intră pe /student/dashboard"] --> Orar["Consultă Orarul Ședinței de Azi (ora 16:00)"]
    Orar --> Materiale["Deschide Materialul Didactic atașat la Geometrie"]
    Materiale --> Teme["Verifică Tema și bifează 'Finalizată' după rezolvare"]
    Teme --> Obiectiv["Adaugă Obiectiv Personal: 'Să rezolv 10 probleme din culegere'"]
```

---

## 5. Fluxul Comutatorului de Roluri Demo (Demo Switcher)

```mermaid
sequenceDiagram
    autonumber
    actor User as Utilizator Demo
    participant Switcher as Bară Demo Switcher
    participant Session as Demo Session Provider
    participant Router as Next.js Router

    User->>Switcher: Click pe [👨‍👩‍👧 Părinte - Fam. Popescu]
    Switcher->>Session: setDemoRole("PARENT", parentId="usr_parent_popescu")
    Session->>Router: Redirecționare către /parent/dashboard
    Router-->>User: Randare instantanee a portalului familiei cu datele copiilor Matei și Sofia
```
