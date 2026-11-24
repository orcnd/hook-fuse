# Checklist: 0.1.2

Close an item only with evidence (URL, what was visible, which check was clean).
Run on 2026-11-24: Playground CLI 3.1.60, WordPress latest, PHP 8.2 (`verify/blueprint.json`), Meta pixel for
WordPress, WPForms Lite and Plugin Check from WordPress.org, plus two demo plugins with a bug (not in the repo).
Only the nonce field of the "Clear the log" form changed since 0.1.1.

| # | Item | Page / command | Status | Evidence |
|---|---|---|---|---|
| 1 | Crash reproduces without Hook Fuse | /contact/, submit, logged out | done | Page text "There has been a critical error on this website." |
| 2 | With Hook Fuse the same submission completes | /contact/, submit 3 times | done | Page text "Thanks, your message was received." each time |
| 3 | Tools > Hook Fuse lists the caught errors | /wp-admin/tools.php?page=hook-fuse | done | 3 rows: the Meta pixel `TypeError` on `send_server_events` and one per demo plugin |
| 4 | No duplicate ids on the page | same page, log not empty, whole document | done | None. Before: `_wpnonce` twice |
| 5 | Clearing the log needs its own nonce | POST /wp-admin/admin-post.php, log with 3 rows | done | No nonce, wrong nonce, the save form's nonce, the right nonce in the old field: 403, 3 rows left. Logged out: 400, 3 rows left |
| 6 | Saving does not accept the clear nonce | same | done | 403 |
| 7 | Both buttons work | Tools > Hook Fuse | done | Save with the weekly email off: "Settings saved.", email off and 3 plugins kept, 3 rows left. Clear: "Nothing caught yet." No console errors |
| 8 | The log table fits narrow screens | 390 to 1440 px, 9 widths | done | No sideways page scroll at any width |
| 9 | Plugin Check: no errors | Tools > Plugin Check, Hook Fuse, all categories, errors and warnings | done | "Checks complete. No errors found." on a fresh site |
| 10 | readme.txt passes the readme validator | https://wordpress.org/plugins/developers/readme-validator/ | done | No errors or warnings (5110 bytes). Notes: no Upgrade Notice, no donate link |
| 11 | Zip builds with only plugin files | `bin/build-zip.sh` | done | 8 files. `php -l` ran in the build |
| 12 | Dashboard notice; uninstall | see 0.1.1, items 4a, 4b and 12 | not repeated | Code did not change since 0.1.1 |
| 13 | Released | https://wordpress.org/plugins/hook-fuse/ | open | |

# Checklist: 0.1.1

Close an item only with evidence (URL, what was visible, which check was clean).
Run on 2026-11-24: Playground CLI 3.1.60, WordPress 7.1.5, PHP 8.2 (`verify/blueprint.json`), Meta pixel for
WordPress, WPForms Lite and Plugin Check from WordPress.org, plus two demo plugins with a bug (not in the repo).

| # | Item | Page / command | Status | Evidence |
|---|---|---|---|---|
| 1 | Crash reproduces without Hook Fuse | /contact/, submit, logged out | done | Page text "There has been a critical error on this website." |
| 2 | With Hook Fuse the same submission completes | /contact/, submit 3 times | done | Page text "Thanks, your message was received." each time |
| 3 | Tools > Hook Fuse lists the caught errors | /wp-admin/tools.php?page=hook-fuse | done | 3 rows: the Meta pixel `TypeError` on `send_server_events` and one per demo plugin (`.wordpress-org/screenshot-3.png`) |
| 4a | Dashboard notice appears | /wp-admin/ | done | "Hook Fuse caught 3 new errors in protected plugins. Your pages kept working." (`.wordpress-org/screenshot-2.png`) |
| 4b | Dashboard notice goes away after opening the page | /wp-admin/, /wp-admin/plugins.php | done | Published 0.1.1 zip. Before: "Hook Fuse caught 2 new errors in protected plugins. Your pages kept working." on both screens. After opening Tools > Hook Fuse (2 rows): no Hook Fuse notice on either. A new error brought it back; "Dismiss" hid it again |
| 5 | Meta pixel ticked on first activation; settings save | /wp-admin/tools.php?page=hook-fuse | done | First activation: only `official-facebook-pixel` ticked. Save: "Settings saved.", log still there. Save ran just before the phone layout (CSS and `data-colname` only) was added. Switching the weekly email off was not repeated |
| 6 | The log table fits narrow screens | same page at 390, 600, 782, 900, 1024, 1200, 1280, 1366 and 1440 px | done | No sideways page scroll at any width. In 0.1.0: 272 px at 1024, 96 at 1200, 16 at 1280 |
| 7 | "Clear the log" and "Save Changes" have different ids; clear works | same page | done | `hook-fuse-clear` and `submit`. After clear: "Nothing caught yet." |
| 8 | No duplicate ids on the page | same page, log not empty | open | `_wpnonce` appears twice (hidden fields of the two forms), as in 0.1.0. Fixed in 0.1.2 |
| 9 | Plugin Check: no errors | Tools > Plugin Check, Hook Fuse, all categories, errors and warnings | done | "Checks complete. No errors found." |
| 10 | readme.txt passes the readme validator | https://wordpress.org/plugins/developers/readme-validator/ | done | No errors or warnings (5002 bytes). Notes: no Upgrade Notice, no donate link |
| 11 | Zip builds with only plugin files | `bin/build-zip.sh` | done | 8 files. `php -l` ran in the build |
| 12 | Uninstall removes stored data | Plugins > Delete, database read before and after | done | Published 0.1.1 zip, installed by upload. Before: options `hook_fuse_settings`, `hook_fuse_log`, `hook_fuse_digest_sent`, user meta `hook_fuse_seen`, cron `hook_fuse_weekly_digest`. After deactivating: cron gone, the rest kept. After "Hook Fuse was successfully deleted.": no options, no user meta, no cron, no folder. The mail was faked so the digest option exists |
| 13 | Released | https://wordpress.org/plugins/hook-fuse/ | done | SVN r3821277, GitHub e411149. API version 0.1.1; the published 0.1.1 zip is identical to the local build; page shows the banner, icon and 3 screenshots at 1280 px |

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
| 7 | readme.txt passes the readme validator | https://wordpress.org/plugins/developers/readme-validator/ | done | 2026-11-24, the released 0.1.0 readme (3999 bytes): no errors or warnings. Notes: tags `stability` and `white screen` not widely used, no Upgrade Notice, no Screenshots, no donate link |
| 8 | `php -l` clean on every file; zip builds with only plugin files | `bin/build-zip.sh` | done | 8 files: hook-fuse.php, uninstall.php, readme.txt, 5 files in includes/ |
| 9 | Uninstall removes stored data | delete plugin, /wp-admin/options.php | done | Before: `hook_fuse_settings` listed. After "Yes, delete these files and data": no `hook_fuse_` options. User meta and cron cleanup are in uninstall.php but were not inspected |
