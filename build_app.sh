#!/bin/bash
set -e

# Configuration
# Arguments
MARKETING_VERSION=${1:-"1.0"}
PROJECT_VERSION=${2:-"1"}

APP_NAME="Wordflow"
BUNDLE_ID="com.markolenberg.Wordflow"
SCHEME="wordflow"
SOURCE_DIR="Wordflow"
BUILD_DIR=".build/release"
APP_BUNDLE="$APP_NAME.app"
CONTENTS_DIR="$APP_BUNDLE/Contents"
MACOS_DIR="$CONTENTS_DIR/MacOS"
RESOURCES_DIR="$CONTENTS_DIR/Resources"

echo "🚀 Building $APP_NAME (Version $MARKETING_VERSION, Build $PROJECT_VERSION)..."

# 0. Regenerate icon assets to avoid stale icns content.
if [ -f "IconScripts/generate_brand_icons.swift" ]; then
    echo "🎨 Regenerating brand icons..."
    swift "IconScripts/generate_brand_icons.swift"
fi

if [ -d "$SOURCE_DIR/Wordflow.iconset" ]; then
    echo "🧩 Building AppIcon .icns from iconset..."
    iconutil -c icns "$SOURCE_DIR/Wordflow.iconset" -o "Wordflow.icns"
fi

# 1. Compile Swift Files
# We find all .swift files in Wordflow/ directory
echo "Building for production..."
mkdir -p "$MACOS_DIR"
mkdir -p "$RESOURCES_DIR"

# Find all swift files
SWIFT_FILES=$(find "$SOURCE_DIR" -name "*.swift")

# Compile
# note: -parse-as-library is needed for @main
swiftc -O $SWIFT_FILES \
    -o "$MACOS_DIR/$APP_NAME" \
    -target arm64-apple-macosx13.0 \
    -parse-as-library \
    -Xlinker -rpath -Xlinker @executable_path/../Frameworks \
    -Xlinker -sectcreate -Xlinker __TEXT -Xlinker __info_plist -Xlinker "$SOURCE_DIR/Info.plist"

echo "Build complete!"

# 2. Create Info.plist (if not exists or dynamic)
# We generate a minimal one if needed, but swiftc expects one for embedding?
# Actually, let's write a proper Info.plist to Resources as well.

echo "📦 Creating App Bundle..."

# Generate Info.plist content
cat > "$CONTENTS_DIR/Info.plist" <<EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>CFBundleExecutable</key>
    <string>$APP_NAME</string>
    <key>CFBundleIdentifier</key>
    <string>$BUNDLE_ID</string>
    <key>CFBundleName</key>
    <string>$APP_NAME</string>
    <key>CFBundleDisplayName</key>
    <string>$APP_NAME</string>
    <key>CFBundlePackageType</key>
    <string>APPL</string>
    <key>CFBundleShortVersionString</key>
    <string>$MARKETING_VERSION</string>
    <key>CFBundleVersion</key>
    <string>$PROJECT_VERSION</string>
    <key>CFBundleIconFile</key>
    <string>AppIcon</string>
    <key>LSMinimumSystemVersion</key>
    <string>13.0</string>
    <key>LSUIElement</key>
    <true/>
    <key>NSMicrophoneUsageDescription</key>
    <string>Wordflow benötigt Zugriff auf das Mikrofon für die Sprachaufnahme und Transkription.</string>
    <key>NSAppleEventsUsageDescription</key>
    <string>Wordflow benötigt Zugriff, um Text in andere Apps einzufügen.</string>
    <key>CFBundleURLTypes</key>
    <array>
        <dict>
            <key>CFBundleURLName</key>
            <string>$BUNDLE_ID</string>
            <key>CFBundleURLSchemes</key>
            <array>
                <string>$SCHEME</string>
            </array>
        </dict>
    </array>
</dict>
</plist>
EOF

# 3. Copy Resources
echo "🎵 Copying Sounds..."
if [ -d "$SOURCE_DIR/Sounds" ]; then
    cp -r "$SOURCE_DIR/Sounds" "$RESOURCES_DIR/"
fi

# Copy App Icon
if [ -f "Wordflow.icns" ]; then
    echo "🎨 Copying App Icon..."
    cp "Wordflow.icns" "$RESOURCES_DIR/AppIcon.icns"
fi

# Copy Menu Bar Icons
if [ -f "$SOURCE_DIR/Assets.xcassets/MenuBar-Normal.png" ]; then
    cp "$SOURCE_DIR/Assets.xcassets/MenuBar-Normal.png" "$RESOURCES_DIR/"
    cp "$SOURCE_DIR/Assets.xcassets/MenuBar-Recording.png" "$RESOURCES_DIR/"
fi

# Code Signing with Entitlements
echo "🔐 Signing App..."
if [ -f "$SOURCE_DIR/Wordflow.entitlements" ]; then
    codesign --force --options runtime --deep --sign - --entitlements "$SOURCE_DIR/Wordflow.entitlements" "$APP_BUNDLE"
else
    codesign --force --options runtime --deep --sign - "$APP_BUNDLE"
fi

echo "✅ Done! App is at: $(pwd)/$APP_BUNDLE"
echo "👉 You can drag this to your Applications folder."
