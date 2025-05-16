# High-Level Architecture

Project Velvue is a full-stack SaaS boilerplate application designed with a decoupled frontend and backend architecture. The frontend is a Vue.js 3 Single Page Application (SPA) located in the `/fe_velvue` directory, and the backend is a Laravel 11 API located in the `/be_velvue` directory. The project aims to provide a starter template for SaaS applications, including features like authentication, user account management, dashboard, and more.

### Overall Structure:

- **Monorepo-style organization**:
  - `/fe_velvue/`: Contains the Vue.js 3 frontend SPA.
  - `/be_velvue/`: Contains the Laravel 11 backend API.
- **Communication**: The frontend SPA communicates with the backend API via HTTP requests (primarily JSON). The API base URL for the frontend is configured via the `VITE_API_URL` environment variable (e.g., `http://localhost:8000`).

### Frontend Architecture (`/fe_velvue`)

- **Framework**: Vue.js 3 (`vue: ^3.5.13`) using the Composition API (`<script setup>`).
- **Build Tool**: Vite (`vite: ^6.0.7`) for fast development and optimized builds.
- **UI Library**:
  - PrimeVue (`primevue: ^4.2.5`) is the primary UI component library.
  - Theme: `@primevue/themes/aura` is used as the base PrimeVue theme.
  - PrimeIcons (`primeicons: ^7.0.0`) for iconography.
- **Styling**:
  - Tailwind CSS (`tailwindcss: ^3.4.17`) for utility-first CSS styling.
    - Integrated with PrimeVue using `tailwindcss-primeui: ^0.3.4`.
    - Configuration: `/fe_velvue/tailwind.config.js` (includes custom screens, dark mode selector `[class*="app-dark"]`).
    - PostCSS with Autoprefixer: `/fe_velvue/postcss.config.js`.
  - SCSS (`sass: ^1.83.1`) for global styles and layout structure.
    - Main SCSS entry point: `/fe_velvue/src/assets/styles.scss`.
    - Layout styles: `/fe_velvue/src/assets/layout/` (variables, mixins, core styles).
    - CSS variables derived from PrimeVue presets.
- **State Management**: Pinia (`pinia: ^2.3.0`) for global state management.
  - Example store: `/fe_velvue/src/stores/auth.js` for authentication state (user, loading, isLoggedIn).
- **Routing**: Vue Router (`vue-router: ^4.5.0`) for client-side navigation.
  - Configuration: `/fe_velvue/src/router/index.js`.
  - Routes are defined with lazy loading for page components.
  - Navigation guards (`router.beforeEach`) handle authentication, guest-only routes, and email verification checks.
  - Layouts: `AppLayout.vue` serves as the main authenticated layout.
- **HTTP Client**: Axios (`axios: ^1.7.9`) for making API requests.
  - Service wrapper: `/fe_velvue/src/service/apiService.js` configures an Axios instance.
    - Handles base URL, `withCredentials: true`, `withXSRFToken: true`.
    - Interceptors:
      - Request interceptor for automatically fetching CSRF token if not present.
      - Response interceptor for handling common HTTP errors (419 CSRF expiry, 500 server error, 401 unauthorized).
- **Component Auto-Import**:
  - `unplugin-vue-components/vite` (`unplugin-vue-components: ^0.28.0`) and `@primevue/auto-import-resolver` are used for automatic component registration, simplifying component usage within templates.
- **Entry Point**: `/fe_velvue/src/main.js` initializes Vue, Pinia, PrimeVue (with Aura preset), Vue Router, and mounts the root `App.vue` component.
- **Root HTML**: `/fe_velvue/index.html` is the main HTML file, loading `Lato` font from CDN.
- **Key Directories**:
  - `src/assets/`: Global styles, fonts, images.
  - `src/components/`: Reusable Vue components (e.g., auth elements, dashboard widgets, form elements).
  - `src/layout/`: Components related to the application layout (sidebar, header, footer, menu).
    - `AppLayout.vue`: Main layout for authenticated views.
    - `composables/layout.js`: Composable for managing layout state (theme, menu mode, active title).
  - `src/router/`: Vue Router configuration.
  - `src/service/`: API service configurations and other data services (e.g., `CountryService.js`, `ProductService.js`).
  - `src/stores/`: Pinia state management stores.
  - `src/views/`: Page-level Vue components, organized by features (e.g., `DashboardPage.vue`, `pages/AccountPage.vue`, `pages/auth/LoginPage.vue`).
- **Dark Mode**: Supported via Tailwind CSS dark mode selector `[class*="app-dark"]` (configured in `tailwind.config.js`) and SCSS variables.
- **Helper Functions**: Utility functions like email validation in `/fe_velvue/src/helpers.js`.

### Backend Architecture (`/be_velvue`)

- **Framework**: Laravel 11 (`laravel/framework: ^11.31`).
- **PHP Version**: `^8.2`.
- **API Type**: Primarily a JSON API serving the frontend SPA.
- **Authentication**:
  - Laravel Sanctum (`laravel/sanctum: ^4.0`) for API authentication using stateful session cookies.
    - Configuration: `config/sanctum.php`. Stateful domains include `localhost`, `localhost:5173`.
  - Social Login: Laravel Socialite (`laravel/socialite: ^5.16`) for OAuth authentication (e.g., Google).
    - Controller: `app/Http/Controllers/AuthController.php` handles redirection and callbacks.
    - View for OAuth redirection: `resources/views/oauth.blade.php`.
- **Database**:
  - Default: MySQL (as per `.env` `DB_CONNECTION=mysql`, `DB_DATABASE=velvue`).
  - Testing: SQLite in-memory (configured in `phpunit.xml`).
  - Migrations: Standard Laravel migrations located in `database/migrations/`. Includes tables for users, password resets, sessions, cache, jobs, Telescope, Cashier (subscriptions, items), user login logs, user providers, temporary uploads.
  - Seeding: `database/seeders/DatabaseSeeder.php` creates a default test user.
- **Routing**:
  - API routes defined in `/be_velvue/routes/api.php`.
  - Global API prefix: `/api` (configured in `bootstrap/app.php` as `apiPrefix: ''` but routes in `api.php` are prefixed with `api`). This means effective routes start with `/api/api/...`. _Correction: The `apiPrefix: ''` with `Route::prefix('api')` in `api.php` means routes start with `/api/...`._
  - CSRF protection is handled by Sanctum and Laravel's middleware. The frontend fetches a CSRF cookie from `/sanctum/csrf-cookie`.
- **Controllers**: Located in `app/Http/Controllers/`.
  - `AuthController.php`: Handles registration, login (email/password & social), logout, fetching user details, password reset, email verification.
  - `AccountController.php`: Manages user profile updates (name, email, avatar) and password changes.
  - `UploadController.php`: Handles image uploads, currently supporting 'avatars'.
- **Models**: Eloquent ORM models in `app/Models/`.
  - `User.php`: Implements `MustVerifyEmail`. Relationships with `UserProvider` and `UserLoginLog`.
  - `UserProvider.php`: Stores social login provider information for users.
  - `UserLoginLog.php`: Logs user login activity.
  - `TemporaryUpload.php`: Tracks temporary file uploads (e.g., avatars before being permanently associated).
- **Middleware**:
  - `App\Http\Middleware\JsonResponse.php`: Ensures API responses are always JSON.
  - Standard Laravel middleware for auth, CSRF, etc., configured in `bootstrap/app.php`.
- **Request Handling & Validation**:
  - Validation is typically handled within controllers using Laravel's validation features.
  - Custom validation rules: `App\Rules\TemporaryFileExists.php`.
- **Image Handling**:
  - Library: Intervention Image (`intervention/image: ^3.10`).
  - Helper: `App\Helpers\Image.php` provides a `convert` method to resize and convert images (default to WebP).
  - Uploaded files are processed using a macro on `UploadedFile` defined in `AppServiceProvider.php`.
  - Temporary uploads are stored and cleaned up by `App\Console\Commands\TemporaryClear.php` (scheduled or manual execution).
  - Storage: Public disk (`public/storage`), symlinked via `php artisan storage:link`.
- **Payments**: Laravel Cashier (`laravel/cashier: ^15.6`) for Stripe integration.
  - Migrations for `subscriptions` and `subscription_items` tables.
  - User model has Stripe customer columns (added via migration `2025_01_05_152619_create_customer_columns.php`).
- **Task Scheduling/Queues**:
  - Laravel Queues are set up (migrations for `jobs`, `job_batches`, `failed_jobs`). Default `QUEUE_CONNECTION=database`.
  - Console commands: `app/Console/Commands/` (e.g., `TemporaryClear.php`).
- **Logging & Debugging**:
  - Laravel Telescope (`laravel/telescope: ^5.2`) for debugging and monitoring in local environments.
  - Standard Laravel logging configured in `config/logging.php`.
- **Error Handling**: Custom JSON responses for `NotFoundHttpException`, `AuthenticationException`, and `ValidationException` are configured in `bootstrap/app.php`.
- **Rate Limiting**: Configured in `AppServiceProvider.php` for API, login, verification notification, and uploads.
- **CORS**: Configured in `config/cors.php` to allow requests from `FRONTEND_URL` (e.g., `http://localhost:5173`) with credentials.

### Interaction & Data Flow Examples:

1.  **User Registration**:

    - FE: User submits registration form (`RegisterPage.vue`).
    - FE: `apiService.js` sends POST request to `/api/register`.
    - BE: `AuthController@register` validates input, creates `User`, fires `Registered` event (sends verification email).
    - BE: Returns JSON success response.
    - FE: Shows success message, redirects to login or prompts for verification.

2.  **User Login**:

    - FE: User submits login form (`LoginPage.vue`).
    - FE: `authStore.login` action calls `apiService.js` to POST to `/api/login`.
    - BE: `AuthController@login` attempts authentication. If successful, regenerates session, logs login attempt in `user_login_logs`.
    - BE: Returns JSON success response, and `Set-Cookie` header for session.
    - FE: `authStore` fetches user data (GET `/api/user`), updates state, router navigates to dashboard.

3.  **Authenticated API Request (e.g., Update Account)**:

    - FE: User updates profile on `AccountPage.vue`.
    - FE: `apiService.js` sends POST request to `/api/account/update` (session cookie is automatically sent by browser).
    - BE: Laravel Sanctum middleware authenticates user via session.
    - BE: `AccountController@update` validates, updates user details. If email changed, marks for re-verification.
    - BE: Returns JSON success response.
    - FE: Updates local user state in `authStore`.

4.  **Image Upload (Avatar)**:
    - FE: User selects image in `AccountPage.vue`'s `FileUpload` component.
    - FE: `uploadProfileImage` function POSTs image data (FormData) to `/api/upload` with `entity: 'avatars'`.
    - BE: `UploadController@image` validates, uses `UploadedFile::convert` macro (which uses `App\Helpers\Image.php`) to process and store image in `public/storage/avatars/` as WebP. Creates `TemporaryUpload` record.
    - BE: Returns JSON response with `{ success: true, path: 'avatars/someid.webp' }`.
    - FE: Updates `profileImage` ref with the new path (prepended with `VITE_API_URL + '/storage/'`).
    - FE: When profile is saved, the new avatar path is sent to `/api/account/update`.
    - BE: `AccountController@update` saves the path, deletes old avatar if any, and deletes the `TemporaryUpload` record.

### Key Design Principles:

- **Separation of Concerns**: Decoupled frontend and backend allow for independent development and scaling.
- **API-Driven**: Frontend relies entirely on the backend API for data and business logic.
- **Stateful SPA Authentication**: Leverages Laravel Sanctum's session-based authentication for SPAs, which is generally considered more secure for this use case than token-based auth if on same-site or properly configured trusted domains.
- **Modern Tooling**: Utilizes Vite, Vue 3 Composition API, Pinia, Tailwind CSS for a modern development experience.
- **Component-Based UI**: PrimeVue and custom components for a modular frontend.
- **Developer Experience**: Features like Telescope, Pint, PHPStan aim to improve backend development quality and productivity.
- **SaaS Focus**: Includes foundational SaaS features like user management, billing (via Cashier), and planned activity logging.

# Project-Specific Standards & Conventions

This project adheres to a set of standards and conventions across its frontend (`fe_velvue`) and backend (`be_velvue`) components to ensure consistency, maintainability, and code quality.

### General Conventions:

1.  **License**: MIT License (File: `/LICENSE`). Copyright (c) 2024 DreamUpstream.
2.  **Version Control**: Git.
    - `.gitignore` files are present in both frontend and backend root directories, as well as specific subdirectories (e.g., `be_velvue/database/`, `be_velvue/storage/app/public/`).
    - `.gitattributes` is present in the backend.
3.  **Directory Structure**: Organized into `fe_velvue` (frontend) and `be_velvue` (backend) top-level directories. A detailed 5-level deep directory structure is documented in `/llm_context.md`.
4.  **README**: `/Readme.md` provides project overview, features, installation, setup instructions, and contribution guidelines.
5.  **Environment Configuration**:
    - Both frontend and backend use `.env` files for environment-specific configurations, with `.env.example` files serving as templates.
    - Frontend: `/fe_velvue/.env` (e.g., `VITE_API_URL`).
    - Backend: `/be_velvue/.env` (e.g., `APP_URL`, `DB_CONNECTION`, `SANCTUM_STATEFUL_DOMAINS`, `FRONTEND_URL`).

### Frontend (`/fe_velvue`):

1.  **Language**: JavaScript (ES Modules, latest ECMAScript features).
2.  **Framework**: Vue.js 3.
    - **Component Style**: Single File Components (`.vue`) with `<script setup>` syntax preferred.
    - **Component Naming**: PascalCase for component file names and when used in templates (e.g., `DashboardPage.vue`, `<AuthContainer />`).
3.  **Build & Development**:
    - **Tool**: Vite.
    - **Scripts** (defined in `package.json`):
      - `dev`: Starts Vite development server.
      - `build`: Builds the application for production.
      - `preview`: Previews the production build locally.
4.  **Code Quality & Formatting**:
    - **Linting**: ESLint (`eslint: ^9.17.0`).
      - Configuration: `/fe_velvue/eslint.config.mjs` (flat config format).
      - Uses `@eslint/js` recommended rules, `eslint-plugin-vue` recommended for Vue 3.
      - Integrates Prettier via `@vue/eslint-config-prettier` (must be the last item in config array).
      - Specific ignores: `node_modules/`, `dist/`, `public/`, `.gitignore`.
      - Language options: `sourceType: "module"`, `ecmaVersion: "latest"`.
      - Defines globals for browser and Node.js environments.
      - Scripts: `lint:check` (check), `lint:fix` (auto-fix).
    - **Formatting**: Prettier (`prettier: ^3.4.2`).
      - Configuration: `/fe_velvue/.prettierrc.json`.
      - Key settings: `semi: true`, `singleQuote: false`, `tabWidth: 2`, `trailingComma: "es5"`, `printWidth: 80`, `vueIndentScriptAndStyle: false`.
      - Scripts: `format:check` (check), `format:write` (auto-format).
5.  **Styling**:
    - **Primary Method**: Tailwind CSS.
      - Configuration: `/fe_velvue/tailwind.config.js`.
      - Content scanning: `./index.html`, `./src/**/*.{vue,js,ts,jsx,tsx}`.
      - Dark mode: `selector`, `[class*="app-dark"]`.
      - Plugins: `tailwindcss-primeui`.
      - Custom screen sizes defined.
    - **Global Styles & Structure**: SCSS.
      - Main entry: `/fe_velvue/src/assets/styles.scss`.
      - Imports PrimeIcons CSS and layout-specific SCSS files from `/fe_velvue/src/assets/layout/`.
      - The `layout.scss` imports variables, mixins, and structural styles for core layout, menu, footer, etc.
      - CSS variables (e.g., `--primary-color`, `--surface-card`) are defined in `/fe_velvue/src/assets/layout/variables/_common.scss`, often deriving from PrimeVue theme variables.
    - **PostCSS**: `/fe_velvue/postcss.config.js` uses `tailwindcss` and `autoprefixer`.
    - **Utility Classes**: Project-specific utility/component classes like `.card` (in `_utils.scss`) and `.card-container` (in `tailwind.css`) are defined.
6.  **Path Aliases**:
    - Configured in `/fe_velvue/jsconfig.json`.
    - `@/*` maps to `./src/*`.
7.  **State Management (Pinia)**:
    - Stores are located in `/fe_velvue/src/stores/`.
    - Example: `auth.js` defines state (`user`, `loading`), getters (`isLoggedIn`), and actions (`WorkspaceUser`, `login`, `logout`).
8.  **Routing (Vue Router)**:
    - Configuration: `/fe_velvue/src/router/index.js`.
    - Lazy loading of route components: `component: () => import('@/views/...')`.
    - Route meta fields used for auth logic: `requiresAuth`, `isGuest`, `requiresVerified`, `title`.
    - Navigation guards (`beforeEach`) for authentication and authorization logic.
9.  **API Communication**:
    - Axios instance in `/fe_velvue/src/service/apiService.js`.
    - Base URL from `VITE_API_URL` (from `.env`).
    - Automatic CSRF token handling for POST/PUT/PATCH/DELETE requests.
    - Response interceptors for common errors (401, 419, 500).
10. **Helper Functions**:
    - General utility functions are placed in `/fe_velvue/src/helpers.js` (e.g., `validateEmail`).
11. **Auto-Import**:
    - `unplugin-vue-components` and `@primevue/auto-import-resolver` are used to auto-import PrimeVue components and potentially custom components, reducing manual import statements.

### Backend (`/be_velvue`):

1.  **Language**: PHP `^8.2`.
2.  **Framework**: Laravel 11.
3.  **Code Style & Formatting**:
    - **Tool**: Laravel Pint.
      - Configuration: `/be_velvue/pint.json`.
      - Preset: `"laravel"`.
      - Custom rules: `concat_space: { spacing: "one" }`, `unary_operator_spaces: false`, `not_operator_with_successor_space: false`.
    - **Editor Configuration**: `/be_velvue/.editorconfig`.
      - `charset = utf-8`, `end_of_line = lf`, `indent_size = 4`, `indent_style = space`, `insert_final_newline = true`, `trim_trailing_whitespace = true`.
4.  **Static Analysis**:
    - **Tool**: PHPStan with Larastan.
      - Configuration: `/be_velvue/phpstan.neon`.
      - Level: `5`.
      - Paths analyzed: `app/`, `routes/`.
      - `inferPrivatePropertyTypeFromConstructor: true`.
5.  **Testing**:
    - **Framework**: PHPUnit.
      - Configuration: `/be_velvue/phpunit.xml`.
      - Test suites for "Unit" (`tests/Unit`) and "Feature" (`tests/Feature`).
      - Source code coverage includes the `app/` directory.
      - Uses SQLite in-memory database for tests (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).
      - Specific environment variables set for testing (e.g., `APP_ENV=testing`, `CACHE_STORE=array`, `SESSION_DRIVER=array`, `TELESCOPE_ENABLED=false`).
6.  **Database**:
    - **Migrations**: Standard Laravel migration naming convention (e.g., `YYYY_MM_DD_HHMMSS_create_table_name.php`). Located in `database/migrations/`.
    - **Factories**: Located in `database/factories/` (e.g., `UserFactory.php`).
    - **Seeders**: Located in `database/seeders/` (e.g., `DatabaseSeeder.php`).
7.  **Routing**:
    - API routes are defined in `/be_velvue/routes/api.php`.
    - Routes are typically named (e.g., `->name('login')`).
    - Route groups are used for prefixing (`Route::prefix('api')`) and middleware application (`Route::middleware(['auth:sanctum'])`).
8.  **Controllers**:
    - Located in `app/Http/Controllers/`.
    - Return `Illuminate\Http\JsonResponse` for API responses.
    - Validation typically done within controller methods using `$request->validate()`.
9.  **Models (Eloquent)**:
    - Located in `app/Models/`.
    - Mass assignment protection using `$fillable` property.
    - Attribute hiding for serialization using `$hidden` property.
    - Attribute casting using `casts()` method.
    - Relationships defined as methods (e.g., `userProviders(): HasMany`).
10. **API Authentication & Authorization**:
    - Laravel Sanctum for SPA authentication using session cookies.
    - `auth:sanctum` middleware protects routes.
11. **CSRF Protection**:
    - Handled by Laravel's built-in CSRF protection, integrated with Sanctum for SPA stateful authentication. Frontend obtains token from `/sanctum/csrf-cookie`.
12. **Error Handling**:
    - Custom JSON responses for `NotFoundHttpException`, `AuthenticationException`, `ValidationException` configured in `/be_velvue/bootstrap/app.php`. Errors include a `success: false` flag and structured error messages.
13. **Service Providers**:
    - Custom application logic bootstrapped in `/be_velvue/app/Providers/AppServiceProvider.php`.
    - Includes custom macros (e.g., `UploadedFile::macro('convert')`, `Str::macro('onlyWords')`) and rate limiter configurations.
14. **Image Handling**:
    - Images are processed to WebP by default using `Intervention/Image` via the `App\Helpers\Image.php` helper and `UploadedFile::convert` macro.
    - Uploaded files are stored in `public/storage` (symlinked).
    - Temporary uploads are managed via the `TemporaryUpload` model and a cleanup command (`temporary:clear`).
15. **Dependency Management**: Composer for PHP packages.
    - `composer.json` defines dependencies and PSR-4 autoloading for `App\` and `Database\`.
16. **Naming Conventions (PHP/Laravel - General)**:
    - Classes: PascalCase (e.g., `AccountController`, `User`).
    - Methods: camelCase (e.g., `WorkspaceUser`, `saveProfile`).
    - Variables: snake_case for database columns and often for array keys in responses, camelCase for local variables in PHP.
    - Blade templates: kebab-case or snake_case (e.g., `oauth.blade.php`).
    - Route names: kebab-case or snake_case (e.g., `password.email`).

# Directory Structure (5-level deep)

Velvue-Laravel-Vue-SaaS-Starter/
├─ be_velvue/
│ ├─ app/
│ │ ├─ Console/
│ │ │ └─ Commands/
│ │ │ └─ TemporaryClear.php
│ │ ├─ Helpers/
│ │ │ └─ Image.php
│ │ ├─ Http/
│ │ │ ├─ Controllers/
│ │ │ │ ├─ AccountController.php
│ │ │ │ ├─ AuthController.php
│ │ │ │ ├─ Controller.php
│ │ │ │ └─ UploadController.php
│ │ │ └─ Middleware/
│ │ │ └─ JsonResponse.php
│ │ ├─ Models/
│ │ │ ├─ TemporaryUpload.php
│ │ │ ├─ User.php
│ │ │ ├─ UserLoginLog.php
│ │ │ └─ UserProvider.php
│ │ ├─ Providers/
│ │ │ ├─ AppServiceProvider.php
│ │ │ └─ TelescopeServiceProvider.php
│ │ └─ Rules/
│ │ └─ TemporaryFileExists.php
│ ├─ bootstrap/
│ │ ├─ cache/
│ │ │ ├─ .gitignore
│ │ │ ├─ packages.php
│ │ │ └─ services.php
│ │ ├─ app.php
│ │ └─ providers.php
│ ├─ config/
│ │ ├─ app.php
│ │ ├─ auth.php
│ │ ├─ cache.php
│ │ ├─ cors.php
│ │ ├─ database.php
│ │ ├─ filesystems.php
│ │ ├─ logging.php
│ │ ├─ mail.php
│ │ ├─ queue.php
│ │ ├─ sanctum.php
│ │ ├─ services.php
│ │ ├─ session.php
│ │ └─ telescope.php
│ ├─ database/
│ │ ├─ factories/
│ │ │ └─ UserFactory.php
│ │ ├─ migrations/
│ │ │ ├─ 0001_01_01_000000_create_users_table.php
│ │ │ ├─ 0001_01_01_000001_create_cache_table.php
│ │ │ ├─ 0001_01_01_000002_create_jobs_table.php
│ │ │ ├─ 2025_01_05_152549_create_telescope_entries_table.php
│ │ │ ├─ 2025_01_05_152619_create_customer_columns.php
│ │ │ ├─ 2025_01_05_152620_create_subscriptions_table.php
│ │ │ ├─ 2025_01_05_152621_create_subscription_items_table.php
│ │ │ ├─ 2025_01_15_181343_create_user_login_logs_table.php
│ │ │ ├─ 2025_01_15_193830_create_user_providers_table.php
│ │ │ └─ 2025_01_21_172120_create_temporary_uploads_table.php
│ │ ├─ seeders/
│ │ │ └─ DatabaseSeeder.php
│ │ └─ .gitignore
│ ├─ public/
│ │ ├─ .htaccess
│ │ ├─ favicon.ico
│ │ ├─ index.php
│ │ ├─ robots.txt
│ │ └─ storage
│ ├─ resources/
│ │ └─ views/
│ │ └─ oauth.blade.php
│ ├─ routes/
│ │ ├─ api.php
│ │ └─ console.php
│ ├─ storage/
│ │ ├─ app/
│ │ │ ├─ private/
│ │ │ │ └─ .gitignore
│ │ │ ├─ public/
│ │ │ │ └─ .gitignore
│ │ │ └─ .gitignore
│ │ ├─ framework/
│ │ │ ├─ cache/
│ │ │ │ ├─ data/
│ │ │ │ │ └─ .gitignore
│ │ │ │ └─ .gitignore
│ │ │ ├─ sessions/
│ │ │ │ └─ .gitignore
│ │ │ ├─ testing/
│ │ │ │ └─ .gitignore
│ │ │ ├─ views/
│ │ │ │ └─ .gitignore
│ │ │ └─ .gitignore
│ │ └─ logs/
│ │ └─ .gitignore
│ ├─ tests/
│ │ ├─ Feature/
│ │ │ ├─ Auth/
│ │ │ │ ├─ AuthenticationTest.php
│ │ │ │ ├─ EmailVerificationTest.php
│ │ │ │ ├─ PasswordResetTest.php
│ │ │ │ └─ RegistrationTest.php
│ │ │ └─ ExampleTest.php
│ │ ├─ Unit/
│ │ │ └─ ExampleTest.php
│ │ └─ TestCase.php
│ ├─ .editorconfig
│ ├─ .env
│ ├─ .env.example
│ ├─ .gitattributes
│ ├─ .gitignore
│ ├─ artisan
│ ├─ composer.json
│ ├─ composer.lock
│ ├─ phpstan.neon
│ ├─ phpunit.xml
│ └─ pint.json
├─ fe_velvue/
│ ├─ public/
│ │ ├─ demo/
│ │ │ ├─ data/
│ │ │ │ ├─ countries.json
│ │ │ │ ├─ customers-large.json
│ │ │ │ ├─ customers-medium.json
│ │ │ │ ├─ events.json
│ │ │ │ ├─ icons.json
│ │ │ │ ├─ photos.json
│ │ │ │ ├─ products-orders-small.json
│ │ │ │ ├─ products-small.json
│ │ │ │ ├─ products.json
│ │ │ │ ├─ treenodes.json
│ │ │ │ └─ treetablenodes.json
│ │ │ └─ images/
│ │ │ ├─ access/
│ │ │ │ └─ asset-access.svg
│ │ │ ├─ error/
│ │ │ │ └─ asset-error.svg
│ │ │ ├─ flag/
│ │ │ │ └─ flag_placeholder.png
│ │ │ ├─ landing/
│ │ │ │ ├─ enterprise.svg
│ │ │ │ ├─ free.svg
│ │ │ │ ├─ mockup-desktop.svg
│ │ │ │ ├─ mockup.svg
│ │ │ │ ├─ new-badge.svg
│ │ │ │ ├─ peak-logo.svg
│ │ │ │ ├─ screen-1.png
│ │ │ │ └─ startup.svg
│ │ │ ├─ google-icon.svg
│ │ │ ├─ logo-white.png
│ │ │ └─ logo.png
│ │ └─ favicon.ico
│ ├─ src/
│ │ ├─ assets/
│ │ │ ├─ demo/
│ │ │ │ ├─ flags/
│ │ │ │ │ ├─ flags_responsive.png
│ │ │ │ │ └─ flags.css
│ │ │ │ ├─ code.scss
│ │ │ │ └─ demo.scss
│ │ │ ├─ layout/
│ │ │ │ ├─ variables/
│ │ │ │ │ ├─ \_common.scss
│ │ │ │ │ └─ \_light.scss
│ │ │ │ ├─ \_core.scss
│ │ │ │ ├─ \_footer.scss
│ │ │ │ ├─ \_main.scss
│ │ │ │ ├─ \_menu.scss
│ │ │ │ ├─ \_mixins.scss
│ │ │ │ ├─ \_preloading.scss
│ │ │ │ ├─ \_responsive.scss
│ │ │ │ ├─ \_typography.scss
│ │ │ │ ├─ \_utils.scss
│ │ │ │ └─ layout.scss
│ │ │ ├─ styles.scss
│ │ │ └─ tailwind.css
│ │ ├─ components/
│ │ │ ├─ auth/
│ │ │ │ ├─ AuthContainer.vue
│ │ │ │ ├─ AuthLogo.vue
│ │ │ │ ├─ DividerOr.vue
│ │ │ │ └─ SocialLoginButton.vue
│ │ │ ├─ dashboard/
│ │ │ │ └─ uikit/
│ │ │ │ ├─ BestSellingWidget.vue
│ │ │ │ ├─ NotificationsWidget.vue
│ │ │ │ ├─ RecentSalesWidget.vue
│ │ │ │ ├─ RevenueStreamWidget.vue
│ │ │ │ └─ StatsWidget.vue
│ │ │ └─ forms/
│ │ │ └─ ValidFormElement.vue
│ │ ├─ layout/
│ │ │ ├─ composables/
│ │ │ │ └─ layout.js
│ │ │ ├─ AppFooter.vue
│ │ │ ├─ AppLayout.vue
│ │ │ ├─ AppMenu.vue
│ │ │ ├─ AppMenuItem.vue
│ │ │ ├─ AppSidebar.vue
│ │ │ └─ UserMenu.vue
│ │ ├─ router/
│ │ │ └─ index.js
│ │ ├─ service/
│ │ │ ├─ apiService.js
│ │ │ ├─ CountryService.js
│ │ │ ├─ CustomerService.js
│ │ │ ├─ NodeService.js
│ │ │ ├─ PhotoService.js
│ │ │ └─ ProductService.js
│ │ ├─ stores/
│ │ │ └─ auth.js
│ │ ├─ views/
│ │ │ ├─ pages/
│ │ │ │ ├─ auth/
│ │ │ │ │ ├─ AccessPage.vue
│ │ │ │ │ ├─ ErrorPage.vue
│ │ │ │ │ ├─ ForgotPasswordPage.vue
│ │ │ │ │ ├─ LoginPage.vue
│ │ │ │ │ └─ RegisterPage.vue
│ │ │ │ ├─ uikit/
│ │ │ │ │ ├─ CrudPage.vue
│ │ │ │ │ └─ DocumentationPage.vue
│ │ │ │ ├─ AccountPage.vue
│ │ │ │ ├─ EmptyPage.vue
│ │ │ │ └─ NotFoundPage.vue
│ │ │ └─ DashboardPage.vue
│ │ ├─ App.vue
│ │ ├─ helpers.js
│ │ └─ main.js
│ ├─ .env
│ ├─ .env.example
│ ├─ .gitignore
│ ├─ .prettierrc.json
│ ├─ eslint.config.mjs
│ ├─ index.html
│ ├─ jsconfig.json
│ ├─ package-lock.json
│ ├─ package.json
│ ├─ postcss.config.js
│ ├─ tailwind.config.js
│ ├─ vercel.json
│ └─ vite.config.mjs
├─ LICENSE
├─ llm_context.md
├─ Readme.md
└─ update-fork.sh

# Packages & Dependencies

/be_velvue/composer.json

{
"$schema": "https://getcomposer.org/schema.json",
"name": "laravel/laravel",
"type": "project",
"description": "The skeleton application for the Laravel framework.",
"keywords": [
"laravel",
"framework"
],
"license": "MIT",
"require": {
"php": "^8.2",
"intervention/image": "^3.10",
"laravel/cashier": "^15.6",
"laravel/framework": "^11.31",
"laravel/sanctum": "^4.0",
"laravel/socialite": "^5.16",
"laravel/tinker": "^2.9"
},
"require-dev": {
"fakerphp/faker": "^1.23",
"larastan/larastan": "^3.0",
"laravel/pail": "^1.1",
"laravel/pint": "^1.13",
"laravel/telescope": "^5.2",
"mockery/mockery": "^1.6",
"nunomaduro/collision": "^8.1",
"phpstan/phpstan": "^2.1",
"phpunit/phpunit": "^11.0.1"
},
"autoload": {
"psr-4": {
"App\\": "app/",
"Database\\Factories\\": "database/factories/",
"Database\\Seeders\\": "database/seeders/"
}
},
"autoload-dev": {
"psr-4": {
"Tests\\": "tests/"
}
},
"scripts": {
"post-autoload-dump": [
"Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
"@php artisan package:discover --ansi"
],
"post-update-cmd": [
"@php artisan vendor:publish --tag=laravel-assets --ansi --force"
],
"post-root-package-install": [
"@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
],
"post-create-project-cmd": [
"@php artisan key:generate --ansi",
"@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
"@php artisan migrate --graceful --ansi"
],
"dev": [
"Composer\\Config::disableProcessTimeout",
"npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite"
]
},
"extra": {
"laravel": {
"dont-discover": []
}
},
"config": {
"optimize-autoloader": true,
"preferred-install": "dist",
"sort-packages": true,
"allow-plugins": {
"pestphp/pest-plugin": true,
"php-http/discovery": true
}
},
"minimum-stability": "stable",
"prefer-stable": true
}

/fe_velvue/package.json
{
"name": "velvue",
"version": "1.0.0",
"scripts": {
"dev": "vite",
"build": "vite build",
"preview": "vite preview",
"lint:check": "eslint .",
"lint:fix": "eslint --fix .",
"format:check": "prettier --check .",
"format:write": "prettier --write ."
},
"dependencies": {
"@primevue/themes": "^4.2.5",
"axios": "^1.7.9",
"chart.js": "4.4.7",
"js-cookie": "^3.0.5",
"pinia": "^2.3.0",
"primeicons": "^7.0.0",
"primevue": "^4.2.5",
"vue": "^3.5.13",
"vue-router": "^4.5.0"
},
"devDependencies": {
"@eslint/js": "^9.26.0",
"@primevue/auto-import-resolver": "^4.2.5",
"@rushstack/eslint-patch": "^1.10.4",
"@vitejs/plugin-vue": "^5.2.1",
"@vue/eslint-config-prettier": "^10.1.0",
"autoprefixer": "^10.4.20",
"eslint": "^9.17.0",
"eslint-plugin-vue": "^9.32.0",
"globals": "^16.1.0",
"postcss": "^8.4.49",
"prettier": "^3.4.2",
"sass": "^1.83.1",
"tailwindcss": "^3.4.17",
"tailwindcss-primeui": "^0.3.4",
"unplugin-vue-components": "^0.28.0",
"vite": "^6.0.7"
}
}
