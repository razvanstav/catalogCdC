# Îndrumar

Îndrumar este aplicația privată a unei profesoare pentru gestionarea grupelor, elevilor și comunicării cu părinții. Nu este un catalog instituțional pentru o școală întreagă.

## Roluri

- **Profesor:** grupe, elevi, prezență, ședințe, teme, materiale, evaluări, notițe private, feedback, anunțuri, conversații și rapoarte.
- **Părinte:** informațiile copiilor asociați, orar, prezență, teme, rezultate, feedback, obiective, anunțuri și mesagerie.
- **Elev:** propriul orar, teme, materiale, rezultate, aprecieri și obiective.

## Tehnologie

```text
PHP 8+
MySQL 8 / SQLite pentru dezvoltare
HTML semantic server-rendered
Vanilla CSS
Vanilla JavaScript
PWA
```

Nu sunt folosite Tailwind, Bootstrap, React, Vue, jQuery sau un build pipeline frontend.

## Designul actual

Întregul site folosește aceeași identitate vizuală mobile-first, inspirată de aplicații mobile premium:

- canvas cald și suprafețe albe rotunjite;
- controale de tip pill;
- albastru didactic, verde salvie și chihlimbar cald;
- sidebar desktop și bottom navigation mobilă;
- chat cu listă de conversații, bule clare și composer tactil;
- notițe private distincte de feedbackul publicat;
- focus vizibil și suport pentru reduced motion.

Toate stilurile sunt centralizate în:

```text
public/assets/css/tokens.css
public/assets/css/reset.css
public/assets/css/base.css
public/assets/css/layout.css
public/assets/css/components.css
public/assets/css/pages.css
```

Șabloanele PHP nu conțin CSS inline sau JavaScript inline.

## Pornire locală corectă

Din rădăcina proiectului:

```bash
php -S 127.0.0.1:8000 -t public public/router.php
```

Apoi deschide:

```text
http://127.0.0.1:8000
```

Document root trebuie să fie întotdeauna `public/`.

## De ce designul vechi putea apărea în Antigravity

Proiectul inițial conținea `dev_server.py`, cu o copie separată a interfeței vechi încorporată direct în Python. Dacă acel server era pornit, el nu citea șabloanele PHP actualizate. Fișierul a fost eliminat complet.

Service worker-ul inițial păstra și CSS-ul vechi prin cache-first. Noua versiune:

- șterge cache-urile `indrumar-*` vechi;
- folosește network-first pentru asset-uri;
- folosește cache-busting prin `filemtime`;
- nu salvează în cache paginile autentificate.

Dacă browserul afișează o versiune veche după înlocuirea proiectului, deschide DevTools → Application → Service Workers → `Unregister`, apoi Application → Storage → `Clear site data`.

Instrucțiunile complete sunt în [`ANTIGRAVITY_START_AICI.md`](ANTIGRAVITY_START_AICI.md).

## Configurare bază de date

Copiază `.env.example` în `.env` și configurează MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=indrumar_db
DB_USERNAME=root
DB_PASSWORD=parola_ta
```

Importă schema:

```bash
mysql -u root -p indrumar_db < database/schema.sql
```

Pentru un mediu local care are extensia `pdo_sqlite`, aplicația poate folosi fallback-ul SQLite inclus.

## Conturi demonstrative

- Profesor: `prof.radu@indrumar.ro` / `parola123`
- Părinte: `radu.popescu@familie.ro` / `parola123`
- Elev: `matei.popescu@elev.ro` / `parola123`

Datele sunt fictive.

## Teste

Verificarea designului și randarea tuturor ecranelor nu necesită o bază de date:

```bash
php tests/design_integrity.php
php tests/render_smoke.php
```

Testele funcționale care folosesc baza de date se rulează separat:

```bash
php tests/run_tests.php
```

Suita verifică autentificarea, CSRF, relația părinte–copil, izolarea notițelor private și interogările parametrizate. Mediul PHP trebuie să aibă cel puțin un driver PDO: `pdo_mysql` sau `pdo_sqlite`.

Raportul complet de validare este în `docs/design/VALIDATION_REPORT.md`.

## Structură

```text
app/                 controlere, repository-uri, servicii, politici și views
database/            schema MySQL și date demonstrative
public/              document root, CSS, JavaScript, manifest și service worker
routes/               rutele aplicației
tests/                teste automate
docs/design/          auditul redesignului
```
