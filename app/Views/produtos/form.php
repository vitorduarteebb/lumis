<?php

declare(strict_types=1);

/** @var string $mode */
/** @var array<string, mixed>|null $product */
/** @var list<array<string, mixed>> $categories */
/** @var list<array<string, mixed>> $brands */
/** @var list<array<string, mixed>> $units */
/** @var array<string, string> $errors */
/** @var array<string, mixed> $old */

$mode = $mode ?? 'create';
$product = is_array($product ?? null) ? $product : null;
$categories = is_array($categories ?? null) ? $categories : [];
$brands = is_array($brands ?? null) ? $brands : [];
$units = is_array($units ?? null) ? $units : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$isEdit = $mode === 'edit';
$action = $isEdit ? '/produtos/' . (int) ($product['id'] ?? 0) : '/produtos';

$val = static function (string $key, string $default = '') use ($old, $product, $isEdit): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($isEdit && $product !== null && array_key_exists($key, $product) && $product[$key] !== null) {
        $v = $product[$key];
        if (is_float($v) || is_int($v)) {
            return (string) $v;
        }

        return (string) $v;
    }

    return $default;
};

$sel = static function (string $key) use ($old, $product, $isEdit): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($isEdit && $product !== null && array_key_exists($key, $product)) {
        $v = $product[$key];

        return $v === null || $v === '' ? '' : (string) $v;
    }

    return '';
};
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Produtos</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar produto' : 'Novo produto' ?></h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/produtos">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Dados principais</div>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label lumis-label" for="name">Nome <span class="text-danger">*</span></label>
                <input type="text" class="form-control app-input <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" required value="<?= htmlspecialchars($val('name'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="sku">SKU <span class="text-danger">*</span></label>
                <input type="text" class="form-control app-input <?= isset($errors['sku']) ? 'is-invalid' : '' ?>" id="sku" name="sku" required value="<?= htmlspecialchars($val('sku'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['sku'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['sku'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="internal_code">Código interno</label>
                <input type="text" class="form-control app-input" id="internal_code" name="internal_code" value="<?= htmlspecialchars($val('internal_code'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="barcode">Código de barras</label>
                <input type="text" class="form-control app-input" id="barcode" name="barcode" value="<?= htmlspecialchars($val('barcode'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="category_id">Categoria</label>
                <select class="form-select app-input" id="category_id" name="category_id">
                    <option value="">—</option>
                    <?php $s = $sel('category_id'); ?>
                    <?php foreach ($categories as $c): ?>
                        <?php $cid = (int) ($c['id'] ?? 0); ?>
                        <option value="<?= $cid ?>" <?= $s !== '' && (int) $s === $cid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="brand_id">Marca</label>
                <select class="form-select app-input" id="brand_id" name="brand_id">
                    <option value="">—</option>
                    <?php $s = $sel('brand_id'); ?>
                    <?php foreach ($brands as $b): ?>
                        <?php $bid = (int) ($b['id'] ?? 0); ?>
                        <option value="<?= $bid ?>" <?= $s !== '' && (int) $s === $bid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($b['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="unit_id">Unidade</label>
                <select class="form-select app-input" id="unit_id" name="unit_id">
                    <option value="">—</option>
                    <?php $s = $sel('unit_id'); ?>
                    <?php foreach ($units as $u): ?>
                        <?php $uid = (int) ($u['id'] ?? 0); ?>
                        <?php $uname = (string) ($u['name'] ?? '') . (($u['abbreviation'] ?? '') !== '' ? ' (' . $u['abbreviation'] . ')' : ''); ?>
                        <option value="<?= $uid ?>" <?= $s !== '' && (int) $s === $uid ? 'selected' : '' ?>><?= htmlspecialchars($uname, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label" for="status">Status</label>
                <?php $st = (int) $val('status', '1'); ?>
                <select class="form-select app-input" id="status" name="status">
                    <option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
        </div>
    </div>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Valores e estoque</div>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label lumis-label" for="cost">Custo</label>
                <input type="text" inputmode="decimal" class="form-control app-input <?= isset($errors['cost']) ? 'is-invalid' : '' ?>" id="cost" name="cost" value="<?= htmlspecialchars($val('cost', '0'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['cost'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['cost'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="sale_price">Preço de venda</label>
                <input type="text" inputmode="decimal" class="form-control app-input <?= isset($errors['sale_price']) ? 'is-invalid' : '' ?>" id="sale_price" name="sale_price" value="<?= htmlspecialchars($val('sale_price', '0'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['sale_price'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['sale_price'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="stock_qty">Estoque atual</label>
                <input type="text" inputmode="decimal" class="form-control app-input <?= isset($errors['stock_qty']) ? 'is-invalid' : '' ?>" id="stock_qty" name="stock_qty" value="<?= htmlspecialchars($val('stock_qty', '0'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['stock_qty'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['stock_qty'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label" for="stock_min">Estoque mínimo</label>
                <input type="text" inputmode="decimal" class="form-control app-input <?= isset($errors['stock_min']) ? 'is-invalid' : '' ?>" id="stock_min" name="stock_min" value="<?= htmlspecialchars($val('stock_min', '0'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['stock_min'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['stock_min'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Descrição</div>
        <textarea class="form-control app-input" name="description" rows="4"><?= htmlspecialchars($val('description'), ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-end pt-2">
        <a class="btn btn-lumis-secondary" href="/produtos">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar' : 'Cadastrar' ?></button>
    </div>
</form>
