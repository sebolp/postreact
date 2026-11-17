# How to import legacy steve/postreactions data into PostReaction

This guide covers only the actual import (`steve-import-core.php` +
`import-steve-reactions.php` for CLI, or `import-steve-reactions-web.php`
for browser). It does not cover the test-data generator scripts
(`seed-legacy-test-data.php` / `seed-legacy-test-users.php`) - those are
for trying the importer out safely before running it for real, and are
not needed on a real board with real legacy data.

## 0. Before you start

- You need three things in hand: your phpBB **root folder path** (where
  `common.php` lives), an existing **install of the PostReaction
  extension** already active, and the **legacy steve/postreactions
  data** still sitting in your database (even if the old extension
  itself is long uninstalled - only the database table needs to still
  exist).
- The import is designed to be **safe to run more than once**: it never
  overwrites a reaction a user already has, and it recognises icons it
  already created. Still, follow step 1.

## 1. Back up first - always

1. Back up your **database** (a full SQL dump, e.g. via phpMyAdmin
   "Export", or `mysqldump`). This is the step that actually protects
   you - everything else in this guide is secondary to having a backup
   you can restore from.
2. If you're not fully sure the legacy table's name/columns match what
   this importer expects, also export just that one table separately so
   you can inspect or restore it in isolation.
3. Only continue once the backup has finished and you've confirmed the
   file is not empty/corrupted.

## 2. Choose CLI or web

Use this decision, in order:

- **Do you have terminal/SSH access to the server (or a hosting panel
  with a "Cron Job" / "Scheduled Task" feature that can run a PHP file
  directly, e.g. cPanel)?** → Use the **CLI version**. It's simpler,
  has no timeout concerns, and doesn't need to be deleted afterwards for
  security (a script only runnable from the command line can't be
  triggered by visiting a URL).
- **Only FTP/file manager and a browser?** → Use the **web version**.
  It works the same way, just through a browser page instead of a
  terminal, with a login check built in.

Both versions share the same logic and produce the same result - pick
whichever you can actually run.

## 3A. Running the CLI version

1. Copy **both** `import-steve-reactions.php` and `steve-import-core.php`
   into your phpBB root folder (next to `common.php`).
2. Open a terminal in that folder and run a dry run first:
   ```
   php import-steve-reactions.php --dry-run
   ```
3. Read the report (see "Reading the report" below).
4. If it looks right, run it for real:
   ```
   php import-steve-reactions.php
   ```
5. If the legacy table wasn't found automatically, pass its name
   explicitly:
   ```
   php import-steve-reactions.php --old-table=your_table_name --dry-run
   ```
6. When you're done, you can delete both files - they're not part of
   the extension and aren't needed again unless you want to re-run the
   import later.

### No SSH/terminal, but your hosting panel has "Cron Jobs"?

You can still use the CLI version - this is the best option for a large
legacy table, since it fully bypasses every web-related timeout (PHP's
own limit *and* the web server/proxy's limit), because a cron job runs
the PHP binary directly and never goes through the web server at all.

1. Copy the two files into your phpBB root as in step 1 above.
2. In your hosting panel, find **Cron Jobs** (cPanel) or **Scheduled
   Tasks** (Plesk) - almost every shared-hosting panel has one of these,
   even without SSH access.
3. Create a new job with a command along these lines (adjust the path to
   where your phpBB is actually installed - the panel often shows you
   the correct base path, or shows a dropdown to pick the PHP version
   instead of writing "php" literally):
   ```
   php /home/yourusername/public_html/import-steve-reactions.php --dry-run
   ```
4. Set it to run **once**, a couple of minutes from now (most panels
   let you do this instead of a recurring schedule - if yours only
   offers recurring schedules, pick the shortest interval, let it run
   once, then immediately delete/disable the job so it doesn't keep
   running).
5. Cron jobs don't show you a live console - instead, the panel usually
   **emails you the command's output** (make sure an email address is
   set on the job). That email is exactly the same report you'd see in
   a real terminal.
6. Once the dry run report looks right, edit the job's command to drop
   `--dry-run` and run it once more the same way, for the real import.
7. Delete the cron job afterwards, and delete the two files from the
   server once you're done - same as the plain CLI steps above.

## 3B. Running the web version

1. Copy **both** `import-steve-reactions-web.php` and
   `steve-import-core.php` into your phpBB root folder (next to
   `common.php`).
2. Log into the forum in your browser **as an administrator**.
3. Visit the script in your browser, e.g.
   `https://yourforum.com/import-steve-reactions-web.php`.
   - If you're not recognised as an admin, it will tell you to log in
     first - log in, then reload the page.
4. Optionally fill in the legacy table name if you already know it,
   otherwise leave it blank (auto-detect).
5. Click **Preview (dry run)** and read the report (see below).
6. If it looks right, click the **Confirm** button that appears - this
   is the only step that actually writes to the database.
7. **Delete `import-steve-reactions-web.php` from the server right
   after you're done** (FTP/file manager, or your hosting's file
   manager). It's gated behind admin login and a one-time security
   token, but a script that can write to your database shouldn't stay
   reachable by URL indefinitely. `steve-import-core.php` alone is
   harmless if left behind (it does nothing when visited directly), but
   there's no reason to leave either file there once you're finished.

## 4. Reading the report (both versions show the same information)

- **Legacy table** - which table it found/used. If this looks wrong,
  stop and re-run with the table name given explicitly.
- **Icon mapping** - lines about icons it matched or created. The first
  time you run this on a given board, you'll see 3 new "placeholder"
  icons created (Dislike, Neutral, Expressionless) - these are created
  **disabled**, with no image yet. That's expected. After the import,
  go to **ACP → PostReaction → Icons**, upload an image for each of
  the 3, and enable them whenever you're ready. Until then, reactions
  using those icons are stored correctly but display without an image.
- **Legacy rows / unique pairs / duplicates dropped** - how much data
  was found, and how many entries were consolidated because the old
  extension allowed a user to leave more than one reaction on the same
  post (yours allows only one, so only the most recent is kept).
- **Skipped, already exists** - reactions the importer left alone
  because that user already has a reaction on that post in your live
  system. Nothing is overwritten, ever.
- **Skipped, unmapped filenames** - reactions using an emoji filename
  the importer doesn't recognise (not part of the original 11-emoji
  set). These are simply not imported; nothing breaks.
- **N reaction(s) ready to import / inserted** - the actual count that
  will be (or was) written.

If the numbers in the dry run look reasonable, it's safe to confirm.

## 5. Large databases and timeouts

The importer reads the whole legacy table into memory once, then does a
single bulk insert - there's no batching/chunking. On a typical board
(up to a few tens of thousands of legacy reactions) this finishes in
seconds and none of this section matters. For a much larger legacy
table, here's what to expect and what to do:

- **CLI**: essentially no timeout risk. PHP's command-line mode has no
  execution time limit by default, and the scripts also explicitly ask
  for unlimited time and more memory as a safety net. If a very large
  import still runs out of memory on a heavily restricted server, that
  will show as a plain PHP "Allowed memory size exhausted" error - in
  that case, ask your host to temporarily raise the CLI `memory_limit`,
  or run it from a machine/VPS with fewer restrictions if you have one.
- **Web**: this is where timeouts are actually a risk, for two separate
  reasons:
  1. **PHP's own execution time limit** - the script already tries to
     lift this for you, but some hosts lock it down and ignore the
     request.
  2. **The web server or hosting platform's own hard timeout** (e.g.
     Apache/nginx/reverse proxy, or a shared-hosting platform cap) -
     this is outside PHP's control entirely, and no setting inside the
     script can work around it.
  - If the page just hangs and eventually shows a blank page, a "504
    Gateway Timeout", or "Error" with no report at all, that's this
    kind of timeout.
  - **What to do:** first choice is always to switch to the CLI version
    if there's any way to get to it (many hosting panels, e.g. cPanel,
    can schedule a one-off "Cron Job" that runs
    `php /full/path/to/import-steve-reactions.php` even without giving
    you a terminal - check if yours has this). If CLI truly isn't an
    option at all, ask your host to temporarily raise the PHP execution
    time limit and any web server timeout for your account, run the
    import, then it's safe to have them put the limits back.
  - As a rule of thumb: if your legacy table has well over ~50,000
    rows and you only have the web version available, plan for a
    timeout and look into the CLI/cron option above before spending
    time troubleshooting the web version further.

## 6. After a successful import

1. Go to **ACP → PostReaction → Icons** and check whether any
   placeholder icons (Dislike / Neutral / Expressionless) were created.
   If so, upload an image for each and enable the ones you want visible
   for new reactions - the historical reactions already display
   correctly regardless.
2. Spot-check a handful of posts that you know had reactions on the old
   extension, and confirm they show up correctly with the right counts.
3. If you ran the web version, delete it from the server (see step 3B.7
   above) if you haven't already.
4. You're done - the import is safe to leave as-is, and safe to run
   again later (e.g. after finding more old data) without duplicating
   anything already imported.
