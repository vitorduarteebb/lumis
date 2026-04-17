<?php

declare(strict_types=1);

/** @var string $pageHeading */
/** @var list<array{label: string, href?: string|null}> $breadcrumbs */

$userName = (string) ($_SESSION['user_name'] ?? 'Usuário');
$userEmail = (string) ($_SESSION['user_email'] ?? '');
if ($userName === '') {
    $initial = '?';
} elseif (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
    $initial = mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');
} else {
    $initial = strtoupper(substr($userName, 0, 1));
}
?>

<header class="app-header app-header--sticky border-bottom">
    <div class="app-header__left d-flex align-items-center gap-2 gap-md-3 min-w-0">
        <button type="button" class="btn btn-icon btn-ghost d-lg-none" id="appSidebarOpen" aria-label="Abrir menu">
            <i class="bi bi-list fs-4" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-icon btn-ghost d-none d-lg-inline-flex" id="appSidebarCollapse" aria-label="Recolher menu" title="Recolher menu">
            <i class="bi bi-layout-sidebar-inset fs-5" aria-hidden="true"></i>
        </button>
        <div class="min-w-0">
            <div class="app-header__eyebrow text-secondary small text-truncate"><?= htmlspecialchars((string) (config('app.name') ?? 'Lumis ERP'), ENT_QUOTES, 'UTF-8') ?></div>
            <h1 class="app-header__title h5 mb-0 text-white text-truncate"><?= htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
    </div>

    <div class="app-header__center d-none d-xl-flex flex-grow-1 justify-content-center px-3">
        <div class="app-search-placeholder w-100" role="search" aria-label="Busca global (em breve)">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span>Buscar em todo o sistema…</span>
            <kbd class="ms-auto">/</kbd>
        </div>
    </div>

    <div class="app-header__right d-flex align-items-center gap-2">
        <button type="button" class="btn btn-icon btn-ghost position-relative" title="Notificações (em breve)" aria-label="Notificações">
            <i class="bi bi-bell fs-5" aria-hidden="true"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-2 border-dark rounded-circle d-none" id="appNotifDot" aria-hidden="true"></span>
        </button>

        <div class="dropdown">
            <button class="btn btn-user dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="user-avatar" aria-hidden="true"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="d-none d-md-flex flex-column align-items-start lh-sm text-start">
                    <span class="small fw-semibold text-white text-truncate" style="max-width: 160px;"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($userEmail !== ''): ?>
                        <span class="text-secondary" style="font-size: 0.75rem; max-width: 160px;"><?= htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end app-dropdown shadow-lg border-0 p-2">
                <li><span class="dropdown-item-text small text-secondary">Perfil (em breve)</span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="post" action="/logout" class="m-0">
                        <?= \App\Helpers\Csrf::field() ?>
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 rounded-2">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                            Sair
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

<nav class="app-breadcrumb border-bottom px-4 py-2" aria-label="breadcrumb">
    <ol class="breadcrumb mb-0 small">
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <?php
            $label = (string) ($crumb['label'] ?? '');
            $href = $crumb['href'] ?? null;
            $isLast = $i === count($breadcrumbs) - 1;
            ?>
            <li class="breadcrumb-item <?= $isLast ? 'active' : '' ?>" <?= $isLast ? 'aria-current="page"' : '' ?>>
                <?php if (!$isLast && is_string($href) && $href !== ''): ?>
                    <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
                <?php else: ?>
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
