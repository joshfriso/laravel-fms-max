# ZIP template baseline

The project started from the supplied `laravel-fms.zip` as a functional and layout reference. Its `Folder`, `File`, and `User` models, CRUD routes, seeders, and AdminLTE navigation define the baseline workflow. The ZIP contains Laravel 9, Vue 2, Bootstrap, MySQL defaults, subscription/payment screens, and a bundled `vendor` directory. Those versions and extra screens do not meet the technical test, so this project implements the same file-management workflow on Laravel 11 with Vue, Tailwind, and PostgreSQL. No `.env`, bundled dependencies, payment code, or credentials from the ZIP are imported.

The template's folder/file entities are retained in the new data model. Folder nesting, department metadata, and administrator/viewer permissions are added to satisfy the test. The first UI keeps the template's straightforward sidebar, dashboard, and table workflow; visual polish and optional features are deferred.
