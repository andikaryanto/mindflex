# Contributing

## Requirements

- PHP 7.4 or newer
- Composer
- SQLite PDO extension enabled

## Local Setup

1. Generate Composer autoload files:

   ```bash
   composer dump-autoload
   ```

2. Ensure the SQLite database exists at the project root:

   ```text
   mindflex.db
   ```

3. Start a local PHP server:

   ```bash
   php -S localhost:8000
   ```

4. Open the dashboard:

   ```text
   http://localhost:8000/index.php
   ```

## Development Notes

- Put database queries in `src/Services`, not in views or entry files.
- Keep `index.php` focused on request handling and view data preparation.
- Keep `api_legacy.php` returning JSON for all responses.
- Use prepared statements through `DatabaseConnection`.
- Add CSRF tokens to dashboard forms that mutate data.
- Run PHP lint before finishing changes:

  ```bash
  php -l index.php
  php -l api_legacy.php
  ```

## Project Structure

```text
index.php                 Dashboard entry point
api_legacy.php            JSON API entry point
src/Database/             PDO connection helper
src/Services/             Database-backed business operations
src/Views/                PHP view templates
mindflex.db               SQLite database
```
