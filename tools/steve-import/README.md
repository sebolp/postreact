# steve/postreactions -> PostReaction importer

Standalone tool to import legacy reaction data from the old, defunct
**steve/postreactions** extension into **PostReaction** (2.6.1+).

**Not part of the PostReaction extension itself** - do not copy these
files into `ext/sebo/postreact`. They go in your **phpBB root folder**
(next to `common.php`) instead.

⚠️ **Early stage / not tested against the real steve/postreactions
extension** - the mapping was reverse-engineered from a third-party
port, since the original extension is no longer downloadable. It's
based on the best information available, but please **back up your
database before running it**, and report any bugs or mismatches you
find.

## Files

| File | Needed for |
|---|---|
| `steve-import-core.php` | Always - shared logic, required by both versions below |
| `import-steve-reactions.php` | CLI version (terminal / SSH / cron job) |
| `import-steve-reactions-web.php` | Web version (browser, admin login required) |
| `HOW-TO-IMPORT-REACTIONS.md` | Full step-by-step instructions - **read this first** |

## Quick start

1. Back up your database.
2. Copy `steve-import-core.php` + **one** of the two front-ends into
   your phpBB root folder, depending on whether you have CLI/SSH/cron
   access or only a browser.
3. CLI: `php import-steve-reactions.php --dry-run`
   Web: visit `import-steve-reactions-web.php` while logged in as admin.
4. Check the report, then run again without `--dry-run` (CLI) or click
   Confirm (web) to actually import.
5. **If you used the web version, delete `import-steve-reactions-web.php`
   from your server once you're done.** It's protected by admin login
   and a one-time token, but a script that can write to your database
   shouldn't stay reachable by URL indefinitely.

Full details, large-database/timeout handling, and troubleshooting: see
[`HOW-TO-IMPORT-REACTIONS.md`](./HOW-TO-IMPORT-REACTIONS.md).
