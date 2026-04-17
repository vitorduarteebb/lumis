<?php

declare(strict_types=1);

$items = config('navigation');
if (!is_array($items)) {
    $items = [];
}
$current = lumis_current_path();
?>

<aside class="app-sidebar" id="appSidebar" aria-label="Menu principal">
    <div class="app-sidebar__brand border-bottom">
        <a class="app-sidebar__brand-link text-decoration-none d-flex align-items-center gap-2" href="/dashboard">
            <span class="brand-mark rounded-2 flex-shrink-0" aria-hidden="true"></span>
            <span class="app-sidebar__brand-text min-w-0">
                <span class="d-block fw-semibold text-white text-truncate">Lumis</span>
                <span class="d-block text-secondary brand-sub">ERP</span>
            </span>
        </a>
    </div>

    <div class="app-sidebar__nav">
        <nav class="nav flex-column py-2 px-2 gap-1" id="appSidebarNav">
            <?php foreach ($items as $item): ?>
                <?php
                $key = (string) ($item['key'] ?? '');
                $label = (string) ($item['label'] ?? '');
                $icon = (string) ($item['icon'] ?? 'bi-circle');
                $children = $item['children'] ?? null;
                $href = $item['href'] ?? '#';
                $disabled = !empty($item['disabled']);
                $match = (string) ($item['match'] ?? 'prefix');
                ?>

                <?php if (is_array($children) && $children !== []): ?>
                    <?php
                    $collapseId = 'nav-sub-' . preg_replace('/[^a-z0-9_-]/i', '', $key);
                    $anyActive = false;
                    foreach ($children as $ch) {
                        $chref = (string) ($ch['href'] ?? '#');
                        $chMatch = (string) ($ch['match'] ?? 'exact');
                        if ($chref !== '#' && lumis_nav_active($chref, $chMatch)) {
                            $anyActive = true;
                            break;
                        }
                    }
                    ?>
                    <div class="nav-group">
                        <button
                            class="nav-link nav-link--parent d-flex align-items-center gap-2 w-100 border-0 bg-transparent text-start <?= $anyActive ? 'is-active' : '' ?>"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8') ?>"
                            aria-expanded="<?= $anyActive ? 'true' : 'false' ?>"
                            aria-controls="<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8') ?>"
                        >
                            <i class="bi <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?> nav-icon" aria-hidden="true"></i>
                            <span class="nav-label flex-grow-1 text-truncate"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                            <i class="bi bi-chevron-down small chevron" aria-hidden="true"></i>
                        </button>
                        <div class="collapse <?= $anyActive ? 'show' : '' ?>" id="<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="nav-sub ms-3 ps-2 border-start border-secondary-subtle">
                                <?php foreach ($children as $child): ?>
                                    <?php
                                    $clabel = (string) ($child['label'] ?? '');
                                    $chref = (string) ($child['href'] ?? '#');
                                    $chMatch = (string) ($child['match'] ?? 'exact');
                                    $cdisabled = !empty($child['disabled']);
                                    ?>
                                    <?php if ($cdisabled || $chref === '#'): ?>
                                        <span class="nav-link nav-link--child disabled small" title="Em breve — módulo em desenvolvimento"><?= htmlspecialchars($clabel, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php else: ?>
                                        <a class="nav-link nav-link--child small <?= lumis_nav_active($chref, $chMatch) ? 'active' : '' ?>" href="<?= htmlspecialchars($chref, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($clabel, ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php
                    $isActive = !$disabled && $href !== '#' && lumis_nav_active($href, $match);
                    ?>
                    <?php if ($disabled || $href === '#'): ?>
                        <span class="nav-link disabled d-flex align-items-center gap-2">
                            <i class="bi <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?> nav-icon" aria-hidden="true"></i>
                            <span class="nav-label text-truncate"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                    <?php else: ?>
                        <a class="nav-link d-flex align-items-center gap-2 <?= $isActive ? 'active' : '' ?>" <?= $isActive ? 'aria-current="page"' : '' ?> href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?> nav-icon" aria-hidden="true"></i>
                            <span class="nav-label text-truncate"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="app-sidebar__footer mt-auto border-top small text-secondary px-3 py-3">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <span class="text-truncate">v1 · Fase 3</span>
            <span class="badge rounded-pill text-bg-secondary">SaaS</span>
        </div>
    </div>
</aside>

<div class="app-sidebar-backdrop" id="appSidebarBackdrop" aria-hidden="true"></div>
