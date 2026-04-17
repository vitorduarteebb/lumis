<?php

declare(strict_types=1);

/** @var int $page */
/** @var int $totalPages */
/** @var int $total */
/** @var string $basePath */
/** @var array<string, string|int|float|bool|null> $queryParams */

$page = max(1, (int) ($page ?? 1));
$total = (int) ($total ?? 0);
$totalPages = max(1, (int) ($totalPages ?? 1));
if ($total === 0) {
    $totalPages = 1;
}
$basePath = (string) ($basePath ?? '/');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];

$build = static function (int $p) use ($basePath, $queryParams): string {
    $q = array_merge($queryParams, ['page' => $p]);
    $qs = http_build_query($q);
    return $basePath . ($qs !== '' ? '?' . $qs : '');
};
?>

<nav class="d-flex flex-wrap align-items-center justify-content-between gap-2 text-secondary small pt-2" aria-label="Paginação">
    <div>
        Total: <span class="text-white"><?= $total ?></span>
        <?php if ($total > 0): ?>
            · Página <span class="text-white"><?= $page ?></span> de <span class="text-white"><?= $totalPages ?></span>
        <?php endif; ?>
    </div>
    <?php if ($total > 0 && $totalPages > 1): ?>
        <ul class="pagination lumis-pagination pagination-sm mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <?php if ($page > 1): ?>
                    <a class="page-link" href="<?= htmlspecialchars($build($page - 1), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
                <?php else: ?>
                    <span class="page-link">Anterior</span>
                <?php endif; ?>
            </li>
            <?php
            $from = max(1, $page - 2);
            $to = min($totalPages, $page + 2);
            for ($i = $from; $i <= $to; $i++):
                ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <?php if ($i !== $page): ?>
                        <a class="page-link" href="<?= htmlspecialchars($build($i), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                    <?php else: ?>
                        <span class="page-link"><?= $i ?></span>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="<?= htmlspecialchars($build($page + 1), ENT_QUOTES, 'UTF-8') ?>">Próximo</a>
                <?php else: ?>
                    <span class="page-link">Próximo</span>
                <?php endif; ?>
            </li>
        </ul>
    <?php endif; ?>
</nav>
