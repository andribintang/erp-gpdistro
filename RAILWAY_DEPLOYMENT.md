# Railway Deployment

## Services

Create these Railway services from the same repository:

1. `web`: use the repository `Dockerfile`.
2. `worker`: use the same image and override the start command with
   `php artisan horizon`.
3. MySQL database.
4. Redis database.

Run migrations during release or immediately before promoting a deployment:

```sh
php artisan migrate --force
```

## Required Variables

```dotenv
APP_NAME="GPDISTRO ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_TIMEZONE=Asia/Jakarta
APP_KEY=

DB_CONNECTION=mysql
DB_URL=

REDIS_URL=
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_SECURE_COOKIE=true

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_ENDPOINT=
AWS_URL=
```

Generate `APP_KEY` once with:

```sh
php artisan key:generate --show
```

Use Railway's `/up` endpoint health check. Uploaded product media, payment
proofs, and production artifacts must use durable S3-compatible storage rather
than the container filesystem.
