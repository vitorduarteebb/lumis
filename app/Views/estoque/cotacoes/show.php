<?php

declare(strict_types=1);

$bundle = is_array($bundle ?? null) ? $bundle : ['quote' => [], 'lines' => []];
$q = $bundle['quote'];
$lines = is_array($bundle['lines'] ?? null) ? $bundle['lines'] : [];
$id = (int) ($q['id'] ?? 0);
$st = (string) ($q['status'] ?? '');
$stLab = ['open' => 'Aberta', 'approved' => 'Aprovada', 'cancelled' => 'Cancelada'];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($q['quote_number'] ?? 'Cotação'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">
            <?= htmlspecialchars((string) ($q['supplier_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            · <?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/estoque/cotacoes">Voltar</a>
        <?php if ($st !== 'cancelled' && can('estoque.cotacoes.edit')): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="/estoque/cotacoes/<?= $id ?>/editar">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="lumis-form-section mb-3">
    <div class="row g-2 text-secondary small">
        <div class="col-md-4"><span class="text-white">Total:</span> <?= htmlspecialchars(lumis_money_br((float) ($q['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="col-md-4"><span class="text-white">Data:</span> <?= htmlspecialchars(substr((string) ($q['quoted_at'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php if (!empty($q['notes'])): ?>
        <p class="mt-2 mb-0 small"><?= nl2br(htmlspecialchars((string) $q['notes'], ENT_QUOTES, 'UTF-8')) ?></p>
    <?php endif; ?>
</div>

<div class="lumis-table-wrap mb-3">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Produto</th>
                <th class="text-end">Qtd</th>
                <th class="text-end">Custo unit.</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lines as $ln): ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($ln['product_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars((string) ($ln['qty'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['unit_cost'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['line_total'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($st !== 'cancelled' && can('estoque.cotacoes.delete')): ?>
    <form method="post" action="/estoque/cotacoes/<?= $id ?>/excluir" onsubmit="return confirm('Cancelar esta cotação?');">
        <?= \App\Helpers\Csrf::field() ?>
        <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar cotação</button>
    </form>
<?php endif; ?>
