# Design of Hook Fuse

## In the product

- The admin page uses WordPress' own elements only: `widefat striped` table, `form-table`, `submit_button`, core
  notices. No custom colors, no own stylesheet file; the few rules are inline on the Hook Fuse page alone.
- The log must fit every width: hook, callback and file names may break anywhere; at 600 px and below each value is
  stacked under its column name (`data-colname`).
- One short notice, only on the Dashboard and Plugins screens, only when something new was caught.
- Nothing is ever shown to visitors.

## Listing art

Concept: the fuse panel of a car. Each slot is a hook, each blade fuse a plugin. The blown fuse's element is broken
so that what is left has the shape of a hook; that is the icon.

| Part | Value |
|---|---|
| Ground | yellow `#f6c21c` (the 20 A fuse color), text `#17191c` |
| Panel | `#2a2e33`, sockets `#1b1e22`, label `#f3f1ea`, blown label `#d8392b` |
| Fuses | real ISO colors and amps: 10 red `#d8392b`, 15 blue `#2f6fc4`, 20 yellow, 30 green `#3f9a53` |
| Type | DIN Condensed for the name and hook labels, DIN Alternate for text (macOS system fonts) |
| Line | "One plugin throws an error. The rest of the page still loads." |

All vector, drawn in `art/art.html`, rendered with `art/render.cjs`. Stays out: generated images, gradients, glows,
stock icons, emoji, rounded pill badges.

## Screenshots

1. Without and with Hook Fuse side by side (`art/compare.html`, needs `art/raw/crash.png` and `art/raw/after.png`).
2. The Dashboard notice.
3. The log and the settings, last because an odd last screenshot is shown full width.

Taken at 1440 x 810 on a Playground site. Two demo plugins with a bug fill the log; they carry "Example" in their
name and the caption says they are demo plugins. The Meta pixel row is the real error.

## Readme text

Plain sentences, the problem first ("There has been a critical error on this website"), then what it does, how it
works, a real case, what it cannot catch, privacy. Never claim more than the code does.
