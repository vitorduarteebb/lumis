<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(($title ?? 'Início') . ' · ' . (config('app.name') ?? 'ERP'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body lumis-launch">
<div class="lumis-launch__ambient" aria-hidden="true"></div>
<div class="lumis-launch__grid" aria-hidden="true"></div>

<header class="lumis-launch-header">
    <div class="lumis-launch-header__brand">
        <div class="brand-mark rounded-3"></div>
        <div>
            <div class="lumis-launch-header__name text-white">Lumis</div>
            <div class="lumis-launch-header__tag">Enterprise ERP</div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a class="lumis-btn-header d-none d-sm-inline-flex" href="/login">Área do cliente</a>
        <a class="lumis-btn-header lumis-btn-header--primary" href="/login">
            Entrar <i class="bi bi-arrow-right-short fs-5"></i>
        </a>
    </div>
</header>

<main class="lumis-launch-main">
    <?= $content ?>
</main>

<footer class="lumis-launch-footer">
    <span>© <?= date('Y') ?> <?= htmlspecialchars((string) (config('app.name') ?? 'Lumis'), ENT_QUOTES, 'UTF-8') ?> · Gestão com precisão.</span>
    <span class="d-none d-md-inline">MVC · PHP 8.2+ · Multiempresa · Auditoria</span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="/assets/js/app.js" defer></script>
</body>
</html>
