<?php

declare(strict_types=1);

$cfg = is_array($cfg ?? null) ? $cfg : [];
$perm = (string) ($cfg['perm'] ?? 'notas_fiscais.produtos');
$bundle = is_array($bundle ?? null) ? $bundle : ['doc' => [], 'lines' => []];
$doc = $bundle['doc'];
$lines = is_array($bundle['lines'] ?? null) ? $bundle['lines'] : [];
$basePath = (string) ($cfg['base'] ?? '/notas-fiscais/produtos');
$lineMode = (string) ($cfg['lineMode'] ?? 'product');
$id = (int) ($doc['id'] ?? 0);
$st = (string) ($doc['status'] ?? '');
$stLab = ['draft' => 'Digitada', 'issued' => 'Emitida', 'cancelled' => 'Cancelada', 'voided' => 'Inutilizada', 'error' => 'Erro'];
$party = (string) ($doc['client_name'] ?? $doc['supplier_name'] ?? '—');
$canEdit = in_array($st, ['draft', 'error'], true) && can($perm . '.edit');
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Notas fiscais</div>
        <h2 class="h4 mb-1 text-white"><?= htmlspecialchars((string) ($doc['document_number'] ?? 'Nota'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="text-secondary small">
            <span class="badge <?= $st === 'issued' ? 'badge-lumis-success' : ($st === 'cancelled' || $st === 'voided' ? 'text-bg-secondary' : 'badge-lumis-warning') ?>"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
            · <?= htmlspecialchars($party, ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-lumis-secondary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Voltar</a>
        <?php if (!empty($doc['xml_path'])): ?>
            <a class="btn btn-outline-light btn-sm rounded-3" href="<?= htmlspecialchars($basePath . '/' . $id . '/download/xml', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">XML</a>
        <?php endif; ?>
        <?php if (!empty($doc['pdf_path'])): ?>
            <a class="btn btn-outline-light btn-sm rounded-3" href="<?= htmlspecialchars($basePath . '/' . $id . '/download/pdf', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">PDF</a>
        <?php endif; ?>
        <?php if ($canEdit): ?>
            <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath . '/' . $id . '/editar', ENT_QUOTES, 'UTF-8') ?>">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="lumis-form-section mb-3">
    <div class="row g-2 text-secondary small">
        <div class="col-md-4"><span class="text-white">Subtotal:</span> <?= htmlspecialchars(lumis_money_br((float) ($doc['subtotal_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="col-md-4"><span class="text-white">Desconto:</span> <?= htmlspecialchars(lumis_money_br((float) ($doc['discount_total'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="col-md-4"><span class="text-white">Total:</span> <?= htmlspecialchars(lumis_money_br((float) ($doc['total_amount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></div>
        <?php if (!empty($doc['access_key'])): ?>
            <div class="col-12 font-monospace small"><span class="text-white">Chave:</span> <?= htmlspecialchars((string) $doc['access_key'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>
    <?php if (!empty($doc['notes'])): ?>
        <p class="mt-2 mb-0 small"><?= nl2br(htmlspecialchars((string) $doc['notes'], ENT_QUOTES, 'UTF-8')) ?></p>
    <?php endif; ?>
</div>

<div class="lumis-table-wrap mb-3">
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
            <?php foreach ($lines as $ln): ?>
                <?php $nm = $lineMode === 'service' ? ($ln['service_name'] ?? '') : ($ln['product_name'] ?? ''); ?>
                <tr>
                    <td><?= htmlspecialchars((string) $nm, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars((string) ($ln['qty'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['unit_price'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['line_discount'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><?= htmlspecialchars(lumis_money_br((float) ($ln['line_total'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (can($perm . '.edit')): ?>
    <div class="card border-secondary-subtle bg-transparent rounded-3 p-3 mb-3">
        <h3 class="h6 text-white">Registrar status</h3>
        <form method="post" action="<?= htmlspecialchars($basePath . '/' . $id . '/status', ENT_QUOTES, 'UTF-8') ?>" class="row g-2 align-items-end">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="col-md-4">
                <select name="new_status" class="form-select form-select-sm app-input">
                    <?php foreach (['draft', 'issued', 'cancelled', 'voided', 'error'] as $s): ?>
                        <option value="<?= $s ?>" <?= $st === $s ? 'selected' : '' ?>><?= htmlspecialchars($stLab[$s] ?? $s, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><button type="submit" class="btn btn-sm btn-lumis-secondary">Atualizar status</button></div>
        </form>
    </div>
<?php endif; ?>

<?php if ($st !== 'cancelled' && $st !== 'voided' && can($perm . '.edit')): ?>
    <form method="post" action="<?= htmlspecialchars($basePath . '/' . $id . '/cancelar', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Cancelar esta nota?');" class="d-inline">
        <?= \App\Helpers\Csrf::field() ?>
        <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar nota</button>
    </form>
<?php endif; ?>
