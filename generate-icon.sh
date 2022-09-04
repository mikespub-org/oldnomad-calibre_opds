#!/bin/sh
#
# This requires:
# - librsvg2-bin (for `rsvg-convert`);
# - netpbm (for `pngtopam` and `pamtowinicon`).
#
set -e

CUR_DIR=$(dirname "$0")
SIZES="256 128 64 32 16"

cd "$CUR_DIR"
for sz in $SIZES
do
    rsvg-convert -f png -w "$sz" -h "$sz" img/icon.svg | pngtopam --alphapam
done | pamtowinicon >img/icon.ico
