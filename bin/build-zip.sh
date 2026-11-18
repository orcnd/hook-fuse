#!/bin/sh
# Builds dist/hook-fuse.zip with only the files the plugin needs (no verify/, checklist or build scripts).
set -eu
cd "$(dirname "$0")/.."
for f in hook-fuse.php uninstall.php includes/*.php; do php -l "$f" >/dev/null; done
rm -rf dist && mkdir -p dist/hook-fuse/includes
cp hook-fuse.php uninstall.php readme.txt dist/hook-fuse/
cp includes/*.php dist/hook-fuse/includes/
(cd dist && zip -qr hook-fuse.zip hook-fuse)
echo "dist/hook-fuse.zip"
unzip -l dist/hook-fuse.zip | tail -n +4 | sed '$d' | sed '$d'
