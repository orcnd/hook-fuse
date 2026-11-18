=== Hook Fuse ===
Contributors: orcnd
Tags: fatal error, error handling, white screen, debugging, stability
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Stops a crash inside another plugin's hook from taking your whole page down. The error is logged and reported once a week instead.

== Description ==

Some plugins throw an uncaught error from one of their hooks: a form submission, a checkout step, a tracking call. PHP then stops the whole request. Visitors see "There has been a critical error on this website" and you get a "Your Site is Experiencing a Technical Issue" email.

Hook Fuse sits between WordPress and the plugins you choose. When a protected plugin's hook callback throws, Hook Fuse catches the error, writes it to a log and lets the rest of the page run. A filter passes its value on unchanged; an action just ends. The protected plugin's files are never changed.

**What you get**

* A list of the plugins to protect, under Tools > Hook Fuse.
* A log of caught errors: hook, callback, error message, file and line, how many times and when.
* One email a week to the site's admin address when something new was caught (can be switched off).
* A short notice on the Dashboard and Plugins screens when there are new errors. It goes away when you open the log or dismiss it.

**Ready for Meta pixel for WordPress.** If the Meta pixel plugin is installed when you first activate Hook Fuse, it is protected right away. Its WPForms integration throws a TypeError when a form has an email confirmation field, which stops the form submission with a fatal error. With Hook Fuse the submission completes and the error is logged.

**What it cannot catch**

* Errors while a plugin's files are being loaded, before any hook runs.
* Code that runs outside hooks.
* Real fatal errors that PHP cannot catch, such as running out of memory or a syntax error.

Hook Fuse hides errors from visitors, not from you. It is a stopgap until the plugin's author fixes the bug, so please report the errors you see in the log to them.

**Privacy.** Hook Fuse makes no external requests and sends no data anywhere. The log is stored in your own database and the weekly email goes through your site's own mail settings.

== Installation ==

1. Install and activate Hook Fuse from the Plugins screen.
2. Open Tools > Hook Fuse and tick the plugins you want to protect.
3. Check the log after plugin updates.

== Frequently Asked Questions ==

= Does it slow my site down? =

It looks at each hook once per request, just before the hook runs, and only wraps callbacks that belong to a protected plugin. With no plugin protected it does nothing at all.

= Will it hide errors I need to know about? =

Every caught error is logged with its file and line. With WP_DEBUG on, each one is also written to the PHP error log.

= Can I protect every plugin? =

You can, but it is better to protect only the plugins that cause trouble. A plugin whose hook failed may have done only part of its work.

= Does it change the protected plugin? =

No. It wraps the plugin's hook callbacks in memory while the page loads. Deactivating Hook Fuse puts everything back.

== Changelog ==

= 0.1.0 =
* First release: protected plugins list, error log, weekly email, admin notice.
