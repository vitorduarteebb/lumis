<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $priceLists */
/** @var int $listId */
/** @var list<array<string, mixed>> $rows */

$priceLists = is_array($priceLists ?? null) ? $priceLists : [];
$rows = is_array($rows ?? null) ? $rows : [];
$listId = (int) ($listId ?? 0);
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Produtos</div>
        <h2 class="h4 mb-1 text-white">Valores de venda</h2>
        <div class="text-secondary small">Tabelas de preço por produto. O preço base do cadastro é referência quando não houver valor na tabela.</div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/produtos">Produtos</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <form method="get" action="/produtos/valores-venda" class="d-flex flex-wrap gap-2 align-items-end">
            <div class="flex-grow-1">
                <label class="form-label lumis-label small mb-1" for="list_id">Tabela de preço</label>
                <select class="form-select app-input" id="list_id" name="list_id" onchange="this.form.submit()">
                    <?php foreach ($priceLists as $pl): ?>
                        <?php $pid = (int) ($pl['id'] ?? 0); ?>
                        <option value="<?= $pid ?>" <?= $pid === $listId ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($pl['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            <?= (int) ($pl['is_default'] ?? 0) === 1 ? ' (padrão)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    <div class="col-md-6">
        <?php if (can('produtos.valores_venda.edit')): ?>
            <form method="post" action="/produtos/valores-venda" class="row g-2 align-items-end">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="_action" value="set_default">
                <input type="hidden" name="list_id" value="<?= $listId ?>">
                <div class="col">
                    <button type="submit" class="btn btn-outline-light btn-sm rounded-3 w-100">Usar esta tabela como padrão</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (can('produtos.valores_venda.edit')): ?>
    <div class="lumis-form-section mb-4">
        <div class="lumis-form-section__title">Nova tabela de preço</div>
        <form method="post" action="/produtos/valores-venda" class="row g-2 align-items-end">
            <?= \App\Helpers\Csrf::field() ?>
            <input type="hidden" name="_action" value="create_list">
            <div class="col-md-6">
                <label class="form-label lumis-label small mb-1" for="new_list_name">Nome</label>
                <input type="text" class="form-control app-input" id="new_list_name" name="new_list_name" placeholder="Ex.: Varejo, Atacado, E-commerce" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm rounded-3">Criar tabela</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php if (can('produtos.valores_venda.edit')): ?>
<form method="post" action="/produtos/valores-venda" class="lumis-form">
    <?= \App\Helpers\Csrf::field() ?>
    <input type="hidden" name="_action" value="save_prices">
    <input type="hidden" name="list_id" value="<?= $listId ?>">
<?php else: ?>
<div class="lumis-form">
<?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="text-secondary small">Ajuste os valores da tabela selecionada e salve.</div>
        <?php if (can('produtos.valores_venda.edit')): ?>
            <button type="submit" class="btn btn-primary btn-sm rounded-3">Salvar preços</button>
        <?php endif; ?>
    </div>

    <div class="lumis-table-wrap mb-2">
        <table class="table lumis-table mb-0">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>SKU</th>
                    <th>Preço base (cadastro)</th>
                    <th>Preço nesta tabela</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="4" class="text-secondary small py-4">Nenhum produto cadastrado.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $rid = (int) ($r['id'] ?? 0);
                    $base = (float) ($r['base_price'] ?? 0);
                    $listPrice = $r['list_price'];
                    $display = $listPrice !== null && $listPrice !== '' ? (float) $listPrice : $base;
                    ?>
                    <tr>
                        <td class="text-white"><?= htmlspecialchars((string) ($r['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars((string) ($r['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars(lumis_money_br($base), ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="max-width: 200px;">
                            <?php if (can('produtos.valores_venda.edit')): ?>
                                <input type="text" class="form-control form-control-sm app-input" name="prices[<?= $rid ?>]" value="<?= htmlspecialchars(number_format($display, 4, ',', ''), ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <?= htmlspecialchars(lumis_money_br($display), ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php if (can('produtos.valores_venda.edit')): ?>
</form>
<?php else: ?>
</div>
<?php endif; ?>
