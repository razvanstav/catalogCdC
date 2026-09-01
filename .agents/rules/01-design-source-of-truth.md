---
description: Protejează redesignul complet Îndrumar și împiedică reapariția interfeței vechi.
---

# Sursa unică pentru UI

- `app/Views/` este singura sursă de șabloane server-rendered.
- `public/assets/css/` este singura sursă de stil.
- `public/assets/js/` conține numai progressive enhancement în JavaScript vanilla.
- Nu genera servere Python, copii HTML ale paginilor sau CSS încorporat în alte fișiere.
- Nu folosi stiluri și event handlers inline.
- Nu modifica designul prin frameworkuri sau biblioteci UI.
- Păstrează limbajul vizual: canvas cald, suprafețe rotunjite, albastru didactic, salvie pentru progres și chihlimbar pentru atenționări/notițe private.
- Pe mobil, păstrează bottom navigation, controale tactile și lipsa overflow-ului orizontal.
