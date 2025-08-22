
# Cursor “Remember” Document — Word Search Web App

> **Purpose:** This document tells Cursor/ChatGPT exactly what we’re building, the tech choices, conventions, and where things live. Treat it as persistent project memory. When generating code, follow these rules by default.

---

## 1) Project Overview

* **App:** Browser-based **Word Search game**.
* **Goals:** Fast, simple, mobile-friendly; users can play generated puzzles, optionally create/share custom lists; printable PDF later.
* **Primary stack:** **PHP (8.x)** + **Nginx** on Linux, **jQuery** for interactivity, **Bootstrap 5** for styling, **Composer** for PHP deps/autoloading.
* **Style:** Clean Bootstrap UI; **no Bootstrap `nav` component** unless explicitly asked. Use responsive grid/cards/buttons.

---

## 2) Default Tech Assumptions

* **PHP:** 8.2+; strict types in new classes; PSR-12 coding style.
* **Composer:** PSR-4 autoloading; env loader (vlucas/phpdotenv) if needed.
* **Nginx:** Standard PHP-FPM setup; friendly URLs routed to `public/index.php`.
* **Frontend:** jQuery 3.7+, Bootstrap 5.3+, icons via Bootstrap Icons (if needed). Keep JS lean; no frameworks (React/Vue) unless requested.
* **Database:** Start **file-based JSON** persistence for prototypes; can add PostgreSQL later when requested.

---

## 3) File/Folder Structure (top-level)

```
/wordsearch
├── app/                    # PHP source (PSR-4: App\)
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Http/
│   │   └── Router.php
│   └── Utils/
├── bootstrap/              # App bootstrap (wiring, DI, config)
│   └── app.php
├── config/
│   ├── app.php             # App settings (title, debug, etc.)
│   ├── game.php            # Puzzle defaults (grid sizes, alphabets)
│   └── paths.php
├── public/                 # Web root served by Nginx
│   ├── index.php           # Front controller / router entry
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── views/              # PHP partials for layout and pages
│       ├── layout.php
│       ├── home.php
│       ├── play.php
│       └── create.php
├── resources/              # Raw assets (scss, templates) (optional)
├── storage/                # Writable dir (logs, json data, cache)
│   ├── logs/
│   ├── puzzles/            # Saved/generated puzzles (.json)
│   └── cache/
├── tests/                  # (optional) PHPUnit
├── vendor/
├── composer.json
├── composer.lock
├── .env                    # Environment config (never commit real secrets)
├── .env.example
└── README.md
```

---

## 4) Routing & Pages

* **Routing:** Minimal internal router (`app/Http/Router.php`) mapping clean paths to controller callables.
* **Pages:**

  * `/` → Home (New Game button, Difficulty selector, Theme selector)
  * `/play` → Active puzzle UI (grid, word list, timer, hint button)
  * `/create` → Custom word list form; generate & share link
  * `/api/*` → JSON endpoints (see below)

---

## 5) Core Features & MVP Scope

* Generate a word search grid from a word list (horizontal/vertical/diagonal; reversals toggled by difficulty).
* UI for selecting words in the grid via click+drag (mouse) and touch.
* Highlight found words; strike-through in the list; win state when all words found.
* Difficulty presets: **Easy (10×10), Medium (12×12), Hard (15×15)**; allow diagonals/reverse by level.
* Theme packs (animals, geography, tech) pulled from JSON files.
* Persistent **share link** for a generated puzzle (store JSON in `storage/puzzles/` with short id).

---

## 6) API Endpoints (PHP, return JSON)

* `POST /api/generate` → body: `{ words: string[], options: { size:int, diagonals:bool, reverse:bool, seed?:int } }` → returns puzzle `{ id, grid, words, placed, size, seed }` & saves to storage.
* `GET  /api/puzzle/{id}` → returns saved puzzle by id.
* `POST /api/validate` → body: `{ id, selection: [{r0,c0,r1,c1}, ...] }` → validates a found word (optional in MVP; can validate client-side initially).

**Notes for Cursor:** Prefer server-side generation for consistency & shareability; keep client lightweight.

---

## 7) PHP Services (sketch)

* `App\Services\PuzzleGenerator` — place words with backtracking:

  * Placement orders: randomize directions by options; attempt overlaps where letters match; retry with backtracking; fill empty with random A–Z.
  * Accept **seed** for reproducible puzzles (`mt_srand(seed)`).
* `App\Services\PuzzleStore` — save/load puzzles as JSON to `storage/puzzles/{id}.json` (id = short, e.g., base36 timestamp+rand).
* `App\Utils\Id` — short id generator.

---

## 8) Frontend Conventions (jQuery + Bootstrap)
t
* **jQuery only** for DOM/state; no global pollution — scope code in IIFEs or modules.
* Use **Bootstrap 5** layout: `.container`, `.row`, `.col` with responsive breakpoints. Keep font sizes readable.
* **Accessible colors**; add **Dark mode** toggle by adding `data-bs-theme="dark"` on `<html>` when requested.
* Grid rendered as a table or CSS grid; each cell has data attributes: `data-r`, `data-c`, `data-ch`.
* Selection UX: mousedown/touchstart → drag → mouseup to emit a line; highlight tentative path.

---

## 9) Composer Setup

**composer.json (baseline):**

```json
{
  "name": "wem/wordsearch",
  "type": "project",
  "require": {
    "php": ">=8.2",
    "vlucas/phpdotenv": "^5.6"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  },
  "scripts": {
    "post-install-cmd": [
      "@php -r \"if(!is_dir('storage')) mkdir('storage', 0775, true);\""
    ],
    "post-update-cmd": []
  }
}
```

* Run `composer install`; commit `composer.json`/`lock`, not `vendor/` (unless policy differs).

---

## 10) Nginx + PHP-FPM (sketch)

* **Web root:** `/path/to/wordsearch/public`.
* **Routing:** All non-file requests → `public/index.php`.
* **Client body size:** keep default unless uploads introduced.

Example server block (summary):

```
server {
  server_name example.test;
  root /path/to/wordsearch/public;
  index index.php;

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
  }

  access_log /var/log/nginx/wordsearch.access.log;
  error_log  /var/log/nginx/wordsearch.error.log warn;
}
```

---

## 11) Security & Quality

* Escape output; enable **CSP** later; CSRF token for POST endpoints.
* Validate inputs server-side: word lists (A–Z only unless extended), max sizes/lengths.
* Logging to `storage/logs/app.log` (errors + generation diagnostics at debug level).
* Unit-test `PuzzleGenerator` edge cases (overlaps, impossible placements → retry/resize).

---

## 12) UI Details to Remember

* Use **cards** for panels (word list, controls, status).
* Show **timer**, **found/total** counter, and **hint** button.
* Provide **Print** button (print CSS) in later iteration.
* **No Bootstrap `nav`** unless explicitly asked; use a simple header with brand + two buttons.

---

## 13) Default Configs (in `config/game.php`)

```php
return [
  'sizes' => [
    'easy' => 10,
    'medium' => 12,
    'hard' => 15,
  ],
  'alphabet' => range('A', 'Z'),
  'allowDiagonals' => [ 'easy' => false, 'medium' => true, 'hard' => true ],
  'allowReverse'   => [ 'easy' => false, 'medium' => true, 'hard' => true ],
  'maxWordLen' => 18,
];
```

---

## 14) Minimal Bootstrap Layout (guideline)

* **layout.php** includes Bootstrap CSS/JS (CDN OK), jQuery, a sticky header, and container.
* Keep custom CSS in `public/assets/css/app.css`; keep custom JS in `public/assets/js/app.js`.

---

## 15) Prompts for Cursor (copy/paste when asking for code)

1. **Router + Controller skeleton:**

   * “Create `app/Http/Router.php` and a minimal router to map `/`, `/play`, `/create`, `/api/*` to controller callables, defaulting to `public/views/*.php` for pages.”
2. **Puzzle generator class:**

   * “Implement `App\Services\PuzzleGenerator` with seeded backtracking placement (H,V,Diag, reverse opts), returning grid array + placed word coords.”
3. **Frontend grid:**

   * “In `public/assets/js/app.js`, render the grid as a table from JSON; implement drag-select (mouse + touch) and highlight valid word lines.”
4. **API endpoints:**

   * “Build `public/index.php` to dispatch `/api/generate` (POST) and `/api/puzzle/{id}` (GET) with JSON responses and error handling.”
5. **Styling:**

   * “Create Bootstrap UI with a header bar (no nav), two-column layout on md+, stacked on sm.”

---

## 16) Backlog / Next Steps

* [ ] Save/load recent games in LocalStorage.
* [ ] Shareable short URLs (e.g., `/p/{id}` alias to `/play?id=...`).
* [ ] Print-friendly stylesheet.
* [ ] Export to PDF (later; via wkhtmltopdf or a PHP lib).
* [ ] Theme packs loaded from `/resources/themes/*.json`.
* [ ] Server-side validation of found words for leaderboards.

---

## 17) Non-Goals (for now)

* No user accounts/auth, no payments, no server-side sessions beyond CSRF token.
* No heavy JS frameworks; keep it jQuery + vanilla.

---

## 18) Definition of Done (MVP)

* Generates puzzles deterministically by seed; saves JSON to `storage/puzzles`.
* Play page works on desktop + mobile; all words discoverable; win state.
* Clean Bootstrap UI, accessible font sizes, works offline-ish (basic caching optional later).
* Nginx serves from `public/`; single entry `index.php` handles routes & APIs.

---

**Remember:** Default to PHP 8.2, Nginx, jQuery, Bootstrap 5, Composer PSR-4. Avoid Bootstrap `nav` unless specifically requested. Keep code modular and testable. Use storage JSON until a DB is requested.
