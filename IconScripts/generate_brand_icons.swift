import Cocoa

let fileManager = FileManager.default
let projectRoot = URL(fileURLWithPath: FileManager.default.currentDirectoryPath)

let assetsDir = projectRoot.appendingPathComponent("Wordflow/Assets.xcassets")
let iconsetDir = projectRoot.appendingPathComponent("Wordflow/Wordflow.iconset")
let appIconBasePath = assetsDir.appendingPathComponent("AppIcon-1024.png")
let menuNormalPath = assetsDir.appendingPathComponent("MenuBar-Normal.png")
let menuRecordingPath = assetsDir.appendingPathComponent("MenuBar-Recording.png")

func drawThreeFlowStrokes(in rect: CGRect, color: NSColor, lineWidth: CGFloat) {
    color.setStroke()

    func stroke(_ build: (NSBezierPath) -> Void) {
        let path = NSBezierPath()
        path.lineCapStyle = .round
        path.lineJoinStyle = .round
        path.lineWidth = lineWidth
        build(path)
        path.stroke()
    }

    let w = rect.width
    let h = rect.height

    // All three strokes share identical geometry and fixed center-line spacing.
    let leftX = rect.minX + w * 0.14
    let rightX = rect.minX + w * 0.86
    let cp1X = rect.minX + w * 0.35
    let cp2X = rect.minX + w * 0.65
    let rise: CGFloat = h * 0.14
    let fall: CGFloat = h * 0.12
    let spacing: CGFloat = h * 0.30
    let centerY = rect.midY

    func wave(at y: CGFloat) {
        stroke { p in
            p.move(to: CGPoint(x: leftX, y: y))
            p.curve(to: CGPoint(x: rightX, y: y),
                    controlPoint1: CGPoint(x: cp1X, y: y + rise),
                    controlPoint2: CGPoint(x: cp2X, y: y - fall))
        }
    }

    wave(at: centerY + spacing)
    wave(at: centerY)
    wave(at: centerY - spacing)
}

func image(size: CGSize, draw: () -> Void) -> NSImage {
    let img = NSImage(size: size)
    img.lockFocus()
    draw()
    img.unlockFocus()
    return img
}

func bitmapImage(pixelWidth: Int, pixelHeight: Int, draw: (CGContext) -> Void) -> NSImage {
    let rep = NSBitmapImageRep(
        bitmapDataPlanes: nil,
        pixelsWide: pixelWidth,
        pixelsHigh: pixelHeight,
        bitsPerSample: 8,
        samplesPerPixel: 4,
        hasAlpha: true,
        isPlanar: false,
        colorSpaceName: .deviceRGB,
        bytesPerRow: 0,
        bitsPerPixel: 0
    )!

    let context = NSGraphicsContext(bitmapImageRep: rep)!
    NSGraphicsContext.saveGraphicsState()
    NSGraphicsContext.current = context

    let cg = context.cgContext
    cg.setFillColor(NSColor.clear.cgColor)
    cg.fill(CGRect(x: 0, y: 0, width: pixelWidth, height: pixelHeight))

    draw(cg)
    NSGraphicsContext.restoreGraphicsState()

    let img = NSImage(size: CGSize(width: pixelWidth, height: pixelHeight))
    img.addRepresentation(rep)
    return img
}

func savePNG(_ image: NSImage, to url: URL) throws {
    guard let tiff = image.tiffRepresentation,
          let bitmap = NSBitmapImageRep(data: tiff),
          let data = bitmap.representation(using: .png, properties: [:]) else {
        throw NSError(domain: "IconGen", code: 1, userInfo: [NSLocalizedDescriptionKey: "Could not encode PNG"])
    }
    try data.write(to: url)
}

func makeMenuBarNormal() -> NSImage {
    let px = 88
    return bitmapImage(pixelWidth: px, pixelHeight: px) { _ in
        let drawingRect = CGRect(x: 9.6, y: 15.6, width: 68.8, height: 56.8)
        drawThreeFlowStrokes(in: drawingRect, color: .black, lineWidth: 8.8)
    }
}

func makeMenuBarRecording() -> NSImage {
    let px = 88
    return bitmapImage(pixelWidth: px, pixelHeight: px) { _ in
        let drawingRect = CGRect(x: 9.6, y: 15.6, width: 68.8, height: 56.8)
        drawThreeFlowStrokes(in: drawingRect, color: .black, lineWidth: 9.4)

        NSColor.black.setFill()
        let dot = NSBezierPath(ovalIn: CGRect(x: 64.4, y: 8.0, width: 15.2, height: 15.2))
        dot.fill()
    }
}

func makeAppIconBase(size: CGSize) -> NSImage {
    image(size: size) {
        let rect = CGRect(origin: .zero, size: size)

        let bg = NSBezierPath(roundedRect: rect, xRadius: size.width * 0.22, yRadius: size.height * 0.22)
        NSColor(calibratedRed: 0.08, green: 0.09, blue: 0.12, alpha: 1).setFill()
        bg.fill()

        let highlight = NSBezierPath(roundedRect: rect.insetBy(dx: size.width * 0.01, dy: size.height * 0.01),
                                     xRadius: size.width * 0.20,
                                     yRadius: size.height * 0.20)
        NSColor.white.withAlphaComponent(0.06).setStroke()
        highlight.lineWidth = size.width * 0.01
        highlight.stroke()

        let glyphRect = rect.insetBy(dx: size.width * 0.20, dy: size.height * 0.24)
        drawThreeFlowStrokes(in: glyphRect, color: NSColor(calibratedRed: 0.95, green: 0.64, blue: 0.32, alpha: 1), lineWidth: size.width * 0.062)
    }
}

func ensureIconsetDir() throws {
    if fileManager.fileExists(atPath: iconsetDir.path) {
        try fileManager.removeItem(at: iconsetDir)
    }
    try fileManager.createDirectory(at: iconsetDir, withIntermediateDirectories: true)
}

func writeIconset(from base: NSImage) throws {
    let specs: [(Int, Int)] = [
        (16, 1), (16, 2),
        (32, 1), (32, 2),
        (128, 1), (128, 2),
        (256, 1), (256, 2),
        (512, 1), (512, 2)
    ]

    for (pt, scale) in specs {
        let px = pt * scale
        let rendered = image(size: CGSize(width: px, height: px)) {
            base.draw(in: CGRect(x: 0, y: 0, width: px, height: px),
                      from: CGRect(origin: .zero, size: base.size),
                      operation: .copy,
                      fraction: 1.0)
        }

        let filename = scale == 1 ? "icon_\(pt)x\(pt).png" : "icon_\(pt)x\(pt)@2x.png"
        try savePNG(rendered, to: iconsetDir.appendingPathComponent(filename))
    }
}

func run() throws {
    let normal = makeMenuBarNormal()
    let recording = makeMenuBarRecording()
    try savePNG(normal, to: menuNormalPath)
    try savePNG(recording, to: menuRecordingPath)

    let appBase = makeAppIconBase(size: CGSize(width: 1024, height: 1024))
    try savePNG(appBase, to: appIconBasePath)

    try ensureIconsetDir()
    try writeIconset(from: appBase)

    print("✅ Generated:")
    print("- \(menuNormalPath.path)")
    print("- \(menuRecordingPath.path)")
    print("- \(appIconBasePath.path)")
    print("- iconset in \(iconsetDir.path)")
}

try run()
