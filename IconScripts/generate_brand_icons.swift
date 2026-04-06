import Cocoa

let fileManager = FileManager.default
let projectRoot = URL(fileURLWithPath: FileManager.default.currentDirectoryPath)

let assetsDir = projectRoot.appendingPathComponent("Wordflow/Assets.xcassets")
let iconsetDir = projectRoot.appendingPathComponent("Wordflow/Wordflow.iconset")
let appIconBasePath = assetsDir.appendingPathComponent("AppIcon-1024.png")
let menuNormalPath = assetsDir.appendingPathComponent("MenuBar-Normal.png")
let menuRecordingPath = assetsDir.appendingPathComponent("MenuBar-Recording.png")

func drawSafeMenuBarW(in rect: CGRect, color: NSColor) {
    let context = NSGraphicsContext.current!.cgContext
    context.saveGState()

    color.setFill()
    context.translateBy(x: rect.minX, y: rect.minY)
    context.scaleBy(x: rect.width / 1024.0, y: rect.height / 1024.0)
    context.translateBy(x: 0, y: 1024)
    context.scaleBy(x: 1.0, y: -1.0)
    
    // Exact matching logic from the new SVG
    // 3 parallel rectangles translated and rotated
    let angleRad = CGFloat(-24.0) * .pi / 180.0
    
    func drawPill(xOffset: CGFloat, w: CGFloat, h: CGFloat, rx: CGFloat) {
        context.saveGState()
        context.translateBy(x: xOffset, y: 250)
        context.rotate(by: angleRad)
        let r = CGRect(x: 0, y: 0, width: w, height: h)
        NSBezierPath(roundedRect: r, xRadius: rx, yRadius: rx).fill()
        context.restoreGState()
    }
    
    // Scale X-offsets slightly to center nicely in the 1024x1024 bounding box
    drawPill(xOffset: 200, w: 190, h: 560, rx: 60)
    drawPill(xOffset: 480, w: 190, h: 560, rx: 60)
    drawPill(xOffset: 760, w: 190, h: 280, rx: 60)
    
    context.restoreGState()
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
    return bitmapImage(pixelWidth: 88, pixelHeight: 88) { _ in
        // Tweaked drawingRect to center the W visual mass perfectly in menu bar icons
        let drawingRect = CGRect(x: -8, y: 18, width: 104, height: 104)
        drawSafeMenuBarW(in: drawingRect, color: .black)
    }
}

func makeMenuBarRecording() -> NSImage {
    return bitmapImage(pixelWidth: 88, pixelHeight: 88) { _ in
        let drawingRect = CGRect(x: -16, y: 18, width: 104, height: 104)
        drawSafeMenuBarW(in: drawingRect, color: .black)
        NSColor.black.setFill()
        let dot = NSBezierPath(ovalIn: CGRect(x: 68.0, y: 8.0, width: 16.0, height: 16.0))
        dot.fill()
    }
}

func makeAppIconBase(size: CGSize) -> NSImage {
    let svgPath = projectRoot.appendingPathComponent("website/assets/wordflow-new-logo.svg").path
    let svgImage = NSImage(contentsOf: URL(fileURLWithPath: svgPath))!
    return image(size: size) {
        svgImage.draw(in: CGRect(origin: .zero, size: size))
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
        (16, 1), (16, 2), (32, 1), (32, 2),
        (128, 1), (128, 2), (256, 1), (256, 2), (512, 1), (512, 2)
    ]
    for (pt, scale) in specs {
        let px = pt * scale
        let rendered = image(size: CGSize(width: px, height: px)) {
            base.draw(in: CGRect(x: 0, y: 0, width: px, height: px),
                      from: CGRect(origin: .zero, size: base.size),
                      operation: .copy, fraction: 1.0)
        }
        let filename = scale == 1 ? "icon_\(pt)x\(pt).png" : "icon_\(pt)x\(pt)@2x.png"
        try savePNG(rendered, to: iconsetDir.appendingPathComponent(filename))
    }
}

func run() throws {
    try savePNG(makeMenuBarNormal(), to: menuNormalPath)
    try savePNG(makeMenuBarRecording(), to: menuRecordingPath)
    let appBase = makeAppIconBase(size: CGSize(width: 1024, height: 1024))
    try savePNG(appBase, to: appIconBasePath)
    try ensureIconsetDir()
    try writeIconset(from: appBase)
    print("✅ All icons generated")
}

try run()
