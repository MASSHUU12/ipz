# Setup

1. **Clone the repository:**

```bash
git clone git@github.com:MASSHUU12/ipz.git && cp ipz
```

2. **Set up the environment file:**

```bash
cp .env.example .env
```

Also, you need to set an email password here, which you can find on Discord.

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

6. **Run database migrations and seed with roles and permissions:**

```bash
php artisan migrate:fresh --seed
```

7. **Run the development server (API):**

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

API will be available at `http://localhost:8000`.

8. **Run the React development server (frontend):**

```bash
bun install
bun run dev
```

Frontend will be available at `http://localhost:8000`.

9. **(Optional) Automatically run scheduled tasks:**

> [!WARNING]
> Omitting this step may result in some functionality to be unavailable.

**When you have time**:

The server is set up to perform certain actions every so often,
but in order for them to execute,
the scheduler and queue must run in the background:

```sh
php artisan schedule:work &
php artisan queue:work &
```

> After each change to the dependent code,
> it must be restarted for the changes to take effect.

**When you don't want to wait**:

> [!NOTE]
> For this to work, queue must be running.

Adding tasks to the queue can be forced using commands.
For each task, there is a command that adds it to the queue, such as:

- `synoptic:store`
- `check:temp-warnings`
- `send:warnings-emails`
- `airpollution:store`
- `airpollution:delete-old`
- `airpollution:leaderboard`
