#!/bin/sh
#
# This script needs `sed`.
#
set -e

CUR_DIR=$(dirname "$0")

mkdir -p "$CUR_DIR/build"
sed -n '/^## /{p;:a;n;/^## /q;p;ba}' "$CUR_DIR/CHANGELOG.md"  >"$CUR_DIR/build/relinfo.md"
