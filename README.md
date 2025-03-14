# Air pollution monitoring system

## Prerequisites

- [Docker](https://www.docker.com/products/docker-desktop/)
- [Bruno](https://www.usebruno.com/downloads) or other API client

## Documentation

API documentation can be found [here](/docs/api/), along with requests for use with Bruno.

## Setup

1. **Clone the repository:**

```bash
git clone git@github.com:MASSHUU12/ipz.git && cp ipz
```

2. **Set up the environment file:**

```bash
cp .env.example .env
```

3. **Set up Docker containers:**

```bash
docker-compose build # required only once or when Dockerfile have changed
docker-compose up -d
```

phpMyAdmin is available under port **6969**.

4. **Connect to the container:**

> [!NOTE]
> If this command fails check container name using `docker ps`.

```bash
docker exec -it ipz-web /bin/bash
```

5. **Generate the application key:**

```bash
php artisan key:generate
```

6. **Run database migrations:**

```bash
php artisan migrate
```

7. **Run the development server (API):**

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

The application will be available at `http://localhost:8000`.

8. **Run the React development server (frontend):**

```bash
bun install
bun run dev
```
