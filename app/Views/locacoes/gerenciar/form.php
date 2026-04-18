<?php
declare(strict_types=1);
$mode = $mode ?? 'create';
$row = is_array($row ?? null) ? $row : [];
$items = is_array($items ?? null) ? $items : [['product_name' => '', 'qty' => 1, 'notes' => '']];
$clients = is_array($clients ?? null) ? $clients : [];
$stores = is_array($stores ?? null) ? $stores : [];
$drivers = is_array($drivers ?? null) ? $drivers : [];
$statusLabels = is_array($statusLabels ?? null) ? $statusLabels : [];
$typeLabels = is_array($typeLabels ?? null) ? $typeLabels : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$isEdit = $mode === 'edit';
$basePath = '/locacoes/gerenciar';
$id = (int) ($row['id'] ?? 0);
$action = $isEdit ? $basePath . '/' . $id : $basePath;
$val = static function (string $key, string $default = '') use ($old, $row, $isEdit): string {
    if (array_key_exists($key, $old)) {
        return (string) $old[$key];
    }
    if ($isEdit && isset($row[$key]) && $row[$key] !== null) {
        return (string) $row[$key];
    }
    return $default;
};
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Locações</div>
        <h2 class="h4 mb-1 text-white"><?= $isEdit ? 'Editar locação' : 'Nova locação' ?></h2>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Voltar</a>
</div>

<form class="lumis-form" method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
    <?= \App\Helpers\Csrf::field() ?>

    <div class="lumis-form-section">
        <div class="lumis-form-section__title">Dados gerais</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label lumis-label">Cliente <span class="text-danger">*</span></label>
                <select name="client_id" class="form-select app-input <?= isset($errors['client_id']) ? 'is-invalid' : '' ?>" required>
                    <option value="">—</option>
                    <?php foreach ($clients as $c): ?>
                        <?php $cid = (int) ($c['id'] ?? 0); ?>
                        <option value="<?= $cid ?>" <?= $val('client_id', (string) ($row['client_id'] ?? '')) === (string) $cid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['client_id'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['client_id'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Loja</label>
                <select name="store_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($stores as $s): ?>
                        <?php $sid = (int) ($s['id'] ?? 0); ?>
                        <option value="<?= $sid ?>" <?= $val('store_id', (string) ($row['store_id'] ?? '')) === (string) $sid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Data da locação <span class="text-danger">*</span></label>
                <input type="date" name="rental_date" class="form-control app-input <?= isset($errors['rental_date']) ? 'is-invalid' : '' ?>" required value="<?= htmlspecialchars($val('rental_date', date('Y-m-d')), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Prev. entrega</label>
                <input type="date" name="expected_delivery_date" class="form-control app-input" value="<?= htmlspecialchars($val('expected_delivery_date', ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Prev. coleta</label>
                <input type="date" name="expected_pickup_date" class="form-control app-input" value="<?= htmlspecialchars($val('expected_pickup_date', ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Tipo</label>
                <select name="operation_type" class="form-select app-input">
                    <?php foreach ($typeLabels as $tk => $tl): ?>
                        <option value="<?= htmlspecialchars($tk, ENT_QUOTES, 'UTF-8') ?>" <?= $val('operation_type', 'both') === $tk ? 'selected' : '' ?>><?= htmlspecialchars($tl, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label lumis-label">Status</label>
                <select name="status" class="form-select app-input">
                    <?php foreach ($statusLabels as $sk => $sl): ?>
                        <option value="<?= htmlspecialchars($sk, ENT_QUOTES, 'UTF-8') ?>" <?= $val('status', 'pending') === $sk ? 'selected' : '' ?>><?= htmlspecialchars($sl, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label lumis-label">Entregador</label>
                <select name="delivery_user_id" class="form-select app-input">
                    <option value="">—</option>
                    <?php foreach ($drivers as $d): ?>
                        <?php $did = (int) ($d['id'] ?? 0); ?>
                        <option value="<?= $did ?>" <?= $val('delivery_user_id', (string) ($row['delivery_user_id'] ?? '')) === (string) $did ? 'selected' : '' ?>><?= htmlspecialchars((string) ($d['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="lumis-form-section mt-3">
        <div class="lumis-form-section__title">Endereço</div>
        <div class="row g-3">
            <div class="col-md-2"><label class="form-label lumis-label">CEP</label><input type="text" name="cep" class="form-control app-input" value="<?= htmlspecialchars($val('cep', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-6"><label class="form-label lumis-label">Logradouro</label><input type="text" name="street" class="form-control app-input" value="<?= htmlspecialchars($val('street', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-2"><label class="form-label lumis-label">Nº</label><input type="text" name="address_number" class="form-control app-input" value="<?= htmlspecialchars($val('address_number', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-2"><label class="form-label lumis-label">Compl.</label><input type="text" name="complement" class="form-control app-input" value="<?= htmlspecialchars($val('complement', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-4"><label class="form-label lumis-label">Bairro</label><input type="text" name="district" class="form-control app-input" value="<?= htmlspecialchars($val('district', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-4"><label class="form-label lumis-label">Cidade</label><input type="text" name="city" class="form-control app-input" value="<?= htmlspecialchars($val('city', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-2"><label class="form-label lumis-label">UF</label><input type="text" name="state" maxlength="2" class="form-control app-input" value="<?= htmlspecialchars($val('state', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-6"><label class="form-label lumis-label">Referência</label><input type="text" name="reference" class="form-control app-input" value="<?= htmlspecialchars($val('reference', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-3"><label class="form-label lumis-label">Latitude</label><input type="text" name="latitude" class="form-control app-input" placeholder="opcional" value="<?= htmlspecialchars($val('latitude', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-3"><label class="form-label lumis-label">Longitude</label><input type="text" name="longitude" class="form-control app-input" placeholder="opcional" value="<?= htmlspecialchars($val('longitude', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
        </div>
    </div>

    <div class="lumis-form-section mt-3">
        <div class="lumis-form-section__title">Contato</div>
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label lumis-label">Responsável</label><input type="text" name="contact_name" class="form-control app-input" value="<?= htmlspecialchars($val('contact_name', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-4"><label class="form-label lumis-label">Telefone</label><input type="text" name="phone_primary" class="form-control app-input" value="<?= htmlspecialchars($val('phone_primary', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-4"><label class="form-label lumis-label">Telefone 2</label><input type="text" name="phone_secondary" class="form-control app-input" value="<?= htmlspecialchars($val('phone_secondary', ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-12"><label class="form-label lumis-label">Observações internas</label><textarea name="notes_internal" class="form-control app-input" rows="2"><?= htmlspecialchars($val('notes_internal', ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
            <div class="col-12"><label class="form-label lumis-label">Observações para o entregador</label><textarea name="notes_driver" class="form-control app-input" rows="2"><?= htmlspecialchars($val('notes_driver', ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
        </div>
    </div>

    <div class="lumis-form-section mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="lumis-form-section__title mb-0">Itens locados</div>
            <button type="button" class="btn btn-sm btn-outline-light" id="lumis-ro-add-line">+ Item</button>
        </div>
        <?php if (isset($errors['items'])): ?><div class="text-danger small mb-2"><?= htmlspecialchars($errors['items'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <div class="table-responsive">
            <table class="table lumis-table mb-0" id="lumis-ro-items">
                <thead><tr><th>Descrição do item</th><th style="width:100px">Qtd</th><th>Obs.</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($items as $idx => $it): ?>
                        <tr class="lumis-ro-line">
                            <td><input type="text" name="items[<?= (int) $idx ?>][product_name]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($it['product_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></td>
                            <td><input type="number" step="0.0001" min="0.0001" name="items[<?= (int) $idx ?>][qty]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($it['qty'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="text" name="items[<?= (int) $idx ?>][notes]" class="form-control form-control-sm app-input" value="<?= htmlspecialchars((string) ($it['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger lumis-ro-rm" title="Remover">&times;</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end pt-3">
        <a class="btn btn-lumis-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar' : 'Cadastrar' ?></button>
    </div>
</form>

<script>
(function () {
    const tbody = document.querySelector('#lumis-ro-items tbody');
    const addBtn = document.getElementById('lumis-ro-add-line');
    if (!tbody || !addBtn) return;
    let idx = <?= count($items) ?>;
    addBtn.addEventListener('click', function () {
        const tr = document.createElement('tr');
        tr.className = 'lumis-ro-line';
        tr.innerHTML = '<td><input type="text" name="items[' + idx + '][product_name]" class="form-control form-control-sm app-input" required></td>' +
            '<td><input type="number" step="0.0001" min="0.0001" name="items[' + idx + '][qty]" class="form-control form-control-sm app-input" value="1"></td>' +
            '<td><input type="text" name="items[' + idx + '][notes]" class="form-control form-control-sm app-input"></td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger lumis-ro-rm" title="Remover">&times;</button></td>';
        tbody.appendChild(tr);
        idx++;
    });
    tbody.addEventListener('click', function (e) {
        const t = e.target;
        if (t && t.classList && t.classList.contains('lumis-ro-rm')) {
            const row = t.closest('tr');
            if (row && tbody.querySelectorAll('tr').length > 1) row.remove();
        }
    });
})();
</script>
