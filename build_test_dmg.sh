#!/bin/bash
set -euo pipefail

# Test distribution build without Apple Developer account.
# Creates an unsigned/ad-hoc signed DMG and runs a lightweight secret scan.

MARKETING_VERSION=${1:-"1.0"}
TEST_BUNDLE_ID=${3:-"com.markolenberg.Wordflow.test"}

# Auto-increment build number stored in .build_number (outside dist/ so it survives clean)
BUILD_COUNTER_FILE=".build_number"
if [[ -f "$BUILD_COUNTER_FILE" ]]; then
    LAST_BUILD=$(cat "$BUILD_COUNTER_FILE")
    PROJECT_VERSION=${2:-$((LAST_BUILD + 1))}
else
    PROJECT_VERSION=${2:-"1"}
fi
echo "$PROJECT_VERSION" > "$BUILD_COUNTER_FILE"
APP_NAME="Wordflow"
APP_BUNDLE="${APP_NAME}.app"
DIST_DIR="dist"
STAGING_DIR="${DIST_DIR}/dmg-staging"
DMG_NAME="${APP_NAME}-${MARKETING_VERSION}-early_access.dmg"
DMG_PATH="${DIST_DIR}/${DMG_NAME}"

# Patterns that indicate likely leaked secrets.
# Keep this focused on real token formats to avoid false positives.
SECRET_PATTERN='(xkeysib-|xsmtpsib-|gsk_[A-Za-z0-9_-]{10,}|sk-[A-Za-z0-9]{10,}|Bearer[[:space:]]+[A-Za-z0-9._-]{12,})'

echo "==> Building app (${MARKETING_VERSION} / ${PROJECT_VERSION})"
echo "==> Using test bundle id: ${TEST_BUNDLE_ID}"
rm -rf "${APP_BUNDLE}"
./build_app.sh "${MARKETING_VERSION}" "${PROJECT_VERSION}" "${TEST_BUNDLE_ID}"

if [[ ! -d "${APP_BUNDLE}" ]]; then
  echo "ERROR: ${APP_BUNDLE} was not produced."
  exit 1
fi

echo "==> Preparing clean DMG staging"
rm -rf "${DIST_DIR}" "${STAGING_DIR}"
mkdir -p "${STAGING_DIR}"
cp -R "${APP_BUNDLE}" "${STAGING_DIR}/"
ln -s /Applications "${STAGING_DIR}/Applications"

# Include setup guide
if [[ -f "INSTALL.rtf" ]]; then
  cp "INSTALL.rtf" "${STAGING_DIR}/README - Start Here.rtf"
fi

echo "==> Running lightweight secret scan against app bundle"
# 1) Binary strings scan
if strings "${STAGING_DIR}/${APP_BUNDLE}/Contents/MacOS/${APP_NAME}" 2>/dev/null | grep -E "${SECRET_PATTERN}" >/dev/null; then
  echo "ERROR: Potential secret detected in app binary. Aborting DMG creation."
  exit 1
fi

# 2) Resource/config text scan in bundle
if grep -RInE "${SECRET_PATTERN}" "${STAGING_DIR}/${APP_BUNDLE}/Contents" >/dev/null 2>&1; then
  echo "ERROR: Potential secret detected in app contents. Aborting DMG creation."
  exit 1
fi

echo "==> Creating DMG: ${DMG_PATH}"
mkdir -p "${DIST_DIR}"
rm -f "${DMG_PATH}"
hdiutil create \
  -volname "${APP_NAME}" \
  -srcfolder "${STAGING_DIR}" \
  -ov \
  -format UDZO \
  "${DMG_PATH}"

echo ""
echo "Done. Test DMG created: ${DMG_PATH}"
echo "Note: This is not notarized. Testers may need right-click -> Open on first launch."
