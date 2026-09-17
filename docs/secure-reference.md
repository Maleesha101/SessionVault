# Secure Reference Implementation Notes

Each vulnerability in SessionVault has a corresponding secure implementation. This document describes how to enable secure mode and what behavior to expect.

## Enabling Secure Mode

Set `LAB_MODE=secure` in the `.env` file, or set each `VULN_*` variable to `false`.

```bash
VULN_PREDICTABLE_SESSION=false
VULN_SESSION_FIXATION=false
VULN_NO_SESSION_ROTATION=false
VULN_MISSING_HTTPONLY=false
VULN_MISSING_SECURE=false
VULN_WEAK_SAMESITE=false
VULN_LOGOUT_NOT_INVALIDATE=false
VULN_PASSWORD_CHANGE_INVALIDATION=false
VULN_ACCOUNT_DISABLE_INVALIDATION=false
```

## Secure Behavior Summary

| Vulnerability | Secure Behavior |
|---------------|----------------|
| SM-01 | Random session IDs (`sess_` + 32 chars) |
| SM-02 | New session ID created after login |
| SM-03 | Session ID rotated after authentication |
| SM-04 | Cookie has `HttpOnly` flag |
| SM-05 | Cookie has `Secure` flag |
| SM-06 | Cookie uses `SameSite=Strict` |
| SM-07 | Shorter session lifetime |
| SM-08 | Server-side session deleted on logout |
| SM-09 | No token acceptance in URL |
| SM-10 | Concurrent sessions capped |
| SM-11 | All sessions invalidated after password change |
| SM-12 | Sessions invalidated when account is disabled |

## Verification Checklist

- [ ] All `Set-Cookie` headers include `HttpOnly`, `Secure`, and `SameSite=Strict`.
- [ ] Session IDs are cryptographically random.
- [ ] No session is reused after login.
- [ ] Logout deletes the server-side session.
- [ ] Password change invalidates all sessions.
- [ ] Account disable invalidates all sessions.
