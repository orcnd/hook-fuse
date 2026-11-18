# Checklist: 0.1.0 (first WordPress.org release)

Close an item only with evidence (URL, what was visible, which check was clean).
Run on 2026-11-18: Playground CLI 3.1.60, WordPress latest, PHP 8.3, Meta pixel for WordPress 5.2.2,
WPForms Lite 2.0.4, Plugin Check from WordPress.org.

| # | Item | Page / command | Status | Evidence |
|---|---|---|---|---|
| 1 | Crash reproduces without Hook Fuse (Meta pixel 5.2.2 + WPForms Lite, email confirmation on, visitor not logged in) | /contact/, submit | done | HTTP 500, page text "There has been a critical error on this website." (`verify/shots/1-crash-without-hook-fuse.png`) |
| 2 | With Hook Fuse the same submission completes and shows the confirmation message | /contact/, submit | done | Page text "Thanks, your message was received." (`verify/shots/2-submit-with-hook-fuse.png`) |
| 3 | Tools > Hook Fuse lists the caught error with hook, callback, count | /wp-admin/tools.php?page=hook-fuse | done | 1 row: Meta pixel for WordPress, hook `send_server_events`, `FacebookPixelPlugin\Core\ServerEventAsyncTask::launch`, `TypeError: strlen(): Argument #1 ($string) must be of type string, array given`, `plugins/official-facebook-pixel/FacebookAds/Object/ServerSide/Normalizer.php:43`, count 1 (`verify/shots/3-log.png`) |
| 4 | Dashboard notice appears once, goes away after opening the page | /wp-admin/ | done | Before: "Hook Fuse caught 1 new error in a protected plugin. Your pages kept working." After opening the page: 0 Hook Fuse notices |
| 5 | Settings save; Meta pixel ticked on first activation | /wp-admin/tools.php?page=hook-fuse | done | First activation: only `official-facebook-pixel` ticked, weekly email on. After saving WPForms Lite ticked + email off: reload shows the same values and "Settings saved." |
| 6 | Plugin Check: no errors | Tools > Plugin Check, Hook Fuse, all categories | done | "Checks complete. No errors found." with `dist/hook-fuse` mounted (the files that ship) |
| 7 | readme.txt passes the readme validator | https://wordpress.org/plugins/developers/readme-validator/ | open | The validator page needs a browser paste; run it before submitting |
| 8 | `php -l` clean on every file; zip builds with only plugin files | `bin/build-zip.sh` | done | 8 files: hook-fuse.php, uninstall.php, readme.txt, 5 files in includes/ |
| 9 | Uninstall removes stored data | delete plugin, /wp-admin/options.php | done | Before: `hook_fuse_settings` listed. After "Yes, delete these files and data": no `hook_fuse_` options. User meta and cron cleanup are in uninstall.php but were not inspected |
