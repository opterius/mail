# Real-time New Mail Detection

## How Roundcube handles it

Roundcube uses **periodic AJAX polling** — plain `setInterval` calling a lightweight endpoint
every N seconds (default: 60s, configurable). The endpoint runs
`STATUS INBOX (MESSAGES UNSEEN UIDNEXT)` against IMAP, which is a single-line command that
returns counts without fetching any message data. If new mail is detected, it updates the
unread badge and optionally fires a browser notification.

---

## Options

### 1. AJAX polling (Roundcube's approach) — recommended for now

```
Browser → GET /check-new every 30s → IMAP STATUS → { unseen: 3, uidnext: 42 } → update badge
```

**Implementation sketch**
- `GET /api/check-new` — runs `STATUS INBOX (UNSEEN UIDNEXT)`, returns JSON
- Small Alpine.js / vanilla JS snippet in the app layout polls every 30–60 s
- On change, update the sidebar unread badge in-place (no page reload)

**Pro:** trivial to implement, stateless, works on shared hosting  
**Con:** up to N seconds of latency; wastes a PHP worker + IMAP auth handshake on every tick even when inbox is quiet

---

### 2. Server-Sent Events (SSE)

```
Browser → GET /stream  (persistent HTTP connection)
Server  → loops: sleep → IMAP STATUS → if changed, write "data: {...}\n\n"
```

**Pro:** server-initiated; browser reconnects automatically; no extra library  
**Con:** ties up a PHP worker per user for the entire session; doesn't scale past a few dozen concurrent users without async PHP (Swoole / ReactPHP)

---

### 3. IMAP IDLE + WebSockets/SSE via a queue worker (proper real-time)

```
Queue worker  : IMAP IDLE (RFC 2177) — server pushes EXISTS when mail arrives
Worker        → publishes to Redis channel
Browser       ← SSE / WebSocket ← Laravel Reverb / Soketi subscribes to channel
```

**Pro:** true push; sub-second delivery; IMAP connection only wakes when the server says to  
**Con:** requires a persistent background worker per IMAP account + a WebSocket server (Laravel Reverb, Soketi); significant infrastructure

---

### 4. Hybrid: SSE + IMAP IDLE in one process

A long-running PHP process opens IMAP IDLE, which blocks waiting for server `EXISTS`
notifications, then writes SSE frames directly. Works well with Swoole or PHP Fibers.
Complex to operate.

---

## Recommended path for Opterius Mail

| Phase | Approach | Notes |
|-------|----------|-------|
| Near-term | **AJAX polling** | ~20 lines of code, matches Roundcube behavior |
| Later | **SSE + IMAP IDLE worker** | Swap polling endpoint for EventSource; browser-side code barely changes |

The polling endpoint should store the last known `UIDNEXT` in the user's session so it can
detect genuinely new messages rather than just checking unseen count (which could decrease
if the user reads mail in another client).
