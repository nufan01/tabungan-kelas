## Repo snapshot

- Framework: Laravel (app skeleton) — composer.json requires "laravel/framework": "^12.0" and PHP ^8.2.
- Frontend: Inertia + Vue 3 + Vite + Tailwind (see `resources/js/`, `vite.config.js`, `tailwind.config.js`).
- DB: migrations in `database/migrations`; tests run with in-memory SQLite (see `phpunit.xml`).

## Quick dev / test commands

- Local full setup (runs migrations & builds assets):
  - `composer run setup` (defined in `composer.json` — installs PHP deps, copies .env, generates key, migrates, npm install, npm run build)
- Start development (PHP server, queue listener, logs, vite):
  - `composer run dev` (calls a concurrently command which runs `php artisan serve`, `php artisan queue:listen`, `php artisan pail`, and `npm run dev`)
- Frontend only:
  - `npm run dev` or `npm run build` (see `package.json`)
- Run tests:
  - `composer test` (maps to `php artisan test`) — phpunit config uses sqlite in-memory so CI/local tests don't need a DB server.

## Architecture & important files (what to read first)

- `routes/web.php` — central place for authentication-protected routes, resources (students), and the welcome page. Example: `Route::resource('students', StudentController::class)`.
- `app/Http/Controllers/` — controllers that implement the web/API surface. Key controllers: `StudentController`, `TransactionController`, `WelcomeController`, `DashboardController`.
- `app/Models/` — Eloquent models; primary domain objects: `Student` (hasMany `transactions`), `Transaction` (belongsTo `student`), `User` (auth). Look at `Student.php` and `Transaction.php` for fields and relationships.
- `resources/js/Pages/`, `resources/js/Components/` — Inertia + Vue pages and components that render the UI and call back-end endpoints via Axios/Inertia.
- `database/migrations/` — DB shape; search for `create_students_table` and `create_transactions_table` migrations to understand fields like `saldo`, `amount`, and `type` (deposit/withdrawal).

## Project-specific patterns & conventions

- Transaction semantics: code and route comments reference transaction `type` values `deposit` and `withdrawal` and compute `totalKas` using sum of those types (see `routes/web.php` example code). When adding transaction logic, keep `type` string values consistent.
- Model casting: `saldo` on `Student` is cast to `decimal:2` — keep money fields as decimals and update casts when adding monetary fields.
- Controllers use standard resource controllers for CRUD (see `StudentController`). Prefer controller actions over putting logic directly in routes.
- Tests: `phpunit.xml` sets `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` — write tests expecting no external DB required. Use model factories in `database/factories/`.

## Integration points & external deps

- Inertia (server-side adapters in controllers + client-side Vue pages) — coordinate response payload shapes in controllers with props expected by `resources/js/Pages`.
- Ziggy (`tightenco/ziggy`) is present for route generation in front-end.
- Laravel Sanctum present for auth (`laravel/sanctum`) — check `routes/auth.php` and middleware for protected APIs.
- Background processing: project uses Laravel queues (see composer dev script that runs `php artisan queue:listen`) — long-running jobs may be present.

## Small examples to help AI agents modify code

- Add a new Student attribute: update `app/Models/Student.php` `$fillable` and `$casts`, then add migration in `database/migrations/`, update any Inertia page props and controller methods that create/update students (`StudentController@store`/`@update`).
- Add a transaction type check: in `TransactionController@store`, validate `type` is `deposit|withdrawal` and update `Student->saldo` accordingly (use DB transactions to keep consistency).

## Tests & CI hints

- Unit/Feature tests live in `tests/Unit` and `tests/Feature`. Use Laravel's `RefreshDatabase` trait as DB uses sqlite in tests.
- To run a single test class locally: `composer test -- --filter ClassName` (composer passes args to `php artisan test`).

## Where to look for more context when uncertain

- `composer.json` — dev scripts and PHP/package constraints.
- `phpunit.xml` — test environment (in-memory sqlite). Important for CI adjustments.
- `routes/web.php` and `app/Http/Controllers/` — canonical app flow entry points.
- `resources/js/Pages/` and `resources/js/Components/` — front-end expectations for props and actions.

If any section is unclear or you'd like me to add short code snippets for common tasks (e.g., safe transaction store logic, or a test skeleton), tell me which area to expand and I'll iterate.
