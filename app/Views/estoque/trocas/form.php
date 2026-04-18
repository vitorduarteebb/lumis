<?php

declare(strict_types=1);

$stores = is_array($stores ?? null) ? $stores : [];
$products = is_array($products ?? null) ? $products : [];
$clients = is_array($clients ?? null) ? $clients : [];
$suppliers = is_array($suppliers ?? null) ? $suppliers : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Nova devolução / troca</h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/estoque/trocas-devolucoes">Voltar</a>
</div>

<form class="lumis-form" method="post" action="/estoque/trocas-devolucoes">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label lumis-label">Tipo</label>
                <select name="return_kind" class="form-select app-input" required>
                    <option value="sale_return">Devolução de venda</option>
                    <option value="purchase_return">Devolução de compra</option>
                    <option value="exchange">Troca</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Loja</label>
                <select name="store_id" class="form-select app-input" required>
                    <?php foreach ($stores as $s): ?>
                        <option value="<?= (int) ($s['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Produto</label>
                <select name="product_id" class="form-select app-input" required>
                    <option value="">—</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= (int) ($p['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Quantidade</label>
                <input type="text" name="qty" class="form-control app-input" required value="1">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Cliente (opcional)</label>
                <select name="client_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int) ($c['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Fornecedor (opcional)</label>
                <select name="supplier_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= (int) ($s['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($s['trade_name'] ?? $s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label">ID venda (opcional)</label>
                <input type="number" name="sales_document_id" class="form-control app-input" min="0" value="0">
            </div>
            <div class="col-md-6">
                <label class="form-label lumis-label">ID compra (opcional)</label>
                <input type="number" name="purchase_order_id" class="form-control app-input" min="0" value="0">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label">Motivo</label>
                <input type="text" name="reason" class="form-control app-input">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label">Observações</label>
                <textarea name="notes" class="form-control app-input" rows="2"></textarea>
            </div>
        </div>
    </div>
    <div class="mt-3 d-flex gap-2 justify-content-end">
        <a class="btn btn-lumis-secondary" href="/estoque/trocas-devolucoes">Cancelar</a>
        <?php if (can('estoque.trocas_devolucoes.create')): ?>
            <button type="submit" class="btn btn-primary">Salvar</button>
        <?php endif; ?>
    </div>
</form>
