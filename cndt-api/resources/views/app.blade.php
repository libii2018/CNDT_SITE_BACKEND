<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  {{-- Pipeline Vite de Laravel : ressources du dashboard React (manifest public/build) --}}
  @vite(['resources/js/dashboard/main.tsx'])
  @inertiaHead
</head>
<body>
  {{-- Lecture des fichiers Word (import .docx dans les articles) --}}
  <script src="/mammoth.browser.min.js"></script>
  @inertia
</body>
</html>
