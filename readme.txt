=== Hook Fuse ===
Contributors: orcnd
Tags: fatal error, error handling, error log, debugging, troubleshooting
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Stops a crash inside another plugin's hook from taking your whole page down. The error is logged and reported once a week instead.

== Description ==

**One plugin throws an error, and the visitor gets "There has been a critical error on this website" instead of your page.** Hook Fuse keeps the page loading and writes the error to a log.

You pick the plugins that cause trouble. When one of them throws an error inside a hook (a form submission, a checkout step, a tracking call), Hook Fuse stops that one callback and the rest of the page runs as usual.

= What you get =

* **Pages that finish loading.** A failed action ends, a failed filter passes its value on unchanged. The protected plugin's files are never changed.
* **A log you can act on.** Hook, callback, error message, file and line, how many times and when, under Tools > Hook Fuse. Enough to file a bug report with the plugin's author.
* **One email a week at most.** Sent only when something new was caught, and you can switch it off.
* **A short notice** on the Dashboard and Plugins screens when there are new errors. It goes away when you open the log or dismiss it.

= How it works =

1. Tick the plugins to protect under Tools > Hook Fuse.
2. Just before a hook runs, Hook Fuse wraps the callbacks of those plugins in memory.
3. A callback throws. Hook Fuse catches the error, logs it, and WordPress goes on to the next callback.
4. You read the log, report the bug, and untick the plugin once it is fixed.

= A real case: Meta pixel and WPForms =

The WPForms integration of Meta pixel for WordPress throws a TypeError when a form has an email confirmation field. The visitor presses Send and gets the critical error page. With Hook Fuse the form is submitted, the confirmation message shows and the error is in the log. The first screenshot is that form, without and with Hook Fuse.

If the Meta pixel plugin is installed when you first activate Hook Fuse, it is protected right away.

= What it cannot catch =

* Errors while a plugin's files are being loaded, before any hook runs.
* Code that runs outside hooks.
* Real fatal errors that PHP cannot catch, such as running out of memory or a syntax error.

Hook Fuse hides errors from visitors, not from you. It is a stopgap until the plugin's author fixes the bug, so please report the errors you see in the log to them.

= Privacy =

Hook Fuse makes no HTTP requests to any outside service and has no tracking. The log is stored in your own database.

The one thing that leaves your site is the weekly email. It is on by default and is sent only in a week when new errors were caught, to the site's admin email address (Settings > General). It goes out through your site's own mail setup (`wp_mail`), not through any service run by Hook Fuse. It contains the site name, and for each new error: the protected plugin's folder name, the hook name, the error message, the file path relative to wp-content and the line number, and how many times it happened. To stop it, untick "Weekly email" under Tools > Hook Fuse.

== Installation ==

1. Install and activate Hook Fuse from the Plugins screen.
2. Open Tools > Hook Fuse and tick the plugins you want to protect.
3. The weekly email to the site's admin address is on by default. Untick "Weekly email" on the same page if you do not want it.
4. Check the log after plugin updates.

== Frequently Asked Questions ==

= Does it slow my site down? =

It looks at each hook once per request, just before the hook runs, and only wraps callbacks that belong to a protected plugin. With no plugin protected it does nothing at all.

= Will it hide errors I need to know about? =

Every caught error is logged with its file and line. With WP_DEBUG on, each one is also written to the PHP error log.

= Can I protect every plugin? =

You can, but it is better to protect only the plugins that cause trouble. A plugin whose hook failed may have done only part of its work.

= Does it change the protected plugin? =

No. It wraps the plugin's hook callbacks in memory while the page loads. Deactivating Hook Fuse puts everything back.

== Screenshots ==

1. The same form, sent by a visitor: without Hook Fuse and with Hook Fuse.
2. The notice on the Dashboard when something new was caught.
3. Tools > Hook Fuse: the caught errors and the plugins to protect. The two "Example" plugins are demo plugins with a bug.

== Changelog ==

= 0.1.2 =
* The hidden security fields of the two forms on the Hook Fuse page no longer share the same id.

= 0.1.1 =
* The error log fits narrow screens: long hook, callback and file names now break onto a new line, and on phones each value is shown under its column name.
* The "Clear the log" and "Save Changes" buttons no longer share the same id.

= 0.1.0 =
* First release: protected plugins list, error log, weekly email, admin notice.
