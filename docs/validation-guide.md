# Validation Guide

This guide explains how to validate that the SessionVault lab is running correctly and that all deliverables are in place.

## 1. Validate Docker Compose Configuration

```bash
docker compose config
```

This should output the resolved configuration without errors.

## 2. Start the Lab

```bash
docker compose down -v
docker compose up --build
```

Expected output:

- PostgreSQL container starts and initializes.
- Laravel app container runs migrations and seeds.
- Nginx proxies requests to the app.

## 3. Verify the Application

### Health Check

```bash
curl -I http://localhost:8080
```

Expected: HTTP 200 OK.

### Login Test

```bash
curl -c /tmp/cookies.txt -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "alice@example.local", "password": "LabPass123!"}'
```

Expected: HTTP 200 with session cookie.

### Session Cookie Inspection

```bash
grep sessionvault_session /tmp/cookies.txt
```

Expected: A valid session ID.

## 4. Run Automated Tests

```bash
docker compose exec app php artisan test
```

All tests should pass.

## 5. Verify Reset Script

```bash
./scripts/reset-lab.sh
```

Expected: The database is recreated, migrations run, and seed data is loaded.

## 6. Check All Deliverables

| Deliverable | Check |
|-------------|-------|
| Docker Compose stack | `docker compose config` succeeds |
| SECURITY-LAB.md | Contains per-vulnerability guides |
| Automated tests | `php artisan test` passes |
| Reset script | `./scripts/reset-lab.sh` works |
| Docs directory | Contains Burp setup, secure reference, validation guides |

## 7. Common Issues

### Database Connection Error

- Ensure PostgreSQL is running: `docker compose ps`.
- Check credentials in `.env`.

### App Returns 500

- Check logs: `docker compose logs app`.
- Run migrations manually: `docker compose exec app php artisan migrate:fresh --seed`.
