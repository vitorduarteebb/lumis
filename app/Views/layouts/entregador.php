<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(($title ?? 'Entregas') . ' · ' . (config('app.name') ?? 'Lumis'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        body.entregador-body { background: #0f1419; min-height: 100vh; }
        .entregador-top { background: linear-gradient(180deg, rgba(30,40,55,.95), rgba(15,20,25,.98)); border-bottom: 1px solid rgba(255,255,255,.08); }
        .entregador-card { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); border-radius: 12px; }
    </style>
</head>
<body class="entregador-body text-white">
<div class="entregador-top sticky-top py-2 px-3 d-flex align-items-center justify-content-between gap-2">
    <a href="/locacoes/painel-entregador" class="text-decoration-none text-white fw-semibold small d-flex align-items-center gap-2">
        <i class="bi bi-truck-front"></i> Minhas entregas
    </a>
    <form method="post" action="/logout" class="m-0"><?= \App\Helpers\Csrf::field() ?>
        <button type="submit" class="btn btn-sm btn-outline-light">Sair</button>
    </form>
</div>
<div class="px-3 py-3">
    <?php include __DIR__ . '/../components/flash.php'; ?>
    <main>
        <?= $content ?>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
