# SessionVault

SessionVault is an intentionally vulnerable session management security lab. It is a realistic small e-commerce/customer portal used to teach and test session-management vulnerabilities in a safe, local environment.

> **SECURITY TRAINING LAB — Intentionally Vulnerable Environment**
>
> This application deliberately contains security weaknesses. Only run it against systems you own or have explicit permission to test.

## 1. Lab Purpose

SessionVault provides a realistic application surface (authentication, profile management, orders, cart, checkout, and account administration) with carefully isolated session-management vulnerabilities. Each weakness is:

- Deliberate and documented
- Independently configurable
- Reproducible with Burp Suite
- Paired with a secure reference implementation
- Covered by automated tests

The goal is not to build a "terrible" application, but to build a realistic one with controlled weaknesses that teach how session management fails and how to remediate it.

## 2. Legal/Safety Notice

This application intentionally contains vulnerabilities and is designed for local security education. Only test against systems you own or have explicit permission to test.

Do not deploy this application to the public internet. All data is fake, local, and disposable.

## 3. Architecture

```
Browser
   |
   v
Nginx (localhost:8080)
   |
   +----> Laravel App (PHP 8.2, port 8000)
   |         |
   |         +----> Blade Frontend
   |         |
   |         +----> REST API (/api/*)
   |
   v
PostgreSQL (localhost:5432)
```

- **Backend:** Laravel 11 (PHP 8.2)
- **Frontend:** Server-rendered Blade templates
- **Database:** PostgreSQL 16
- **Web server:** Nginx (reverse proxy)
- **Testing:** Burp Suite Community Edition

## 4. Requirements

- Docker
- Docker Compose
- Git
- Burp Suite Community Edition
- A web browser (optional but recommended)

## 5. Installation

```bash
git clone <repository-url> sessionvault
cd sessionvault
cp .env.example .env
docker compose up --build
```

The application will be available at http://localhost:8080.

## 6. Seeded Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.local | LabPass123! |
| User | alice@example.local | LabPass123! |
| User | bob@example.local | LabPass123! |
| User | charlie@example.local | LabPass123! |

These are lab-only credentials. Do not reuse them anywhere.

## 7. Application URLs

| Service | URL |
|---------|-----|
| Web application | http://localhost:8080 |
| REST API | http://localhost:8080/api |
| PostgreSQL | localhost:5432 |

## 8. Vulnerability Matrix

| ID | Vulnerability | Location | Difficulty | Expected Evidence |
|----|---------------|----------|------------|-------------------|
| SM-01 | Predictable Session ID | Login | Easy | Predictable tokens |
| SM-02 | Session Fixation | Login | Medium | Same token pre/post login |
| SM-03 | No Rotation | Login | Easy | Token unchanged |
| SM-04 | Missing HttpOnly | Cookie | Easy | Set-Cookie |
| SM-05 | Missing Secure | Cookie | Easy | Set-Cookie |
| SM-06 | Weak SameSite | Cookie | Easy | Set-Cookie |
| SM-07 | Long Session | Session | Easy | Old session remains |
| SM-08 | Logout Failure | Logout | Medium | Replay succeeds |
| SM-09 | Token in URL | Legacy route | Easy | URL token |
| SM-10 | Unlimited Sessions | Sessions | Medium | Many active sessions |
| SM-11 | Password Change | Account | Medium | Old session survives |
| SM-12 | Account Disable | Admin | Medium | Disabled user session works |

## 9. Quick Start

1. Start the lab: `docker compose up --build`
2. Open http://localhost:8080
3. Log in with `alice@example.local` / `LabPass123!`
4. Configure Burp Suite to intercept traffic on port 8080
5. Follow the testing guides in `SECURITY-LAB.md`

## 10. Reset the Lab

```bash
./scripts/reset-lab.sh
```

This removes the database volume, recreates it, runs migrations, and re-seeds all data.

## 11. Documentation

- `SECURITY-LAB.md` — Detailed vulnerability explanations and testing guides
- `CHANGELOG.md` — Development milestones

## 12. License

SessionVault is provided for educational purposes only.
