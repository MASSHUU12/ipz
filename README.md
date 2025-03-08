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
git clone git@github.com:MASSHUU12/ipz.git
cd ipz
```

2. **Set up Docker containers:**

```bash
docker-compose up -d
```

3. **Install PHP dependencies:**

```bash
composer install
```

4. **Set up the environment file:**

```bash
cp .env.example .env
```

Update the `.env` file with your database credentials and other environment settings.

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

8. **Build the frontend assets:**

```bash
bun run build # or npm run build
```

9. **Run the development server:**

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

10. **Run the React development server (optional):**

```bash
bun run dev # or npm run dev
```

This will run the React app at `http://localhost:3000`.
