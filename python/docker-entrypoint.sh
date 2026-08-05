#! /bin/bash
set -e

ME=$(basename $0)

echo "[$ME] loading env from secrets"
for f in $(find /run/secrets/toenv -type f)
do
    export $(basename $f)=$(cat $f)
done

exec "$@"
