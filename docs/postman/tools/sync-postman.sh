#!/usr/bin/env bash
#
# Mirrors the curated v2.1 collections into postman/collections (Postman CLI
# git-native format) and previews the workspace diff. Pushes only with --push.
#
# Usage: bash docs/postman/tools/sync-postman.sh [--push]
# Requires: postman login

set -euo pipefail

cd "$(dirname "$0")/../../.."

for collectionSet in Customer Vendor Delivery; do
    lowercaseSet=$(echo "$collectionSet" | tr '[:upper:]' '[:lower:]')
    mirrorPath="postman/collections/Seahum - ${collectionSet} API"

    rm -rf "$mirrorPath"
    postman collection migrate "docs/postman/collections/seahum-${lowercaseSet}.postman_collection.json" -o "$mirrorPath" > /dev/null
    postman collection lint "$mirrorPath"
done

postman workspace diff --push-strategy default

if [[ "${1:-}" == "--push" ]]; then
    postman workspace push --push-strategy default
fi
