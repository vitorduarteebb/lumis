<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(($title ?? 'Entrar') . ' · ' . (config('app.name') ?? 'ERP'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body lumis-auth-body">

<div class="lumis-auth-shell">
    <aside class="lumis-auth-brand">
        <div class="lumis-auth-brand__inner">
            <div class="lumis-auth-brand__logo">
                <div class="brand-mark brand-mark--lg rounded-3"></div>
                <div>
                    <div class="text-white fw-bold fs-5 lh-1 mb-1">Lumis ERP</div>
                    <div class="small" style="color: var(--lumis-muted);">Plataforma corporativa</div>
                </div>
            </div>
            <p class="lumis-auth-brand__eyebrow">Confiança em escala</p>
            <h1 class="lumis-auth-brand__title">Operações, finanças e cadastros no mesmo ecossistema.</h1>
            <p class="lumis-auth-brand__lead">
                Arquitetura preparada para multiempresa, permissões granulares e auditoria — com a experiência visual que sua equipe merece.
            </p>
            <ul class="lumis-auth-brand__list">
                <li><i class="bi bi-shield-lock"></i> Sessão segura, CSRF e trilhas de acesso por perfil</li>
                <li><i class="bi bi-diagram-3"></i> MVC, repositórios e serviços prontos para evoluir</li>
                <li><i class="bi bi-graph-up-arrow"></i> Painéis e fluxos pensados para produtividade diária</li>
            </ul>
        </div>
        <div class="lumis-auth-brand__footer">
            Uso interno autorizado · Ambiente criptografado (HTTPS recomendado em produção)
        </div>
    </aside>

    <div class="lumis-auth-main">
        <div class="lumis-auth-mobile-brand">
            <div class="brand-mark rounded-3"></div>
            <span class="text-white fw-semibold">Lumis ERP</span>
        </div>
        <div class="lumis-auth-main__wrap">
            <?= $content ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="/assets/js/app.js" defer></script>
</body>
</html>
