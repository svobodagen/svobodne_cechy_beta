# Grafický systém Svobodné Cechy (v2.1)

Tento dokument definuje vizuální identitu a komponentový systém platformy Svobodné Cechy. Systém je navržen pro maximální vizuální dopad, prémiový pocit a přehlednost na mobilních i desktopových zařízeních.

## 1. Barevná paleta

Systém využívá silný kontrast mezi temným pozadím a výraznými akcenty.

| Barva | Hex | Použití |
| :--- | :--- | :--- |
| **Accent (Gold)** | `#f5b932` | Primární akce, ikony v harmonice, zvýraznění. |
| **Background Black** | `#000000` | Hlavní pozadí stránky, tmavé karty. |
| **Background Light**| `#f7f7f7` | Sekundární sekce (statistické lišty, světlé karty). |
| **Text White** | `#ffffff` | Hlavní text na tmavém pozadí. |
| **Text Dark** | `#1a1a1a` | Hlavní text na světlém pozadí. |
| **Text Muted** | `#71717a` | Doplňkové informace, popisky, neaktivní prvky. |
| **Border** | `rgba(255,255,255,0.1)` | Jemné oddělovače na tmavém pozadí. |

### Návrh na vylepšení barev:
- **Zlatý gradient:** Pro tlačítka nepoužívat jen plochou barvu, ale gradient `linear-gradient(135deg, #f5b932 0%, #d49a1a 100%)`.
- **Glassmorphism:** Pro plovoucí prvky používat `rgba(255,255,255,0.05)` s `backdrop-filter: blur(12px)` a tenkým bílým okrajem.

---

## 2. Typografie

Používáme moderní bezpatkové písmo s výraznými řezy pro hierarchii.

- **Hlavní font:** `Plus Jakarta Sans` (400, 500, 600, 700, 800)
- **Nadpisový font (Logo/Display):** `Bungee` (pro specifické brandové prvky)

### Typografické styly:
- **Hero Nadpis (H1):** `44px`, weight `800`, `line-height: 0.95`, uppercase.
- **Sekční nadpis (H2):** `20px`, weight `800`, uppercase, doprovázen ikonou.
- **Název harmoniky:** `15px`, weight `800`, uppercase.
- **Citát / Motto:** `18px`, italic, vlevo žlutá čára (`3px solid var(--accent)`).
- **Popisky (Labels):** `11px`, weight `800`, uppercase, letter-spacing `1.5px`.

---

## 3. Komponenty

### Tlačítka (Buttons)
Všechna tlačítka mají `border-radius: 7px`, `font-weight: 800` a `text-transform: uppercase`.

1. **Primární:** Žluté pozadí, černý text. Při najetí (hover) mírné zesvětlení a stín.
2. **Obrysové (Outline):** Transparentní s bílým rámečkem (2px), bílý text.
3. **Inverzní:** Používá se v tmavých sekcích (např. kontaktní formulář).

### Rozbalovací menu (Harmoniky)
Dva typy pro vizuální oddělení obsahu:

1. **Standardní (Světlý):**
   - Bílé pozadí, šedý rámeček.
   - Ikona v černém kruhu se žlutým symbolem.
   - Černý nadpis, tlumený podnadpis.
2. **Inverzní (Tmavý):**
   - Černé pozadí, výraznější stín.
   - Ikona ve žlutém kruhu s černým symbolem.
   - Bílý nadpis, žlutý podnadpis.

### Ikony
- Používáme **Bootstrap Icons** (`bi-*`).
- Ikony sociálních sítí jsou v jedné řadě, tmavé, s hover efektem do akcentní barvy.

---

## 4. Specifické vizuální prvky

### Žlutá čára u textu
- Používá se u citátů nebo klíčových odstavců.
- `border-left: 3px solid var(--accent); padding-left: 15px;`
- **Vylepšení:** Čára může mít jemný gradient shora dolů nebo jemnou animaci "pulsování" šířky při scrollu.

### Galerie
- Horizontální skrolování s přichytáváním (`scroll-snap-type: x mandatory`).
- Indikátor pořadí v rohu každého obrázku (`01/05`).
- **Vylepšení:** Přidat mírné zvětšení aktivního obrázku a plynulý přechod mezi nimi.

---

## 5. Plán vylepšení (Premium Look)

1. **Mikro-interakce:**
   - Při kliknutí na tlačítko jemné zmenšení (`transform: scale(0.97)`).
   - Harmoniky by měly mít plynulejší animaci otevírání pomocí CSS Grid (`grid-template-rows`).
2. **Hloubka a stíny:**
   - Místo jednoduchých stínů používat vrstvené stíny pro realističtější pocit.
3. **Skeleton Loading:**
   - Při načítání profilu zobrazovat šedé pulzující tvary místo textu "Načítám...".
4. **Interaktivní pozadí:**
   - Hero sekce může mít jemný paralaxní efekt při pohybu myší/naklánění telefonu.
