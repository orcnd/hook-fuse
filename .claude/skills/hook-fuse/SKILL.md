---
name: hook-fuse
description: Use when working on Hook Fuse in any way - changing its code or readme, fixing a bug, adding a feature, testing it, drawing its banner, icon or screenshots, or releasing it to WordPress.org and GitHub.
---

# Hook Fuse

A WordPress plugin that wraps the hook callbacks of plugins the site owner chooses, so an uncaught error in one of them is logged instead of ending the request.

## Files next to this one

| File | Read it when |
|---|---|
| `working-rules.md` | Always (loaded through CLAUDE.md): approvals, commits, text, verification |
| `status.md` | Always (loaded through CLAUDE.md): where things stand, what comes next |
| `design.md` | Before touching the UI, the readme text or any image |
| `wordpress-org.md` | Before a release or anything that goes to WordPress.org |

## Layout

| Path | Holds |
|---|---|
| `hook-fuse.php` | Plugin header and boot; wires the classes together |
| `includes/class-hook-fuse-guard.php` | Wraps protected callbacks through the `all` hook; the core |
| `includes/class-hook-fuse-log.php` | Caught errors, merged and capped at 50, written once at `shutdown` |
| `includes/class-hook-fuse-settings.php` | One non-autoloaded option; presets (Meta pixel) on first activation |
| `includes/class-hook-fuse-digest.php` | Weekly email through `wp_mail` to `admin_email` |
| `includes/class-hook-fuse-admin.php` | Tools > Hook Fuse page, its inline CSS, Dashboard and Plugins notice |
| `uninstall.php` | Deletes every option, user meta and cron event |
| `readme.txt` | Directory listing; must pass the readme validator |
| `verify/` | Playground blueprint and the steps that reproduce the Meta pixel and WPForms crash |
| `checklist.md` | One table per release, closed with evidence |
| `bin/build-zip.sh` | Builds `dist/hook-fuse.zip` with only the files the plugin needs |
| `bin/svn-stage.sh` | Prepares the SVN folder, never commits |

Prefix `hook_fuse` / `Hook_Fuse`, text domain `hook-fuse`.

## Decisions

- Callbacks are wrapped lazily, just before their hook runs, in memory. Protected plugins' files are never changed.
- A failed action ends; a failed filter passes its value on unchanged. The error is logged at the innermost protected
  callback.
- Nothing is protected until the owner ticks it, except the preset (Meta pixel) when it is installed at first
  activation. With no plugin protected the guard does nothing.
- File paths in the log and the email are relative to wp-content, so the server's layout is not shown.
- The weekly email is the only thing that leaves the site. readme.txt says so and lists its contents; a WordPress.org
  reviewer asked for this, so keep that paragraph's meaning.
- Meta may be named in the description as a plugin it works with, never in the plugin name or slug.
- The clear form has its own nonce field, `hook_fuse_clear_nonce`; the settings form keeps `_wpnonce`. Ids on the page
  stay unique (buttons `hook-fuse-clear` and `submit`).
- No unit tests; every change ends with the Playground pass below.

## Verification pass

1. Without Hook Fuse: the WPForms form with email confirmation, sent logged out, ends in "There has been a critical
   error on this website".
2. With it: the same submission shows "Thanks, your message was received."
3. Tools > Hook Fuse lists the `TypeError` on `send_server_events`, with the callback and a count.
4. The notice shows on the Dashboard and Plugins screens, goes away after opening the page, comes back for a new error.
5. Settings save; "Clear the log" clears; a missing, wrong or foreign nonce gives 403 and leaves the log.
6. No sideways scroll from 390 to 1440 px with a filled log; no duplicate ids.
7. Plugin Check clean with `dist/hook-fuse` mounted. For uninstall, install the zip by upload instead of mounting it:
   deleting a mounted plugin deletes the mounted folder.
