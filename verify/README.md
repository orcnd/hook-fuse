# Verification on WordPress Playground

Runs a throwaway WordPress (PHP 8.2, SQLite) with Meta pixel for WordPress, WPForms Lite and Plugin Check
from WordPress.org, plus the built plugin (`dist/hook-fuse`, the same files as the zip) mounted as `hook-fuse`. Nothing is installed on the machine.

```bash
bin/build-zip.sh
npx @wp-playground/cli@3.1.60 server \
  --mount=dist/hook-fuse:/wordpress/wp-content/plugins/hook-fuse \
  --mount=verify:/wordpress/verify \
  --blueprint=verify/blueprint.json --port=9400
```

Hook Fuse is mounted but not active, so the first pass shows the crash.

1. In a private window (logged out; Meta skips tracking for editors), open http://127.0.0.1:9400/contact/,
   fill in a name and the same email twice, submit. Expected without Hook Fuse: "There has been a critical
   error on this website."
2. Log in at /wp-login.php (admin / password), activate Hook Fuse. Tools > Hook Fuse should show Meta pixel
   for WordPress already ticked.
3. Submit the form again logged out. Expected: "Thanks, your message was received."
4. Tools > Hook Fuse lists `TypeError: strlen(): Argument #1 ($string) must be of type string, array given`
   on hook `send_server_events`, callback `FacebookPixelPlugin\Core\ServerEventAsyncTask::launch`
   (the innermost protected callback; it runs inside WPForms' `wpforms_process_before`), count 1.
5. Dashboard: the notice shows once; it is gone after opening Tools > Hook Fuse.
6. Tools > Plugin Check, pick Hook Fuse, run: no errors.
