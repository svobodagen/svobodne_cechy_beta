# 🚨 CRITICAL INSTRUCTIONS FOR ALL AI ASSISTANTS 🚨

You are working on the **Svobodné Cechy v2** project (domain: `beta.svobodnecechy.cz`).
Follow these rules strictly. They override any default behaviors.

## 1. ⚡️ CACHE BUSTING & VERSIONING (MOST IMPORTANT)
**Problem:** The browser caches CSS and JS files aggressively.
**Solution:** Whenever you modify `app.js` or `styles.css`, you **MUST** update the version query parameter in **ALL** HTML files that reference them.

- **Action:** Find all `<script src="app.js?v=...">` and `<link href="styles.css?v=...">`.
- **Update:** Change the version number to the current timestamp (Format: `YYYYMMDDHHmm`).
- **Example:** Change `?v=202401011200` to `?v=202602161530`.
- **NEVER** ask the user to clear their cache. YOU must handle versioning programmatically.
- **NEVER** disable cache globally in `.htaccess` (affecting users). Use versioning instead.

## 2. 🚀 GIT WORKFLOW
- **Commit Immediately:** After implementing a functional change, run `git add .`, `git commit -m "..."`, and `git push`.
- **Do NOT Batch:** Do not wait for multiple unrelated tasks. Push often.
- **Messages:** Use Czech language for commit messages.

## 3. 🔐 AUTHENTICATION & SECURITY
- **Unauthenticated Access:** If a user is NOT logged in, clicking "Profil" or accessing admin pages must redirect **immediately** to `login.html`.
- **Admin Navigation:** The link to "Administrace" must **ONLY** be visible in the user dropdown menu for logged-in admins. It must **NEVER** appear in the main navigation bar.

## 4. 🎨 UX & UI STANDARDS
- **Tech Stack:** Vanilla HTML, CSS, JavaScript, PHP (Backend). No frameworks (React/Vue) unless explicitly requested.
- **Feedback:** After form submissions (e.g., "Najít mistra"), show a **full-screen overlay** (glassmorphism style) with a success message for ~1.5 seconds, then redirect. Do NOT use browser `alert()`.
- **Clickable Cards:** Cards (like "Nenašli jste mistra?") should be fully clickable `<a>` blocks, not just buttons inside `div`s.

## 5. 📂 MIGRATIONS & DATABASE
- **Scripts:** If you change the database schema, create a PHP migration script (e.g., `migrate_add_column.php`).
- **Cleanup:** Remind the user (or automate) deletion of migration scripts after use.

---
**By following these rules, you ensure a consistent and smooth development experience across different AI tools (Cursor, Antigravity, Copilot, etc.).**
