# Pornește Îndrumar corect în Antigravity

## Important: nu suprascrie folderul vechi

Pentru a elimina complet designul anterior, nu copia fișierele noi peste folderul vechi. O copiere de tip merge poate păstra `dev_server.py`, arhive vechi sau alte copii ale interfeței.

1. Închide serverul și proiectul vechi din Antigravity.
2. Redenumește folderul vechi în `catalogCdC-main-VECHI`.
3. Extrage arhiva nouă într-un folder proaspăt numit `catalogCdC-main`.
4. Deschide în Antigravity numai folderul nou.
5. După ce verifici proiectul nou, poți șterge backupul vechi.

## Structura care trebuie deschisă

Rădăcina proiectului trebuie să conțină direct:

```text
app/
public/
routes/
database/
AGENTS.md
```

Nu deschide un nivel de folder deasupra și nu deschide arhiva ZIP direct.

## Nu rula serverul Python vechi

`dev_server.py` a fost eliminat intenționat. Acesta conținea o copie separată a designului anterior și era motivul principal pentru care modificările din PHP/CSS nu apăreau.

## Comanda corectă

În terminalul Antigravity, din rădăcina proiectului:

```bash
php -S 127.0.0.1:8000 -t public public/router.php
```

Alternativ:

```bash
./bin/start-local.sh
```

Deschide apoi:

```text
http://127.0.0.1:8000
```

## Resetarea vechiului cache din browser

O singură dată după înlocuirea proiectului:

1. DevTools → Application → Service Workers → `Unregister`.
2. Application → Storage → `Clear site data`.
3. Închide tabul vechi.
4. Pornește din nou serverul PHP.
5. Deschide din nou `http://127.0.0.1:8000`.

Noul service worker șterge automat vechile cache-uri `indrumar-*`, însă resetarea manuală elimină și un worker vechi care ar putea fi încă activ.

## Verificarea proiectului

```bash
php tests/design_integrity.php
php tests/render_smoke.php
```

Ambele trebuie să afișeze `PASS`.

## Reguli permanente pentru Antigravity

Aceste reguli se află și în `AGENTS.md` și `.agents/rules/`:

- nu recrea `dev_server.py`;
- nu introduce framework CSS sau frontend;
- nu adăuga stiluri ori JavaScript inline;
- nu crea o copie paralelă a șabloanelor;
- păstrează PHP ca sursă unică pentru interfața server-rendered;
- modifică paleta și tokenurile numai în `public/assets/css/tokens.css`;
- verifică fiecare ecran nou pe telefon și desktop.
