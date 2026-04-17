<?php

declare(strict_types=1);

/** @var array<string, mixed> $service */

$s = is_array($service ?? null) ? $service : [];
$id = (int) ($s['id'] ?? 0);
$fmt = static fn ($v): string => number_format((float) $v, 2, ',', '.');
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Serviços</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small"><?= htmlspecialchars((string) ($s['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/servicos">Voltar</a>
        <?php if (can('servicos.gerenciar.edit') || can('services.edit')): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="/servicos/<?= $id ?>/editar">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm module-shell__panel">
    <div class="card-body p-4">
        <div class="row g-3 small">
            <div class="col-md-4">
                <div class="text-secondary">Preço</div>
                <div class="text-white">R$ <?= htmlspecialchars($fmt($s['price'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-secondary">Duração estimada</div>
                <div class="text-white">
                    <?php
                    $dm = $s['duration_minutes'] ?? null;
                    echo $dm !== null && $dm !== '' ? htmlspecialchars((string) $dm, ENT_QUOTES, 'UTF-8') . ' min' : '—';
                    ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-secondary">Status</div>
                <div class="text-white"><?= (int) ($s['status'] ?? 0) === 1 ? 'Ativo' : 'Inativo' ?></div>
            </div>
            <?php if (!empty($s['description'])): ?>
                <div class="col-12">
                    <div class="text-secondary">Descrição</div>
                    <div class="text-white"><?= nl2br(htmlspecialchars((string) $s['description'], ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
