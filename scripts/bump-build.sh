#!/bin/bash
# Wordflow — Auto Build Number
# Liest .build_number aus dem Projektverzeichnis, zählt +1 und schreibt es in Info.plist.
# Einrichten: Xcode → Target → Build Phases → + → New Run Script Phase (vor "Compile Sources")

BUILD_FILE="${SRCROOT}/.build_number"

# Aktuellen Wert lesen (oder 0 als Startwert)
if [ -f "$BUILD_FILE" ]; then
    BUILD=$(cat "$BUILD_FILE")
else
    BUILD=0
fi

# +1 zählen
BUILD=$((BUILD + 1))

# Zurückschreiben
echo "$BUILD" > "$BUILD_FILE"

# In Info.plist eintragen
/usr/libexec/PlistBuddy -c "Set :CFBundleVersion $BUILD" "${TARGET_BUILD_DIR}/${INFOPLIST_PATH}"

echo "✅ Build Number: $BUILD"
