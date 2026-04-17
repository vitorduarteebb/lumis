<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(($title ?? 'Lumis') . ' · ' . (config('app.name') ?? 'ERP'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/components.css">
</head>
<body class="app-body">
<?php
$pageHeading = $pageTitle ?? (isset($title) ? (string) $title : 'Página');
if (!isset($breadcrumbs) || !is_array($breadcrumbs)) {
    $breadcrumbs = [
        ['label' => 'Início', 'href' => '/dashboard'],
        ['label' => $pageHeading, 'href' => null],
    ];
}
?>
<div class="app-layout" id="appLayout">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="app-main">
        <?php include __DIR__ . '/../components/header.php'; ?>

        <div class="app-main__inner px-3 px-lg-4 py-3">
            <?php include __DIR__ . '/../components/flash.php'; ?>
            <main class="app-content" id="appContent">
                <?= $content ?>
            </main>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="/assets/js/app.js" defer></script>
</body>
</html>
