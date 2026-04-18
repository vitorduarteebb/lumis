<?php

declare(strict_types=1);

$order = is_array($order ?? null) ? $order : [];
$items = is_array($items ?? null) ? $items : [];
$totalItems = (float) ($totalItems ?? 0);

$stLabels = [
    'open' => 'Aberta', 'in_analysis' => 'Em análise', 'in_progress' => 'Em andamento', 'waiting_part' => 'Aguardando peça',
    'done' => 'Concluída', 'delivered' => 'Entregue', 'cancelled' => 'Cancelada',
];
$prLabels = ['low' => 'Baixa', 'normal' => 'Média', 'high' => 'Alta', 'urgent' => 'Urgente'];
$rid = (int) ($order['id'] ?? 0);
$st = (string) ($order['status'] ?? '');
$pr = (string) ($order['priority'] ?? '');
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Ordens de serviço</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($order['code'] ?? 'O.S.'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small"><?= htmlspecialchars((string) ($order['client_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/ordens-servico">Voltar</a>
        <a class="btn btn-outline-light btn-sm rounded-3" href="/ordens-servico/<?= $rid ?>/pdf" target="_blank">PDF</a>
        <?php if (can('ordens_servico.edit') && !in_array($st, ['delivered', 'cancelled'], true)): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="/ordens-servico/<?= $rid ?>/editar">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="lumis-form-section mb-3">
            <div class="text-secondary small mb-2">Situação</div>
            <p class="text-white mb-1">Status: <span class="badge badge-lumis badge-lumis-secondary"><?= htmlspecialchars($stLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
                · Prioridade: <?= htmlspecialchars($prLabels[$pr] ?? $pr, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-secondary small mb-0">Abertura: <?= htmlspecialchars((string) ($order['opened_at'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                · Previsão: <?= htmlspecialchars((string) ($order['expected_at'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                · Conclusão: <?= htmlspecialchars((string) ($order['completed_at'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($order['technician_name'])): ?>
                <p class="text-secondary small mb-0">Técnico: <?= htmlspecialchars((string) $order['technician_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
        <?php if (!empty($order['description'])): ?>
            <div class="mb-3"><div class="text-secondary small">Descrição</div><p class="text-white mb-0"><?= nl2br(htmlspecialchars((string) $order['description'], ENT_QUOTES, 'UTF-8')) ?></p></div>
        <?php endif; ?>
        <?php if (!empty($order['internal_notes'])): ?>
            <div class="mb-3"><div class="text-secondary small">Obs. internas</div><p class="text-secondary mb-0 small"><?= nl2br(htmlspecialchars((string) $order['internal_notes'], ENT_QUOTES, 'UTF-8')) ?></p></div>
        <?php endif; ?>
        <?php if (!empty($order['customer_notes'])): ?>
            <div class="mb-3"><div class="text-secondary small">Obs. ao cliente</div><p class="text-secondary mb-0 small"><?= nl2br(htmlspecialchars((string) $order['customer_notes'], ENT_QUOTES, 'UTF-8')) ?></p></div>
        <?php endif; ?>

        <div class="lumis-table-wrap">
            <table class="table lumis-table mb-0">
                <thead>
                    <tr>
                        <th>Tipo</th>
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
                        $isP = $it['product_id'] !== null && (int) $it['product_id'] > 0;
                        $nm = $isP ? (string) ($it['product_name'] ?? 'Produto') : (string) ($it['service_name'] ?? 'Serviço');
                        ?>
                        <tr>
                            <td class="text-secondary small"><?= $isP ? 'Produto' : 'Serviço' ?></td>
                            <td class="text-white"><?= htmlspecialchars($nm, ENT_QUOTES, 'UTF-8') ?></td>
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
    <div class="col-lg-4">
        <div class="p-3 rounded-3 border border-secondary-subtle bg-dark bg-opacity-25">
            <div class="d-flex justify-content-between"><span class="text-secondary">Total itens</span><span class="text-white fw-semibold"><?= htmlspecialchars(lumis_money_br($totalItems), ENT_QUOTES, 'UTF-8') ?></span></div>
        </div>
        <?php if (can('ordens_servico.delete') && !in_array($st, ['cancelled'], true)): ?>
            <form method="post" action="/ordens-servico/<?= $rid ?>/excluir" class="mt-3" onsubmit="return confirm('Cancelar esta O.S.?');">
                <?= \App\Helpers\Csrf::field() ?>
                <button type="submit" class="btn btn-outline-danger w-100">Cancelar O.S.</button>
            </form>
        <?php endif; ?>
    </div>
</div>
