<?php
declare(strict_types=1);
$rows = is_array($rows ?? null) ? $rows : [];
$statusLabels = is_array($statusLabels ?? null) ? $statusLabels : [];
$typeLabels = is_array($typeLabels ?? null) ? $typeLabels : [];
$drivers = is_array($drivers ?? null) ? $drivers : [];
$clients = is_array($clients ?? null) ? $clients : [];
$countsByDriver = is_array($countsByDriver ?? null) ? $countsByDriver : [];
$basePath = (string) ($basePath ?? '/locacoes/distribuicao');
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Locações</div>
        <h2 class="h4 mb-1 text-white">Distribuição de entregas</h2>
        <div class="text-secondary small">Selecione locações e atribua ou reatribua entregadores em lote.</div>
    </div>
</div>

<div class="row g-3 mb-3">
    <?php foreach ($countsByDriver as $c): ?>
        <?php
        $cnt = (int) ($c['cnt'] ?? 0);
        $dn = (string) ($c['name'] ?? 'Sem entregador');
        $duid = $c['delivery_user_id'] ?? null;
        ?>
        <div class="col-md-4">
            <div class="lumis-form-section py-2 px-3 mb-0">
                <div class="small text-secondary"><?= $duid === null ? 'Sem atribuição' : htmlspecialchars($dn, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="h5 mb-0 text-white"><?= $cnt ?> em rota / pendente</div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if ($countsByDriver === []): ?>
        <div class="col-12 text-secondary small">Nenhuma contagem (sem registros ativos).</div>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 300px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Busca…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="status" class="form-select app-input" style="max-width: 170px;">
        <option value="all">Status (todos)</option>
        <?php foreach ($statusLabels as $sk => $sl): ?>
            <option value="<?= htmlspecialchars($sk, ENT_QUOTES, 'UTF-8') ?>" <?= ($status ?? '') === $sk ? 'selected' : '' ?>><?= htmlspecialchars($sl, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="delivery_user_id" class="form-select app-input" style="max-width: 200px;">
        <option value="0">Entregador (todos)</option>
        <?php foreach ($drivers as $d): ?>
            <?php $did = (int) ($d['id'] ?? 0); ?>
            <option value="<?= $did ?>" <?= (int) ($deliveryUserId ?? 0) === $did ? 'selected' : '' ?>><?= htmlspecialchars((string) ($d['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select name="client_id" class="form-select app-input" style="max-width: 200px;">
        <option value="0">Cliente (todos)</option>
        <?php foreach ($clients as $c): ?>
            <?php $cid = (int) ($c['id'] ?? 0); ?>
            <option value="<?= $cid ?>" <?= (int) ($clientId ?? 0) === $cid ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="district" class="form-control app-input" style="max-width: 130px;" placeholder="Bairro" value="<?= htmlspecialchars((string) ($district ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateFrom ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateTo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <select name="only_unassigned" class="form-select app-input" style="max-width: 180px;">
        <option value="1" <?= !empty($onlyUnassigned) ? 'selected' : '' ?>>Só sem entregador</option>
        <option value="0" <?= empty($onlyUnassigned) ? 'selected' : '' ?>>Todos</option>
    </select>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<form method="post" action="/locacoes/distribuicao/atribuir" class="mb-3">
    <?= \App\Helpers\Csrf::field() ?>
    <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
        <div>
            <label class="form-label small text-secondary mb-1">Atribuir entregador</label>
            <select name="assign_delivery_user_id" class="form-select app-input">
                <option value="">— Remover atribuição —</option>
                <?php foreach ($drivers as $d): ?>
                    <?php $did = (int) ($d['id'] ?? 0); ?>
                    <option value="<?= $did ?>"><?= htmlspecialchars((string) ($d['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (can('locacoes.assign')): ?>
            <button type="submit" class="btn btn-primary btn-sm">Aplicar aos selecionados</button>
        <?php endif; ?>
    </div>

    <div class="lumis-table-wrap mb-2">
        <table class="table lumis-table mb-0">
            <thead>
                <tr>
                    <th style="width:40px"><input type="checkbox" id="check-all-ro"></th>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Entregador</th>
                    <th>Prev. entrega</th>
                    <th>Bairro</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="8" class="text-secondary small py-4">Nenhum registro.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $rid = (int) ($row['id'] ?? 0);
                    $st = (string) ($row['status'] ?? '');
                    $ot = (string) ($row['operation_type'] ?? '');
                    ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?= $rid ?>" class="check-ro"></td>
                        <td class="font-monospace small"><?= htmlspecialchars((string) ($row['document_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small"><?= htmlspecialchars((string) ($row['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small"><?= htmlspecialchars($typeLabels[$ot] ?? $ot, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge text-bg-secondary"><?= htmlspecialchars($statusLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td class="small"><?= htmlspecialchars((string) ($row['delivery_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small"><?= htmlspecialchars((string) ($row['expected_delivery_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small"><?= htmlspecialchars((string) ($row['district'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</form>

<?php
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$total = (int) ($total ?? 0);
$queryParams = array_filter([
    'q' => (string) ($search ?? '') !== '' ? (string) $search : null,
    'status' => (string) ($status ?? '') !== 'all' ? (string) $status : null,
    'delivery_user_id' => (int) ($deliveryUserId ?? 0) > 0 ? (string) (int) $deliveryUserId : null,
    'client_id' => (int) ($clientId ?? 0) > 0 ? (string) (int) $clientId : null,
    'district' => (string) ($district ?? '') !== '' ? (string) $district : null,
    'date_from' => (string) ($dateFrom ?? '') !== '' ? (string) $dateFrom : null,
    'date_to' => (string) ($dateTo ?? '') !== '' ? (string) $dateTo : null,
    'only_unassigned' => !empty($onlyUnassigned) ? '1' : null,
]);
include base_path('app/Views/partials/pagination.php');
?>

<script>
(function () {
    const all = document.getElementById('check-all-ro');
    if (!all) return;
    all.addEventListener('change', function () {
        document.querySelectorAll('.check-ro').forEach(function (cb) { cb.checked = all.checked; });
    });
})();
</script>
