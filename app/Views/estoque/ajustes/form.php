<?php

declare(strict_types=1);

$stores = is_array($stores ?? null) ? $stores : [];
$products = is_array($products ?? null) ? $products : [];
$errors = is_array($errors ?? null) ? $errors : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Novo ajuste</h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/estoque/ajustes">Voltar</a>
</div>

<?php if ($errors !== []): ?>
    <div class="alert alert-danger small"><?= htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form class="lumis-form" method="post" action="/estoque/ajustes">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="lumis-form-section">
        <div class="row g-3">
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
                <label class="form-label lumis-label">Tipo</label>
                <select name="direction" class="form-select app-input" required>
                    <option value="in">Entrada (acréscimo)</option>
                    <option value="out">Saída (redução)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Quantidade</label>
                <input type="text" name="qty" class="form-control app-input" required value="1">
            </div>
            <div class="col-md-8">
                <label class="form-label lumis-label">Motivo</label>
                <input type="text" name="reason_text" class="form-control app-input" placeholder="Ex.: inventário, quebra…">
            </div>
            <div class="col-12">
                <label class="form-label lumis-label">Observações</label>
                <textarea name="notes" class="form-control app-input" rows="2"></textarea>
            </div>
        </div>
    </div>
    <div class="mt-3 d-flex gap-2 justify-content-end">
        <a class="btn btn-lumis-secondary" href="/estoque/ajustes">Cancelar</a>
        <?php if (can('estoque.ajustes.create')): ?>
            <button type="submit" class="btn btn-primary">Registrar ajuste</button>
        <?php endif; ?>
    </div>
</form>
