# Îndrumar — redesign complet al aplicației

## Ce a fost schimbat

Întregul strat vizual a fost refăcut, nu doar dashboardul. Toate ecranele profesorului, părintelui și elevului folosesc acum același sistem de design:

- suprafață caldă, luminoasă;
- containere mari și rotunjite;
- navigație desktop și bottom navigation pe mobil;
- controale de tip pill;
- accente albastru didactic, verde salvie și chihlimbar;
- carduri cu profunzime discretă;
- chat cu listă de conversații, bule diferențiate, composer mobil și istoric lizibil;
- pagini de autentificare, erori și offline în aceeași identitate vizuală;
- icon nou, geometric, în format SVG.

Toate stilurile inline și toate atributele JavaScript inline au fost eliminate din șabloanele PHP. Stilurile sunt centralizate exclusiv în fișierele CSS din `public/assets/css/`.

## Cauza pentru care schimbările puteau să nu apară în Antigravity

În proiectul inițial existau două surse de interfață:

1. aplicația PHP din `app/Views/`;
2. fișierul `dev_server.py`, care conținea separat șabloane HTML și CSS vechi, încorporate direct în Python.

Dacă Antigravity pornea `dev_server.py`, browserul afișa copia veche inclusă în acel fișier, nu șabloanele PHP modificate. De aceea schimbările făcute în `app/Views/` și în CSS puteau părea că nu funcționează.

În plus, service worker-ul inițial folosea cache-first pentru fișierele CSS și JavaScript și păstra cache-ul `indrumar-static-v1`. Browserul putea continua să afișeze stilurile vechi chiar după modificarea fișierelor.

## Corecțiile aplicate

- `dev_server.py` a fost eliminat complet;
- arhiva vechiului design kit a fost eliminată din proiect;
- instrucțiunile vechi de modernizare au fost eliminate;
- service worker-ul folosește acum o versiune nouă de cache;
- toate cache-urile `indrumar-*` mai vechi sunt șterse la activare;
- CSS și JavaScript folosesc cache-busting cu `filemtime` în layouturile PHP;
- asset-urile folosesc network-first, astfel încât o versiune veche să nu suprascrie redesignul;
- paginile autentificate nu sunt salvate în cache;
- serverul local oficial este PHP, prin `public/router.php`.

## Comanda corectă în Antigravity

Din rădăcina proiectului:

```bash
php -S 127.0.0.1:8000 -t public public/router.php
```

Apoi se deschide:

```text
http://127.0.0.1:8000
```

Nu se mai rulează niciun server Python pentru acest proiect.

## Resetarea unei instalări vechi în browser

După înlocuirea proiectului, o singură dată:

1. Închide toate taburile deschise cu Îndrumar.
2. Deschide DevTools → Application → Service Workers.
3. Apasă `Unregister` pentru vechea înregistrare, dacă încă apare.
4. În Application → Storage, apasă `Clear site data`.
5. Pornește serverul PHP și reîncarcă pagina.

Noua versiune șterge automat cache-urile vechi, dar această operație este utilă dacă browserul încă rulează un service worker instalat de proiectul precedent.

## Fișierele care controlează designul

```text
public/assets/css/tokens.css
public/assets/css/reset.css
public/assets/css/base.css
public/assets/css/layout.css
public/assets/css/components.css
public/assets/css/pages.css
public/assets/js/app.js
public/assets/js/attendance.js
```

Nu trebuie adăugat CSS în șabloanele PHP și nu trebuie folosite `style="..."` sau `onclick="..."`.
