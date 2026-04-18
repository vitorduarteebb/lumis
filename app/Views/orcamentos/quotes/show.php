<?php

declare(strict_types=1);

$quoteKind = (string) ($quoteKind ?? 'product');
$quote = is_array($quote ?? null) ? $quote : [];
$items = is_array($items ?? null) ? $items : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];
$basePath = (string) ($basePath ?? '/orcamentos/produtos');

$stLabels = [
    'open' => 'Aberto', 'approved' => 'Aprovado', 'rejected' => 'Recusado', 'cancelled' => 'Cancelado', 'converted' => 'Convertido',
];
$st = (string) ($quote['status'] ?? '');
$permEdit = $quoteKind === 'product' ? 'orcamentos.produtos.edit' : 'orcamentos.servicos.edit';
$permCreate = $quoteKind === 'product' ? 'orcamentos.produtos.create' : 'orcamentos.servicos.create';
$permDelete = $quoteKind === 'product' ? 'orcamentos.produtos.delete' : 'orcamentos.servicos.delete';
$rid = (int) ($quote['id'] ?? 0);
$sub = (float) ($quote['subtotal_amount'] ?? 0);
$disc = (float) ($quote['discount_total'] ?? 0);
$tot = (float) ($quote['total_amount'] ?? 0);
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Orçamentos</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($quote['quote_number'] ?? 'Orçamento'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small"><?= htmlspecialchars((string) ($quote['client_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Voltar</a>
        <a class="btn btn-outline-light btn-sm rounded-3" href="<?= htmlspecialchars($basePath . '/' . $rid . '/pdf', ENT_QUOTES, 'UTF-8') ?>" target="_blank">PDF</a>
        <?php if (can($permEdit) && !in_array($st, ['converted', 'cancelled'], true)): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath . '/' . $rid . '/editar', ENT_QUOTES, 'UTF-8') ?>">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="lumis-form-section">
            <div class="text-secondary small mb-2">Resumo</div>
            <p class="text-white mb-1">Status: <span class="badge badge-lumis badge-lumis-secondary"><?= htmlspecialchars($stLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></p>
            <p class="text-secondary small mb-0">Emissão: <?= htmlspecialchars((string) ($quote['issued_at'] ?? '—'), ENT_QUOTES, 'UTF-8') ?> · Validade: <?= htmlspecialchars((string) ($quote['valid_until'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($quote['notes'])): ?>
                <p class="text-secondary small mt-2 mb-0"><?= nl2br(htmlspecialchars((string) $quote['notes'], ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>
        </div>
        <div class="lumis-table-wrap mt-3">
            <table class="table lumis-table mb-0">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-end">Qtd</th>
                        <th class="text-end">V. unit.</th>
                        <th class="text-end">Desc.</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <?php
                        $name = $quoteKind === 'product'
                            ? (string) ($it['product_name'] ?? '—')
                            : (string) ($it['service_name'] ?? '—');
                        ?>
                        <tr>
                            <td class="text-white">
                                <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($it['description'])): ?>
                                    <div class="small text-secondary"><?= htmlspecialchars((string) $it['description'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-secondary small"><?= htmlspecialchars((string) $it['qty'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end text-secondary small"><?= htmlspecialchars(lumis_money_br((float) $it['unit_price']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end text-secondary small"><?= htmlspecialchars(lumis_money_br((float) ($it['line_discount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end text-white"><?= htmlspecialchars(lumis_money_br((float) ($it['line_total'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 rounded-3 border border-secondary-subtle bg-dark bg-opacity-25">
            <div class="d-flex justify-content-between"><span class="text-secondary">Subtotal</span><span class="text-white"><?= htmlspecialchars(lumis_money_br($sub), ENT_QUOTES, 'UTF-8') ?></span></div>
            <div class="d-flex justify-content-between mt-2"><span class="text-secondary">Desconto</span><span class="text-white"><?= htmlspecialchars(lumis_money_br($disc), ENT_QUOTES, 'UTF-8') ?></span></div>
            <hr class="border-secondary">
            <div class="d-flex justify-content-between"><span class="text-white fw-semibold">Total</span><span class="text-white fw-semibold"><?= htmlspecialchars(lumis_money_br($tot), ENT_QUOTES, 'UTF-8') ?></span></div>
        </div>
        <div class="mt-3 d-grid gap-2">
            <?php if ($quoteKind === 'product' && can($permEdit) && !in_array($st, ['converted', 'cancelled'], true)): ?>
                <form method="post" action="<?= htmlspecialchars($basePath . '/' . $rid . '/converter-venda', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Converter em venda e baixar estoque?');">
                    <?= \App\Helpers\Csrf::field() ?>
                    <button type="submit" class="btn btn-primary w-100">Converter em venda</button>
                </form>
            <?php endif; ?>
            <?php if ($quoteKind === 'service' && can($permEdit) && !in_array($st, ['converted', 'cancelled'], true)): ?>
                <form method="post" action="<?= htmlspecialchars($basePath . '/' . $rid . '/converter-os', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Criar ordem de serviço?');">
                    <?= \App\Helpers\Csrf::field() ?>
                    <button type="submit" class="btn btn-primary w-100">Converter em O.S.</button>
                </form>
            <?php endif; ?>
            <?php if (can($permCreate) && !in_array($st, ['cancelled'], true)): ?>
                <form method="post" action="<?= htmlspecialchars($basePath . '/' . $rid . '/duplicar', ENT_QUOTES, 'UTF-8') ?>">
                    <?= \App\Helpers\Csrf::field() ?>
                    <button type="submit" class="btn btn-lumis-secondary w-100">Duplicar</button>
                </form>
            <?php endif; ?>
            <?php if (can($permDelete) && !in_array($st, ['cancelled'], true)): ?>
                <form method="post" action="<?= htmlspecialchars($basePath . '/' . $rid . '/excluir', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Cancelar orçamento?');">
                    <?= \App\Helpers\Csrf::field() ?>
                    <button type="submit" class="btn btn-outline-danger w-100">Cancelar orçamento</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
