<?php

declare(strict_types=1);

$bundle = is_array($bundle ?? null) ? $bundle : ['transfer' => [], 'items' => []];
$t = $bundle['transfer'];
$items = is_array($bundle['items'] ?? null) ? $bundle['items'] : [];
$id = (int) ($t['id'] ?? 0);
$st = (string) ($t['status'] ?? '');
$stLab = ['pending' => 'Pendente', 'done' => 'Concluída', 'cancelled' => 'Cancelada'];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Transferência #<?= $id ?></h2>
        <div class="text-secondary small">
            <?php if ($st === 'done'): ?>
                <span class="badge badge-lumis-success"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
            <?php elseif ($st === 'cancelled'): ?>
                <span class="badge text-bg-secondary"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>
                <span class="badge badge-lumis-warning"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/estoque/transferencias">Voltar</a>
</div>

<div class="lumis-form-section mb-3">
    <div class="row g-2 text-secondary small">
        <div class="col-md-6"><span class="text-white">Origem:</span> <?= htmlspecialchars((string) ($t['from_store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="col-md-6"><span class="text-white">Destino:</span> <?= htmlspecialchars((string) ($t['to_store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
        <?php if (!empty($t['notes'])): ?>
            <div class="col-12"><span class="text-white">Observações:</span> <?= nl2br(htmlspecialchars((string) $t['notes'], ENT_QUOTES, 'UTF-8')) ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="lumis-table-wrap mb-3">
    <table class="table lumis-table mb-0">
        <thead>
            <tr><th>Produto</th><th class="text-end">Qtd</th></tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($it['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end font-monospace small"><?= htmlspecialchars(number_format((float) ($it['qty'] ?? 0), 4, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($st === 'pending' && can('estoque.transferencias.edit')): ?>
    <form method="post" action="/estoque/transferencias/<?= $id ?>/concluir" onsubmit="return confirm('Concluir transferência e movimentar estoque?');">
        <?= \App\Helpers\Csrf::field() ?>
        <button type="submit" class="btn btn-success btn-sm">Concluir transferência</button>
    </form>
<?php endif; ?>
