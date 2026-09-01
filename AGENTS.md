# Îndrumar — reguli obligatorii pentru agenți

Acest proiect are o singură sursă de adevăr pentru interfață: aplicația PHP din `app/Views/`, împreună cu asset-urile din `public/assets/`.

## Stack fix

- PHP server-rendered;
- MySQL/PDO;
- HTML semantic;
- Vanilla CSS;
- Vanilla JavaScript;
- fără React, Next.js, Vue, Tailwind, Bootstrap, jQuery sau alt framework frontend.

## Design

- întregul produs folosește design system-ul actual din `public/assets/css/`;
- tokenurile se modifică numai în `public/assets/css/tokens.css`;
- nu adăuga `style="..."`, `<style>`, `onclick="..."` sau CSS în PHP;
- nu recrea șabloane paralele, servere alternative sau pagini HTML duplicate;
- nu readuce iconul JPG, vechea paletă sau vechile clase de design;
- orice ecran nou trebuie verificat la 320px, 390px, 768px și desktop;
- funcțiile esențiale trebuie să rămână utilizabile fără JavaScript;
- notițele private ale profesoarei și feedbackul publicat rămân vizual și logic separate.

## Pornire locală

Folosește exclusiv:

```bash
php -S 127.0.0.1:8000 -t public public/router.php
```

Nu crea și nu porni `dev_server.py`.

## Verificare obligatorie înainte de finalizare

```bash
php tests/design_integrity.php
php tests/render_smoke.php
```

Dacă există acces la un driver PDO local, rulează și:

```bash
php tests/run_tests.php
```
