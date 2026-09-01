# PRODUCT SCOPE CORRECTION — MEAI Edu

> **Versiune document:** 1.0.0  
> **Status:** Corecție Formală de Viziune și Domeniu de Produs  
> **Data:** 31 August 2026  
> **Autor:** Lead Product Architect & Senior Engineering Team

---

## 1. Motivul și Necesitatea Corecției

Planificarea inițială a asimilat în mod eronat **MEAI Edu** cu un catalog electronic instituțional la nivel de școală/liceu de stat (cu directori, secretariate, comisii de rectificare, medii anuale oficiale și integrare ministerială).

**Clarificare Viziune Reală:**  
**MEAI Edu** este o **aplicație privată de management educațional, organizare a predării și conectare cu familiile**, concepută pentru **UN PROFESOR (Teacher / Educator Owner)**. 

Profesorul o folosește pentru a-și gestiona activitatea didactică completă, acoperind diverse tipuri de formațiuni:
- Clase de școală (unde predă profesorul)
- Grupe de meditații private (pregătire examen: Evaluare Națională, Bacalaureat, admitere)
- Ateliere și cercuri aplicate (ex. robotică, programare, club de lectură)
- Lecții individuale (pregătire 1-la-1, performanță / recuperare)

---

## 2. Tabel Comparativ: Ce Am Eliminat vs Ce Am Adoptat

| Domeniu | Viziunea Veche (Instituțională - ELIMINAT) | Viziunea Nouă (Teacher-Centric - ADOPTAT) |
| :--- | :--- | :--- |
| **Actor Principal** | Școală întreagă (Director, Secretar, 50+ profesori) | **Un singur Profesor / Educator (Workspace Owner)** |
| **Roluri Utilizator** | Admin Școală, Profesor, Părinte, Elev, Superadmin | **1. Profesor (Owner), 2. Părinte/Tutore, 3. Elev** |
| **Organizare Elevi** | Clase oficiale rigide (ex. Clasa a VII-a B) | **Grupe flexibile:** clase, meditații, ateliere, 1-la-1 |
| **Statut Note/Rezultate** | Note oficiale de stat cu norme ministeriale și teze | **Rezultate și evaluări formative private ale profesorului** |
| **Modificări Rezultate** | Birocrație de rectificare cu aprobare director/secretariat | **Control direct al profesorului + istoric revizii transparent** |
| **Feedback & Notițe** | Doar feedback oficial lângă notă | **Distincție clară: Notițe Private Profesor vs Feedback Publicat Familiilor** |
| **Comunicare** | Anunțuri unidirecționale rigide de la conducere | **Anunțuri grupă + Conversații/Mesaje directe Profesor ↔ Părinte** |
| **Resurse Didactice** | Doar titlu temă | **Materiale de învățare (fișiere, link-uri, fișe) + Teme + Obiective personale** |
| **Arhitectură Tehnică** | Multi-school tenant router, microservicii ipotetice | **Next.js (App Router) + TypeScript + Prisma + PostgreSQL (Monolit Simplu)** |

---

## 3. Ce a fost ELIMINAT din Scope

- ❌ Multi-school tenancy la nivel de interfață (administrare multi-școli, directori, inspectori).
- ❌ Roluri de directori, secretariate, administratori instituționali.
- ❌ Integrări cu ministerul educației (SIIIR, cataloage naționale oficiale).
- ❌ Formule de medii anuale oficiale sau încheieri de situație școlară de stat.
- ❌ Gestiune de personal didactic și abonamente pe unitate școlară.
- ❌ Fluxuri birocratice de aprobare a corectării notelor.

---

## 4. Ce a fost ADĂUGAT și CONSOLIDAT în Scope

- ✅ **Gestiune Grupe & Cursuri:** Creare grupe de meditații, ateliere, clase și ședințe 1-la-1.
- ✅ **Asocieri Tutori – Elevi:** Fiecare elev poate avea 1 sau mai mulți tutori asociați (`GuardianLink`).
- ✅ **Orar & Ședințe Recurente:** Stabilire program săptămânal pentru fiecare grupă.
- ✅ **Prezență Simplă:** Prezent, Absent, Întârziat, Învoit la fiecare lecție.
- ✅ **Materiale de Învățare & Teme:** Atașare resurse educaționale și enunțuri de temă.
- ✅ **Rezultate & Evaluări Formative:** Punctaje, note, calificative sau procentaje private.
- ✅ **Notițe Private Profesor:** Câmp strict confidențial pentru observațiile personale ale profesorului (invizibil părinților și elevilor).
- ✅ **Feedback Publicat către Familie:** Aprecieri formative și recomandări explicite transmise familiei.
- ✅ **Modul de Conversații / Mesagerie:** Canal calm de mesaje asincrone între profesor și părinți.
- ✅ **Obiective Personale de Învățare (Goals):** Stabilire targeturi individuale pentru fiecare elev.
- ✅ **Rapoarte & Sumare Săptămânale Calde:** Generare sinteză de progres pentru familie.

---

## 5. Arhitectura Simplificată

- **Framework:** Next.js (App Router cu Server Actions / Route Handlers)
- **Limbaj:** TypeScript (strict mode)
- **ORM & Bază de date:** Prisma + PostgreSQL (pentru persistență clară și migrații fidele)
- **Autorizare:** Server-side guards bazate pe sesiune (verificare strictă ReBAC părinte-copil și elev-sine)
- **Date Demo:** 1 Profesor, 4 Grupe (Școală, Meditații, Atelier, 1-la-1), ~20 Elevi, Tutori asociați.
