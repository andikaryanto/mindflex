# Mindflex Matchmaking Refactor Notes

This project is a small PHP 7.4 tutoring/admin dashboard backed by SQLite.

## Refactoring Strategy

The refactor focused on separating responsibilities without introducing a full framework.

- `index.php` remains the web entry point, but request handling is split into small functions.
- SQL access is moved into service classes under `src/Services`.
- `DatabaseConnection` provides a small shared PDO wrapper with prepared statement support.
- Views stay in `src/Views`, with data prepared before rendering.
- API responses in `api_legacy.php` now go through service methods and a shared JSON response helper.

This pattern was chosen because the project is small. A controller/service split gives most of the maintainability and security benefit without adding framework overhead.

## Primary Flaws Found and Resolved

- Raw SQL and direct PDO usage were still present in `api_legacy.php`.
- Many queries initially interpolated request values directly without prepared statements, making them easier to exploit through SQL injection.
- Assignment display performed N+1 queries inside `main.php`.
- Mutating dashboard actions used GET links for `delete` and `complete`.
- POST handlers in `index.php` directly read globals and mixed request routing with business actions.
- CSRF protection was missing from dashboard forms.
- Database helper `exec()` passed params incorrectly to `prepare()` instead of `execute()`.

Resolved changes include service-backed queries, prepared statement execution, joined assignment loading, POST-only mutation actions, CSRF validation for dashboard forms, cleaner handler functions, and associative fetch results. Using prepared statements keeps request values bound as parameters instead of being concatenated into SQL strings, making the query layer more secure against SQL injection.

## Composer Dependencies

No new Composer dependencies were added.

The project currently only uses Composer autoloading through `vendor/autoload.php`.

## AI Usage Note

AI assistance was used to inspect the codebase, identify security and maintainability issues, and apply incremental refactors. Each change was kept small, checked with PHP linting, and aligned with the existing plain PHP structure instead of introducing a new framework.
