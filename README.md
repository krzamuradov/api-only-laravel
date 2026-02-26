# API First (Laravel 11)

API-only проект на Laravel 11 c:
- версионированием `v1`
- CRUD для `posts`
- `Sanctum` токен-аутентификацией
- `Policy` (изменять/удалять пост может только владелец)
- единым JSON-форматом ошибок
- feature-тестами

## Требования

- PHP `>= 8.2`
- Composer
- SQLite (по умолчанию) или MySQL/PostgreSQL

## Установка

```bash
git clone <YOUR_GITHUB_REPO_URL>
cd api_first
composer install
cp .env.example .env
php artisan key:generate
```

## Настройка окружения

По умолчанию можно использовать SQLite.

1. Убедись, что файл БД существует:

```bash
touch database/database.sqlite
```

2. Проверь `.env`:

```env
APP_NAME="API First"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite
```

Если используешь MySQL/PostgreSQL, укажи соответствующие `DB_*` переменные.

## Миграции и тестовые данные

```bash
php artisan migrate
php artisan db:seed
```

Сидер создаёт:
- тестового пользователя (`test@example.com`)
- тестовые записи `posts`

## Запуск

```bash
php artisan serve
```

API будет доступен на:
`http://127.0.0.1:8000/api/v1`

## Основные маршруты

### Auth

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `GET /api/v1/auth/me` (Bearer token)
- `POST /api/v1/auth/logout` (Bearer token)

### Posts

Публичные:
- `GET /api/v1/posts`
- `GET /api/v1/posts/{id}`

Требуют токен:
- `POST /api/v1/posts`
- `PUT/PATCH /api/v1/posts/{id}`
- `DELETE /api/v1/posts/{id}`

## Быстрый сценарий (curl)

### 1) Регистрация

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Demo User",
    "email":"demo@example.com",
    "password":"password123",
    "device_name":"local-cli"
  }'
```

Скопируй `token` из ответа.

### 2) Создать пост

```bash
curl -X POST http://127.0.0.1:8000/api/v1/posts \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "title":"First Post",
    "content":"Hello API"
  }'
```

### 3) Получить список

```bash
curl "http://127.0.0.1:8000/api/v1/posts?per_page=10&sort=-created_at"
```

## Тесты

```bash
php artisan test
```

## Полезные команды

```bash
php artisan route:list
php artisan migrate:fresh --seed
```
