# Ominimo Blog

A simple blog application built with **Laravel** as an interview assignment for Ominimo Insurance.

Users can register, write posts, and comment. Guests can read everything and leave comments under a name. Post owners manage their own content, and an admin role can moderate any post or comment.

## Features

- **Authentication** – registration, login and logout via Laravel Breeze (Blade).
- **Posts CRUD** – list, view, create, edit and delete posts, with validation through Form Requests.
- **Comments** – logged-in users and guests can comment; comments can be deleted by their author or by the post owner.
- **Authorization** – Laravel Policies (`PostPolicy`, `CommentPolicy`) plus a custom authentication middleware.
- **Role-based access control** – an `admin` role that can delete any post or comment.
- **REST API** – JSON API under `/api`, secured with Laravel Sanctum (SPA cookie authentication).
- **Vue 3 frontend** – the post and comment pages are Vue components that consume the API.
- **Tests** – feature and unit tests for the web routes, the API, policies and models.
- **Docker** – multi-stage Dockerfile and a Compose setup with MySQL.

## Tech stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4, Laravel 13 |
| Auth | Laravel Breeze (Blade), Laravel Sanctum |
| Frontend | Blade layouts, Vue 3, Tailwind CSS, Vite |
| Database | SQLite (local), MySQL 8.4 (Docker) |
| Testing | PHPUnit |
| Containers | Docker, Docker Compose |

---

## Getting started

There are two ways to run the project: **locally** (PHP + Node on your machine) or with **Docker** (nothing but Docker required).

### Demo accounts

The seeders create these accounts (both use the password `password`):

| Role | Email | Password |
|---|---|---|
| Admin | `admin@example.com` | `password` |
| Regular user | `user@example.com` | `password` |

Five additional random users, 15 posts and a mix of user and guest comments are also created.

### Option A: Run locally

**Requirements:** PHP 8.3+, Composer, Node.js 20+ and npm.

```bash
git clone https://github.com/YOUR_USERNAME/ominimo-blog.git
cd ominimo-blog

composer install
npm install
npm run build

cp .env.example .env
php artisan key:generate
```

Create the SQLite database file, then run migrations with seed data:

```bash
# macOS / Linux
touch database/database.sqlite

# Windows (PowerShell)
New-Item database/database.sqlite

php artisan migrate --seed
```

Start the development server:

```bash
php artisan serve
```

Open **http://localhost:8000**.

> If you serve the app from another address (for example with Laravel Herd at `http://ominimo-blog.test`), set `APP_URL` in `.env` to that address. Sanctum uses it to recognise requests coming from the frontend.

To reset the database with fresh sample data at any time:

```bash
php artisan migrate:fresh --seed
```

### Option B: Run with Docker

**Requirements:** Docker with Docker Compose.

```bash
git clone https://github.com/YOUR_USERNAME/ominimo-blog.git
cd ominimo-blog

docker compose up --build
```

Open **http://localhost:8000**.

On startup the container waits for MySQL to become healthy, runs the migrations and seeds the database. Seeding is idempotent, so restarting the containers does not duplicate data.

Useful commands:

```bash
docker compose logs -f app     # follow application logs
docker compose exec app bash   # open a shell inside the app container
docker compose down            # stop containers (database is kept)
docker compose down -v         # stop containers and delete the database volume
```

#### Production image

The same Dockerfile produces the production image. Without the `INSTALL_DEV` build argument, dev dependencies are left out:

```bash
docker build -t ominimo-blog:prod .
```

Run it with production configuration supplied through environment variables, for example:

```bash
docker run -p 80:80 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e APP_KEY=base64:... \
  -e APP_URL=https://blog.example.com \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=your-db-host \
  -e DB_DATABASE=blog \
  -e DB_USERNAME=blog \
  -e DB_PASSWORD=... \
  ominimo-blog:prod
```

With `APP_ENV=production`, the entrypoint also caches the configuration, routes and views. Generate a permanent key with `php artisan key:generate --show` and keep it secret.

---

## Running the tests

```bash
php artisan test
```

The tests use an in-memory SQLite database (configured in `phpunit.xml`), so they never touch your development data.

| Test | Covers |
|---|---|
| `tests/Feature/PostTest.php` | Post pages, create/update/delete, validation, ownership, admin rules, cascade delete |
| `tests/Feature/CommentTest.php` | Guest and user comments, validation, delete permissions, admin rules |
| `tests/Feature/ApiPostTest.php` | Post API: pagination, JSON structure, auth (401), validation (422), authorization (403), permission flags |
| `tests/Feature/ApiCommentTest.php` | Comment API: guest comments, validation, delete permissions |
| `tests/Unit/CommentAuthorNameTest.php` | Comment author name resolution for users and guests |
| `tests/Unit/UserRoleTest.php` | Admin role detection |
| `tests/Feature/Auth/*` | Registration, login, password and profile flows (from Breeze) |

---

## Routes

### Web routes

| Method | URI | Access | Description |
|---|---|---|---|
| GET | `/posts` | Everyone | List all posts |
| GET | `/posts/create` | Authenticated | Form for a new post |
| POST | `/posts` | Authenticated | Store a post |
| GET | `/posts/{id}` | Everyone | Show a post with its comments |
| GET | `/posts/{id}/edit` | Post owner | Edit form |
| PUT | `/posts/{id}` | Post owner | Update a post |
| DELETE | `/posts/{id}` | Post owner, admin | Delete a post |
| POST | `/posts/{id}/comments` | Everyone (rate limited) | Add a comment |
| DELETE | `/comments/{id}` | Comment author, post owner, admin | Delete a comment |

### API routes (`/api`)

| Method | URI | Access | Response |
|---|---|---|---|
| GET | `/api/posts` | Everyone | Paginated list of posts |
| GET | `/api/posts/{id}` | Everyone | Post with its comments |
| POST | `/api/posts` | Authenticated | `201` with the created post |
| PUT | `/api/posts/{id}` | Post owner | Updated post |
| DELETE | `/api/posts/{id}` | Post owner, admin | `204 No Content` |
| POST | `/api/posts/{id}/comments` | Everyone (rate limited) | `201` with the created comment |
| DELETE | `/api/comments/{id}` | Comment author, post owner, admin | `204 No Content` |
| GET | `/api/user` | Authenticated | The current user |

Validation errors return `422`, unauthenticated requests `401`, and forbidden actions `403`.

---

## Design decisions

**Guest comments.** The assignment allows guests to comment, so `comments.user_id` is nullable and a `guest_name` column was added. Guests must provide a name; for logged-in users the name comes from their account and any submitted `guest_name` is ignored.

**Mass assignment protection.** `user_id`, `post_id` and `role` are not in `$fillable`. Ownership is always set from the authenticated user (for example `$request->user()->posts()->create(...)`), so it cannot be spoofed through form input. This also prevents users from assigning themselves the admin role.

**Authorization in one place.** All ownership rules live in `PostPolicy` and `CommentPolicy`. The web controllers, the API controllers, the Blade views and the API resources all use the same policies. The API returns `can` flags (`can.update`, `can.delete`) so the Vue frontend shows only the actions the user is allowed to take, while the server still checks every request.

**Admin role.** Implemented with a PHP enum (`App\Enums\Role`) and a `before()` hook in the policies. As specified, admins may **delete** any post or comment, but cannot **edit** content they do not own.

**Custom middleware.** `EnsureUserIsAuthenticated` protects the routes that require login. It redirects browsers to the login page (remembering the intended URL) and returns `401` for JSON requests. Breeze's own routes keep the built-in `auth` middleware.

**Validation reuse.** The same Form Request classes validate both web and API requests; Laravel returns redirects with errors for the web and `422` JSON for the API.

**N+1 queries.** Listings eager-load relationships (`with('user')`, `withCount('comments')`), and API resources only include relations that were loaded (`whenLoaded`, `whenCounted`).

**Frontend architecture.** Laravel renders the layout and authentication pages (Breeze), and Vue components are mounted on the post pages ("islands"). Because the frontend and the API share a domain, Sanctum's cookie-based SPA authentication works without storing tokens in the browser, and CSRF protection stays enabled. The web routes required by the assignment remain available and tested.

**Spam protection.** Comment creation is rate limited (10 requests per minute) because guests can post without an account.

**Docker.** A multi-stage build installs PHP and Node dependencies in separate stages, so the final image contains no Composer or Node tooling. The same image serves local development and production; behaviour is controlled by environment variables. Seeding is idempotent so containers can be restarted safely.

---

## Possible improvements

- Extract comment creation into a dedicated action class shared by the web and API controllers.
- Allow editing comments and add soft deletes for moderation history.
- Add a CI pipeline (for example GitHub Actions) that runs the test suite and builds the Docker image.
- Add frontend tests for the Vue components.

---

## Project structure

```
app/
├── Enums/Role.php                          # User roles
├── Http/
│   ├── Controllers/
│   │   ├── PostController.php              # Web: post pages and actions
│   │   ├── CommentController.php           # Web: comment actions
│   │   └── Api/                            # JSON API controllers
│   ├── Middleware/EnsureUserIsAuthenticated.php
│   ├── Requests/                           # Validation (Form Requests)
│   └── Resources/                          # API response shapes
├── Models/                                 # User, Post, Comment
└── Policies/                               # PostPolicy, CommentPolicy
database/
├── factories/                              # Test data factories
├── migrations/
└── seeders/                                # User, Post and Comment seeders
resources/
├── js/
│   ├── api.js                              # Axios client for the API
│   └── components/                         # Vue components
└── views/                                  # Blade layouts and page shells
routes/
├── web.php
└── api.php
docker/                                     # Apache config and entrypoint
tests/
├── Feature/
└── Unit/
```
