# Air pollution monitoring system

## Prerequisites

- [PHP 8^](https://www.php.net/downloads.php) or [XAMPP](https://www.apachefriends.org/download.html)
- [Composer](https://getcomposer.org/)
- [Docker](https://www.docker.com/products/docker-desktop/), for database or [XAMPP](https://www.apachefriends.org/download.html)
- [Bruno](https://www.usebruno.com/downloads) or other API client
- [Bun](https://bun.sh/)/[Node.js](https://nodejs.org/en)

## Setup

1. **Clone the repository:**

```bash
git clone git@github.com:MASSHUU12/ipz.git && cp ipz
```

2. **Set up the environment file:**

```bash
cp .env.example .env
```

Update the `.env` file with your database credentials and other environment settings.

### Using Laravel Sail

> [!NOTE]
> For Windows you need to have [WSL2](https://docs.microsoft.com/en-us/windows/wsl/about)
> enabled according to the [documentation](https://laravel.com/docs/12.x/sail#introduction).

3. **Start Sail:**

```bash
./vendor/bin/sail up
```

4. **Run database migrations:**

```bash
./vendor/bin/sail artisan migrate
```

### Alternative

3. **Set up Docker containers:**

```bash
docker-compose -f docker-compose.db.yml up -d
```

phpMyAdmin is available under port **6969**.

4. **Install PHP dependencies:**

```bash
composer install
```

5. **Generate the application key:**

```bash
php artisan key:generate
```

6. **Run database migrations:**

```bash
php artisan migrate
```

7. **Install Bun/Node.js dependencies:**

```bash
bun install # or npm install
```

8. **Run the development server (API):**

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

9. **Run the React development server (frontend):**

```bash
bun run dev # or npm run dev
```

#### Troubleshooting

##### **Laravel can't connect to the database:**

Check if `extension=pdo_mysql.so` is uncommented in `php.ini`.

[Fedora] If that doesn't work install the `php-mysqlnd` package.

## Documentation

API documentation can be found in the [docs/api](./docs/api) folder.
