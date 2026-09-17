# SessionVault Security Lab Guide

SessionVault is a deliberately vulnerable session-management training application. It demonstrates 12 common session-management weaknesses (SM-01 through SM-12) in a safe, local environment. Each vulnerability is independently toggleable via environment variables and is paired with a secure reference implementation.

> **Safety Notice:** Only test against systems you own or have explicit permission to test. SessionVault is designed for local use only. Do not deploy it to the public internet.

---

## 1. Overview

SessionVault is a Laravel 11 + PostgreSQL security lab built around a realistic small e-commerce/customer portal. It exposes authentication, profile management, order management, and session-management APIs so that each weakness can be reproduced and verified with Burp Suite.

| ID | Name | Description |
|----|------|-------------|
| SM-01 | Predictable Session ID | Session identifiers follow a predictable sequence |
| SM-02 | Session Fixation | Pre-authentication session survives login |
| SM-03 | No Rotation | Session identifier does not change after authentication |
| SM-04 | Missing HttpOnly | Cookie is readable by JavaScript |
| SM-05 | Missing Secure | Cookie is sent over HTTP |
| SM-06 | Weak SameSite | Cookie accepts cross-site requests |
| SM-07 | Long Session | Excessive session lifetime |
| SM-08 | Logout Failure | Logout does not invalidate server-side session |
| SM-09 | Token in URL | Session token accepted in query string |
| SM-10 | Unlimited Sessions | No cap on concurrent sessions |
| SM-11 | Password Change | Existing sessions survive password change |
| SM-12 | Account Disable | Disabled account sessions remain valid |

---

## 2. Environment Variables

All vulnerabilities default to **enabled** (`true`) in vulnerable mode. Set `LAB_MODE=secure` or set individual `VULN_*` flags to `false` to exercise the secure reference behavior.

| Variable | Default | Vulnerable Behavior |
|----------|---------|---------------------|
| `VULN_PREDICTABLE_SESSION` | `true` | Sequential session IDs |
| `VULN_SESSION_FIXATION` | `true` | Reuse pre-auth session |
| `VULN_NO_SESSION_ROTATION` | `true` | No ID rotation after login |
| `VULN_MISSING_HTTPONLY` | `true` | Cookie lacks `HttpOnly` |
| `VULN_MISSING_SECURE` | `true` | Cookie lacks `Secure` |
| `VULN_WEAK_SAMESITE` | `true` | Cookie uses `SameSite=Lax` |
| `VULN_LONG_SESSION` | `true` | 24-hour session lifetime |
| `VULN_LOGOUT_NOT_INVALIDATE` | `true` | Logout leaves server session valid |
| `VULN_SESSION_IN_URL` | `true` | Session token accepted in URL |
| `VULN_CONCURRENT_SESSIONS` | `true` | Unlimited concurrent sessions |
| `VULN_PASSWORD_CHANGE_INVALIDATION` | `true` | Password change keeps sessions |
| `VULN_ACCOUNT_DISABLE_INVALIDATION` | `true` | Disable keeps sessions valid |
| `LAB_SESSION_LIFETIME` | `86400` | Session lifetime in seconds |

---

## 3. Quick Start

```bash
docker compose up --build
```

The application is available at `http://localhost:8080`.

Seeded accounts:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.local | LabPass123! |
| User | alice@example.local | LabPass123! |
| User | bob@example.local | LabPass123! |
| User | charlie@example.local | LabPass123! |

---

## 4. Burp Suite Setup

1. Open Burp Suite and go to **Proxy → Options**.
2. Configure the proxy listener on `127.0.0.1:8080` (or your preferred port).
3. Install the Burp CA certificate in your browser.
4. Open `http://localhost:8080` and log in with `alice@example.local / LabPass123!`.
5. In Burp, intercept the login request and inspect the `Set-Cookie` header.

---

## 5. Per-Vulnerability Testing Guides

### SM-01 — Predictable Session ID

**Objective:** Confirm that session IDs are predictable and sequential.

**Preconditions:** `VULN_PREDICTABLE_SESSION=true`.

**Setup:** Start with a clean database (`./scripts/reset-lab.sh`).

**Steps:**

1. Log in as `alice@example.local`.
2. In Burp, inspect the `Set-Cookie` header.
3. Log out, then log in again.
4. Repeat for several sessions.

**Burp Procedure:**

- Intercept each `/api/auth/login` response.
- Record the `sessionvault_session` cookie value.
- Compare the values across logins.

**Expected Vulnerable Result:** Session IDs follow the pattern `session-000001`, `session-000002`, etc.

**Why It Matters:** An attacker who can observe or guess session IDs can hijack other users' sessions.

**Secure Behavior:** With `VULN_PREDICTABLE_SESSION=false`, IDs are random 32-character values prefixed with `sess_`.

**Remediation:** Use `Str::random(32)` or a cryptographically secure random generator.

**Verification:** Confirm that IDs do not follow a sequential pattern.

---

### SM-02 — Session Fixation

**Objective:** Confirm that a pre-authentication session survives login.

**Preconditions:** `VULN_SESSION_FIXATION=true`.

**Setup:** Start with a clean database.

**Steps:**

1. Visit the login page without authenticating.
2. Capture the pre-auth session cookie in Burp.
3. Log in with valid credentials.
4. Compare the cookie value before and after login.

**Burp Procedure:**

- Intercept the pre-auth request and note the `sessionvault_session` cookie.
- Intercept the login response and check whether the same ID is reused.

**Expected Vulnerable Result:** The same session ID is used before and after authentication.

**Why It Matters:** An attacker who can set a victim's session ID can maintain access after the victim logs in.

**Secure Behavior:** With `VULN_SESSION_FIXATION=false`, a new session ID is created after login.

**Remediation:** Always rotate the session ID after authentication.

**Verification:** Confirm that the pre-auth ID differs from the post-auth ID.

---

### SM-03 — No Session Rotation

**Objective:** Confirm that the session ID does not change after authentication.

**Preconditions:** `VULN_NO_SESSION_ROTATION=true`.

**Steps:**

1. Log in with valid credentials.
2. Capture the session ID.
3. Log out and log in again.
4. Compare session IDs across the two logins.

**Burp Procedure:**

- Compare the `sessionvault_session` cookie in both login responses.

**Expected Vulnerable Result:** The session ID remains unchanged.

**Why It Matters:** Session fixation and replay attacks become possible when IDs are not rotated.

**Secure Behavior:** With `VULN_NO_SESSION_ROTATION=false`, the old session is destroyed and a new one is created.

**Remediation:** Regenerate the session identifier after authentication.

**Verification:** Confirm that the post-auth ID differs from the pre-auth ID.

---

### SM-04 — Missing HttpOnly

**Objective:** Confirm that the session cookie is readable by JavaScript.

**Preconditions:** `VULN_MISSING_HTTPONLY=true`.

**Steps:**

1. Log in to the application.
2. Inspect the `Set-Cookie` header in Burp.
3. Check whether the `HttpOnly` attribute is present.

**Burp Procedure:**

- Intercept the login response.
- Review the `Set-Cookie` header for the absence of `HttpOnly`.

**Expected Vulnerable Result:** The cookie does not include `HttpOnly`.

**Why It Matters:** Client-side scripts can steal the session cookie via XSS.

**Secure Behavior:** With `VULN_MISSING_HTTPONLY=false`, the cookie includes `HttpOnly`.

**Remediation:** Always set the `HttpOnly` flag on session cookies.

**Verification:** Confirm that `HttpOnly` is present.

---

### SM-05 — Missing Secure

**Objective:** Confirm that the session cookie is sent over HTTP.

**Preconditions:** `VULN_MISSING_SECURE=true`.

**Steps:**

1. Log in to the application.
2. Inspect the `Set-Cookie` header.
3. Check whether the `Secure` attribute is present.

**Burp Procedure:**

- Intercept the login response.
- Review the `Set-Cookie` header for the absence of `Secure`.

**Expected Vulnerable Result:** The cookie does not include `Secure`.

**Why It Matters:** Session cookies can be intercepted on unencrypted connections.

**Secure Behavior:** With `VULN_MISSING_SECURE=false`, the cookie includes `Secure`.

**Remediation:** Always set the `Secure` flag on session cookies.

**Verification:** Confirm that `Secure` is present.

---

### SM-06 — Weak SameSite

**Objective:** Confirm that the cookie accepts cross-site requests.

**Preconditions:** `VULN_WEAK_SAMESITE=true`.

**Steps:**

1. Log in to the application.
2. Inspect the `Set-Cookie` header.
3. Check the `SameSite` attribute.

**Burp Procedure:**

- Intercept the login response.
- Review the `Set-Cookie` header for `SameSite=Lax`.

**Expected Vulnerable Result:** The cookie uses `SameSite=Lax` instead of `SameSite=Strict`.

**Why It Matters:** Cross-site request forgery (CSRF) attacks become easier.

**Secure Behavior:** With `VULN_WEAK_SAMESITE=false`, the cookie uses `SameSite=Strict`.

**Remediation:** Use `SameSite=Strict` for session cookies.

**Verification:** Confirm that `SameSite=Strict` is present.

---

### SM-07 — Long Session

**Objective:** Confirm that the session lifetime is excessive.

**Preconditions:** `VULN_LONG_SESSION=true` and `LAB_SESSION_LIFETIME=86400`.

**Steps:**

1. Log in to the application.
2. Inspect the `Set-Cookie` header.
3. Check the `Max-Age` attribute.

**Burp Procedure:**

- Intercept the login response.
- Review the `Set-Cookie` header for `Max-Age=86400`.

**Expected Vulnerable Result:** The session cookie is valid for 24 hours.

**Why It Matters:** Stolen cookies remain usable for an extended period.

**Secure Behavior:** With `VULN_LONG_SESSION=false`, a shorter lifetime is used.

**Remediation:** Use a shorter session lifetime and enforce re-authentication.

**Verification:** Confirm that the lifetime is appropriate for the application.

---

### SM-08 — Logout Failure

**Objective:** Confirm that logout does not invalidate the server-side session.

**Preconditions:** `VULN_LOGOUT_NOT_INVALIDATE=true`.

**Steps:**

1. Log in to the application.
2. Capture the session ID.
3. Log out.
4. Replay the session ID against a protected endpoint.

**Burp Procedure:**

- Intercept the logout request and response.
- Replay the original session cookie against `/api/auth/me`.

**Expected Vulnerable Result:** The old session remains valid after logout.

**Why It Matters:** Stolen or cached session tokens remain usable after the user logs out.

**Secure Behavior:** With `VULN_LOGOUT_NOT_INVALIDATE=false`, the server-side session is deleted.

**Remediation:** Delete the server-side session on logout.

**Verification:** Confirm that the old session ID no longer works.

---

### SM-09 — Token in URL

**Objective:** Confirm that a session token is accepted in the query string.

**Preconditions:** `VULN_SESSION_IN_URL=true`.

**Steps:**

1. Log in to the application.
2. Capture the session ID.
3. Visit `/api/legacy/profile?session=<session-id>`.

**Burp Procedure:**

- Send a GET request to the legacy profile endpoint with the session token in the URL.

**Expected Vulnerable Result:** The endpoint returns session details.

**Why It Matters:** Tokens in URLs leak through browser history, logs, and referrers.

**Secure Behavior:** With `VULN_SESSION_IN_URL=false`, the endpoint rejects URL-based tokens.

**Remediation:** Accept session tokens only from cookies or authorization headers.

**Verification:** Confirm that the endpoint rejects tokens in the URL.

---

### SM-10 — Unlimited Sessions

**Objective:** Confirm that there is no cap on concurrent sessions.

**Preconditions:** `VULN_CONCURRENT_SESSIONS=true`.

**Steps:**

1. Log in to the application.
2. Create multiple sessions for the same user.
3. Check the session list.

**Burp Procedure:**

- Send multiple login requests with the same credentials.
- Compare the session IDs returned in each response.

**Expected Vulnerable Result:** All sessions remain active.

**Why It Matters:** Attackers can maintain persistence across multiple devices or browsers.

**Secure Behavior:** With `VULN_CONCURRENT_SESSIONS=false`, excess sessions are revoked.

**Remediation:** Enforce a maximum number of concurrent sessions per user.

**Verification:** Confirm that only the allowed number of sessions remain active.

---

### SM-11 — Password Change

**Objective:** Confirm that existing sessions survive a password change.

**Preconditions:** `VULN_PASSWORD_CHANGE_INVALIDATION=true`.

**Steps:**

1. Log in to the application.
2. Capture the session ID.
3. Change the password.
4. Replay the original session ID.

**Burp Procedure:**

- Send a password-change request.
- Replay the old session cookie against a protected endpoint.

**Expected Vulnerable Result:** The old session remains valid.

**Why It Matters:** Stolen sessions survive credential rotation.

**Secure Behavior:** With `VULN_PASSWORD_CHANGE_INVALIDATION=false`, all sessions are revoked.

**Remediation:** Invalidate all sessions after a password change.

**Verification:** Confirm that the old session ID no longer works.

---

### SM-12 — Account Disable

**Objective:** Confirm that disabled account sessions remain valid.

**Preconditions:** `VULN_ACCOUNT_DISABLE_INVALIDATION=true`.

**Steps:**

1. Log in as an admin.
2. Disable a user account.
3. Replay the disabled user's session ID.

**Burp Procedure:**

- Send a request to disable the user.
- Replay the disabled user's session cookie against a protected endpoint.

**Expected Vulnerable Result:** The disabled user's session remains valid.

**Why It Matters:** Disabled accounts can retain access to the application.

**Secure Behavior:** With `VULN_ACCOUNT_DISABLE_INVALIDATION=false`, sessions are invalidated.

**Remediation:** Invalidate all sessions when an account is disabled.

**Verification:** Confirm that the disabled user's session ID no longer works.

---

## 6. Automated Tests

Run the full test suite with:

```bash
docker compose exec app php artisan test
```

The suite covers all 12 vulnerabilities and verifies both vulnerable and secure reference behavior.

## 7. Reset the Lab

```bash
./scripts/reset-lab.sh
```

This removes the database volume, recreates it, runs migrations, and re-seeds all data.
