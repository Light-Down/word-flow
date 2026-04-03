<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Page not found — Wordflow</title>
  <link href="https://fonts.googleapis.com" rel="preconnect" />
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            "primary": "#D2691E",
            "background": "#FDFBF7",
            "on-surface": "#1A1A1A",
            "on-surface-variant": "#4A4A4A",
            "outline": "#D1CDC7",
          },
          fontFamily: {
            "headline": ["Newsreader", "serif"],
            "body": ["Inter", "sans-serif"],
          },
        },
      },
    }
  </script>
</head>
<body class="bg-background text-on-surface font-body min-h-screen flex flex-col items-center justify-center px-8">
  <p class="font-headline text-8xl text-outline font-light mb-6">404</p>
  <h1 class="font-headline text-3xl text-on-surface mb-3">Page not found.</h1>
  <p class="text-on-surface-variant mb-10">That URL doesn't exist. Maybe it moved, maybe it never did.</p>
  <a href="/" class="px-6 py-3 bg-primary text-white rounded-full text-sm font-medium hover:opacity-90 transition-opacity">← Back to Wordflow</a>
</body>
</html>
