# SessionVault Security Lab Guide

This document contains the detailed vulnerability explanations, testing procedures, and remediation guidance for SessionVault.

## Overview

SessionVault is a deliberately vulnerable session-management training application. Each vulnerability is isolated, documented, and paired with a secure reference implementation.

## Safety Notice

Only test against systems you own or have explicit permission to test. SessionVault is designed for local use only.

## Vulnerability Catalog

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

## Testing Methodology

All testing should be performed locally against the SessionVault instance. Use Burp Suite to intercept, inspect, and replay HTTP requests.

## Secure Reference Implementation

Each vulnerable behavior has a corresponding secure implementation that can be enabled via configuration. See the individual vulnerability sections for details.

---

Detailed per-vulnerability guides are provided in the full documentation.
