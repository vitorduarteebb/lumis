<?php
declare(strict_types=1);
$rows = is_array($rows ?? null) ? $rows : [];
$statusLabels = is_array($statusLabels ?? null) ? $statusLabels : [];
$typeLabels = is_array($typeLabels ?? null) ? $typeLabels : [];
$drivers = is_array($drivers ?? null) ? $drivers : [];
$clients = is_array($clients ?? null) ? $clients : [];
$basePath = (string) ($basePath ?? '/locacoes/gerenciar');
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Locações</div>
        <h2 class="h4 mb-1 text-white">Gerenciar locações</h2>
        <div class="text-secondary small">Entregas, coletas e logística operacional.</div>
    </div>
    <?php if (can('locacoes.gerenciar.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/novo">Nova locação</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 320px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Nº, cliente, endereço…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="status" class="form-select app-input" style="max-width: 180px;">
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
    <input type="text" name="district" class="form-control app-input" style="max-width: 140px;" placeholder="Bairro" value="<?= htmlspecialchars((string) ($district ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateFrom ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateTo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Número</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Entregador</th>
                <th>Prev. entrega</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="7" class="text-secondary small py-4">Nenhuma locação encontrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $st = (string) ($row['status'] ?? '');
                $ot = (string) ($row['operation_type'] ?? '');
                ?>
                <tr>
                    <td class="text-white font-monospace"><?= htmlspecialchars((string) ($row['document_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small"><?= htmlspecialchars($typeLabels[$ot] ?? $ot, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge text-bg-secondary"><?= htmlspecialchars($statusLabels[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="small text-secondary"><?= htmlspecialchars((string) ($row['delivery_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small text-secondary"><?= htmlspecialchars((string) ($row['expected_delivery_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($basePath . '/' . $rid, ENT_QUOTES, 'UTF-8') ?>">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
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
]);
include base_path('app/Views/partials/pagination.php');
?>
